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

namespace pjz9n\mission\mission;

use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\util\StaticArraySerializable;
use pocketmine\utils\UUID;

final class MissionList implements StaticArraySerializable
{
    /** @var Mission[] */
    private static $missions = [];

    /**
     * @return Mission[]
     */
    public static function getAll(): array
    {
        return self::$missions;
    }

    /**
     * @return string[]
     */
    public static function getAllGroups(): array
    {
        $result = [];
        foreach (self::getAll() as $mission) {
            if (($group = $mission->getGroup()) !== null) {
                $result[] = $group;
            }
        }
        $result = array_unique($result);
        return $result;
    }

    public static function get(UUID $id): Mission
    {
        if (!isset(self::$missions[$id->toString()])) {
            throw new NotFoundException("Mission not found");
        }
        return self::$missions[$id->toString()];
    }

    public static function add(Mission $mission): void
    {
        if (isset(self::$missions[$mission->getId()->toString()])) {
            throw new AlreadyExistsException("Mission \"{$mission->getId()->toString()}\" already exists");
        }
        self::$missions[$mission->getId()->toString()] = $mission;
    }

    public static function remove(UUID $id): void
    {
        if (!isset(self::$missions[$id->toString()])) {
            throw new NotFoundException("Mission \"{$id->toString()}\" not found");
        }
        unset(self::$missions[$id->toString()]);
    }

    public static function serializeToArray(): array
    {
        $arrayMissions = [];
        foreach (self::getAll() as $mission) {
            $arrayMissions[] = $mission->arraySerialize();
        }
        return $arrayMissions;
    }

    public static function initFromArray(array $data): void
    {
        foreach ($data as $arrayMission) {
            self::add(Mission::arrayDeSerialize($arrayMission));
        }
    }

    private function __construct()
    {
        //
    }
}
