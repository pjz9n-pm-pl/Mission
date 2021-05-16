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

namespace pjz9n\mission\util;

use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\executor\Executor;
use pjz9n\mission\mission\progress\Progress;
use pjz9n\mission\reward\Reward;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Throwable;
use TypeError;

final class Utils
{
    /**
     * @param mixed $value
     *
     * @throws TypeError
     */
    public static function filterToInteger($value): int
    {
        $value = (string)$value;
        if (!is_numeric($value)) {
            throw new TypeError("value is not numeric");
        }
        return (int)$value;
    }

    /**
     * @param CompoundTag|string|null $tags
     */
    public static function isValidItem(int $id, int $meta = 0, int $count = 1, $tags = null): bool
    {
        try {
            ItemFactory::get($id, $meta, $count, $tags);
        } catch (Throwable $throwable) {
            return false;
        }
        return true;
    }

    /**
     * 箇条書きリストを返す
     *
     * @param string[] $list
     */
    public static function getItemizationList(array $list, string $symbol = " * ", string $eol = TextFormat::EOL): string
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $symbol . $item;
        }
        return implode($eol, $result);
    }

    /**
     * TODO: fix hack
     *
     * @param Reward[] $rewards
     */
    public static function getRewardsItemizationList(array $rewards, string $symbol = " * ", string $eol = TextFormat::EOL): string
    {
        if (count($rewards) > 0) {
            return self::getItemizationList(array_map(function (Reward $reward): string {
                return $reward->getDetail();
            }, $rewards), $symbol, $eol);
        }
        return $symbol . LanguageHolder::get()->translateString("reward.noavailable");
    }

    /**
     * TODO: fix hack
     *
     * @param Executor[] $executors
     */
    public static function getExecutorsItemizationList(array $executors, string $symbol = " * ", string $eol = TextFormat::EOL): string
    {
        if (count($executors) > 0) {
            return self::getItemizationList(array_map(function (Executor $executor): string {
                return $executor->getDetail();
            }, $executors), $symbol, $eol);
        }
        return $symbol . LanguageHolder::get()->translateString("executor.noavailable");
    }

    /**
     * @return string[]
     */
    public static function getAvailableEvents(): array
    {
        $result = [];
        foreach (get_declared_classes() as $declaredClass) {
            if (is_a($declaredClass, Event::class, true)) {
                $result[] = $declaredClass;
            }
        }
        return $result;
    }

    /**
     * @return string[]
     *
     * @throws ReflectionException
     */
    public static function getAvailablePlayerEvents(): array
    {
        $result = [];
        foreach (self::getAvailableEvents() as $availableEvent) {
            if (self::hasGetPlayerMethodAndReturnTypeIsPlayer($availableEvent)) {
                $result[] = $availableEvent;
            }
        }
        return $result;
    }

    public static function eventPriorityToString(int $priority): string
    {
        switch ($priority) {
            case EventPriority::MONITOR:
                return "MONITOR";
            case EventPriority::HIGHEST:
                return "HIGHEST";
            case EventPriority::HIGH:
                return "HIGH";
            case EventPriority::NORMAL:
                return "NORMAL";
            case EventPriority::LOW:
                return "LOW";
            case EventPriority::LOWEST:
                return "LOWEST";
        }
        return "Unknown";
    }

    /**
     * クラスがgetPlayerメソッドを持ち、さらにpocketmine\Playerを返す場合にtrueを返す
     *
     * TODO: PMMPのPlayerEventが正常に機能していないせいです、私は知りません
     *
     * @throws ReflectionException
     */
    public static function hasGetPlayerMethodAndReturnTypeIsPlayer(string $className): bool
    {
        if (is_a($className, PlayerEvent::class, true)) {
            return true;
        }
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->hasMethod("getPlayer")
            && ($method = $reflectionClass->getMethod("getPlayer"))->hasReturnType()
            && ($type = $method->getReturnType()) instanceof ReflectionNamedType
            && is_a($type->getName(), Player::class, true);
    }

    public static function generateLine(?string $title = null, int $baseCount = 30, string $token = "-"): string
    {
        if ($title === null) {
            return str_repeat($token, $baseCount);
        }
        $count = $baseCount - mb_strlen($title);
        $count -= 2;//Space
        if ($count < 2) {
            return $title;
        }
        return str_repeat($token, (int)floor($count / 2)) . " " . $title . " " . str_repeat($token, (int)floor($count / 2));
    }

    /**
     * @throws InquiryKeyGenerateException
     * @deprecated
     */
    public static function generateInquiryKey(bool $encrypt = true): string
    {
        return "";
    }

    /**
     * @param Progress[] $progresses
     */
    public static function isContainPinned(array $progresses): bool
    {
        foreach ($progresses as $progress) {
            if ($progress->isPinned()) {
                return true;
            }
        }
        return false;
    }

    private function __construct()
    {
        //
    }
}
