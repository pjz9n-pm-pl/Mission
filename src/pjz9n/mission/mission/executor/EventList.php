<?php

/**
 * Copyright (c) 2021 PJZ9n.
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

use aieuo\mineflow\event\EntityAttackEvent;
use Closure;
use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\util\SoftdependPlugin;
use pjz9n\mission\util\Utils;
use pocketmine\event\Event;
use pocketmine\Player;
use pocketmine\utils\Utils as PMUtils;
use ReflectionException;

final class EventList
{
    /** @var Closure[] eventClass => Closure */
    private static $events = [];

    /**
     * @throws ReflectionException
     */
    public static function addDefaults(): void
    {
        self::syncDefaults();
        if (SoftdependPlugin::isAvailableMineflow()) {
            self::addEvent(EntityAttackEvent::class, function (Event $event): ?Player {
                /** @var EntityAttackEvent $event */
                $damager = $event->getDamageEvent()->getDamager();
                return $damager instanceof Player ? $damager : null;
            });
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function syncDefaults(): void
    {
        foreach (Utils::getAvailablePlayerEvents() as $event) {
            try {
                self::addEvent($event, function (Event $event): ?Player {
                    return $event->getPlayer();
                });
            } catch (AlreadyExistsException $exception) {
            }
        }
    }

    public static function addEvent(string $event, Closure $closure): void
    {
        PMUtils::validateCallableSignature(function (Event $event): ?Player {
        }, $closure);
        if (isset(self::$events[$event])) {
            throw new AlreadyExistsException();
        }
        self::$events[$event] = $closure;
    }

    /**
     * @return string[]
     * @throws ReflectionException
     */
    public static function getEvents(): array
    {
        self::syncDefaults();
        return array_keys(self::$events);
    }

    public static function getPlayerByEvent(Event $event): ?Player
    {
        $targetEventClass = get_class($event);
        if (!isset(self::$events[$targetEventClass])) {
            return null;
        }
        return (self::$events[$targetEventClass])($event) ?? null;
    }

    private function __construct()
    {
        //
    }
}
