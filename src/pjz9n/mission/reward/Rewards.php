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

namespace pjz9n\mission\reward;

use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\util\SoftdependPlugin;
use pocketmine\utils\Utils;

final class Rewards
{
    /** @var string[] */
    private static $rewards = [];

    public static function addDefaults(): void
    {
        self::add(ItemReward::class);
        self::add(NothingReward::class);
        if (SoftdependPlugin::isAvailableMineflow()) {
            self::add(MineflowReward::class);
        }
    }

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$rewards;
    }

    public static function add(string $rewardClass): void
    {
        if (array_search($rewardClass, self::$rewards, true) !== false) {
            throw new AlreadyExistsException("RewardClass \"{$rewardClass}\" already exists");
        }
        Utils::testValidInstance($rewardClass, Reward::class);
        self::$rewards[] = $rewardClass;
    }

    public static function remove(string $rewardClass): void
    {
        if (($searchKey = array_search($rewardClass, self::$rewards, true)) === false) {
            throw new NotFoundException("RewardClass \"{$rewardClass}\" not found");
        }
        unset(self::$rewards[$searchKey]);
        self::$rewards = array_values(self::$rewards);
    }

    private function __construct()
    {
        //
    }
}
