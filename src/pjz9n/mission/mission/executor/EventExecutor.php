<?php

/**
 * Copyright (c) 2020 PJZ9n.
 *
 * This file is part of Mission.
 *
 * Mission is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mission is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mission. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace pjz9n\mission\mission\executor;

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\StepSlider;
use dktapps\pmforms\element\Toggle;
use InvalidStateException;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\Main;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\mission\progress\ProgressList;
use pjz9n\mission\util\Utils;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\Server;
use pocketmine\utils\UUID;
use ReflectionClass;
use ReflectionException;

class EventExecutor extends Executor implements Listener
{
    public static function getType(): string
    {
        return LanguageHolder::get()->translateString("event");
    }

    /**
     * @throws ReflectionException
     */
    public static function getCreateFormElements(): array
    {
        return [
            new Label(
                "tips",
                LanguageHolder::get()->translateString("executor.eventexecutor.selectevent.tips")
            ),
            new Dropdown(
                "eventClass",
                LanguageHolder::get()->translateString("event"),
                self::availableEventsToOptions(Utils::getAvailableEvents())
            ),
            new StepSlider(
                "eventPriority",
                LanguageHolder::get()->translateString("priority"),
                self::eventPrioritiesToOptions(EventPriority::ALL),
                2
            ),//default: NORMAL
            new Toggle(
                "ignoreCancelled",
                LanguageHolder::get()->translateString("ignorecancelled"),
                true
            ),
        ];
    }

    public static function createByFormResponse(CustomFormResponse $response, Mission $parentMission)
    {
        $eventClass = array_values(Utils::getAvailableEvents())[$response->getInt("eventClass")];
        $eventPriority = EventPriority::ALL[$response->getInt("eventPriority")];
        $ignoreCancelled = $response->getBool("ignoreCancelled");
        return new self($parentMission, $eventClass, $eventPriority, $ignoreCancelled);
    }

    public static function arrayDeSerialize(array $data)
    {
        return new self(
            MissionList::get(UUID::fromString($data["parentMissionId"])),
            $data["eventClass"],
            $data["eventPriority"],
            $data["ignoreCancelled"]
        );
    }

    /**
     * @param string[] $availableEvents
     *
     * @throws ReflectionException
     */
    private static function availableEventsToOptions(array $availableEvents): array
    {
        $result = [];
        foreach ($availableEvents as $availableEvent) {
            $refrectionClass = new ReflectionClass($availableEvent);
            $result[] = $refrectionClass->getShortName();
        }
        return $result;
    }

    private static function eventPrioritiesToOptions(array $eventPriorities): array
    {
        $result = [];
        foreach ($eventPriorities as $eventPriority) {
            $result[] = Utils::eventPriorityToString($eventPriority);
        }
        return $result;
    }

    private static function getIndexByEventPriority(int $eventPriority, array $eventPriorities = EventPriority::ALL): ?int
    {
        foreach ($eventPriorities as $index => $value) {
            if ($eventPriority === $value) {
                return $index;
            }
        }
        return null;
    }

    private static function getIndexByEvent(string $eventClass, array $availableEvents): ?int
    {
        return ($searchKey = array_search($eventClass, $availableEvents, true)) === false ? null : $searchKey;
    }

    /** @var string */
    private $eventClass;

    /** @var int */
    private $eventPriority;

    /** @var bool */
    private $ignoreCancelled;

    /** @var bool */
    private $isRegistered = false;

    public function __construct(Mission $parentMission, string $eventClass, int $eventPriority = EventPriority::NORMAL, bool $ignoreCancelled = true)
    {
        parent::__construct($parentMission);
        $this->eventClass = $eventClass;
        $this->eventPriority = $eventPriority;
        $this->ignoreCancelled = $ignoreCancelled;
    }

    public function register(): void
    {
        if ($this->isRegistered) {
            throw new InvalidStateException("already registered");
        }
        $this->isRegistered = true;
        Server::getInstance()->getPluginManager()->registerEvent(
            $this->eventClass,
            $this,
            $this->eventPriority,
            new MethodEventExecutor("handleEvent"),
            Main::getInstance(),
            $this->ignoreCancelled
        );
    }

    public function getDetail(): string
    {
        return self::getType() . ": " . (new ReflectionClass($this->getEventClass()))->getShortName();
    }

    public function getEventClass(): string
    {
        return $this->eventClass;
    }

    public function setEventClass(string $eventClass, bool $immediate = true): void
    {
        $this->eventClass = $eventClass;
        if ($immediate) {
            $this->reRegister();
        }
    }

    public function getEventPriority(): int
    {
        return $this->eventPriority;
    }

    public function setEventPriority(int $eventPriority, bool $immediate = true): void
    {
        $this->eventPriority = $eventPriority;
        if ($immediate) {
            $this->reRegister();
        }
    }

    public function isIgnoreCancelled(): bool
    {
        return $this->ignoreCancelled;
    }

    public function setIgnoreCancelled(bool $ignoreCancelled, bool $immediate = true): void
    {
        $this->ignoreCancelled = $ignoreCancelled;
        if ($immediate) {
            $this->reRegister();
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function handleEvent(Event $event): void
    {
        foreach (ProgressList::getAllById($this->getParentMission()->getId()) as $targetProgress) {
            $targetProgress->addStep();
        }
    }

    /**
     * @throws ReflectionException
     */
    public function getSettingFormElements(): array
    {
        $nowEventIndex = self::getIndexByEvent($this->eventClass, Utils::getAvailableEvents()) ?? 0;
        $nowEventClassForView = "(" . LanguageHolder::get()->translateString("nowvalue") . ": " . $this->getEventClass() . ")";
        return [
            new Label(
                "tips",
                LanguageHolder::get()->translateString("executor.eventexecutor.selectevent.tips")
            ),
            new Dropdown(
                "eventClass",
                LanguageHolder::get()->translateString("event") . $nowEventClassForView,
                self::availableEventsToOptions(Utils::getAvailableEvents()),
                $nowEventIndex),
            new StepSlider(
                "eventPriority",
                LanguageHolder::get()->translateString("priority"),
                self::eventPrioritiesToOptions(EventPriority::ALL),
                self::getIndexByEventPriority($this->getEventPriority()) ?? 0),
            new Toggle(
                "ignoreCancelled",
                LanguageHolder::get()->translateString("ignorecancelled"),
                $this->isIgnoreCancelled()),
        ];
    }

    public function processSettingFormResponse(CustomFormResponse $response): void
    {
        $eventClass = array_values(Utils::getAvailableEvents())[$response->getInt("eventClass")];
        $eventPriority = EventPriority::ALL[$response->getInt("eventPriority")];
        $ignoreCancelled = $response->getBool("ignoreCancelled");
        $this->setEventClass($eventClass, false);
        $this->setEventPriority($eventPriority, false);
        $this->setIgnoreCancelled($ignoreCancelled, true);
    }

    public function arraySerialize(): array
    {
        return array_merge(parent::arraySerialize(), [
            "eventClass" => $this->eventClass,
            "eventPriority" => $this->eventPriority,
            "ignoreCancelled" => $this->ignoreCancelled,
        ]);
    }

    private function reRegister(): void
    {
        if ($this->isRegistered) {
            $this->isRegistered = false;
            HandlerList::unregisterAll($this);
        }
        $this->register();
    }
}
