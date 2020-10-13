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

use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\util\StaticArraySerializable;
use pocketmine\utils\UUID;

final class ExecutorList implements StaticArraySerializable
{
    /** @var Executor[][] */
    private static $executors = [];

    /**
     * @return Executor[]
     */
    public static function getAll(UUID $missionId): array
    {
        if (!isset(self::$executors[$missionId->toString()])) {
            self::register($missionId);
        }
        return self::$executors[$missionId->toString()];
    }

    public static function add(Executor $executor): void
    {
        if (!isset(self::$executors[$executor->getParentMission()->getId()->toString()])) {
            self::register($executor->getParentMission()->getId());
        }
        if (array_search($executor, self::$executors[$executor->getParentMission()->getId()->toString()], true) !== false) {
            throw new AlreadyExistsException("Executor \"{$executor->getParentMission()->getId()->toString()}\" already exists");
        }
        self::$executors[$executor->getParentMission()->getId()->toString()][] = $executor;
        $executor->register();
    }

    public static function remove(Executor $executor): void
    {
        $missionId = $executor->getParentMission()->getId();
        if (!isset(self::$executors[$missionId->toString()])
            || ($searchKey = array_search($executor, self::$executors[$missionId->toString()], true)) === false) {
            throw new NotFoundException("Executor \"{$missionId->toString()}\" not found");
        }
        unset(self::$executors[$missionId->toString()][$searchKey]);
        self::$executors[$missionId->toString()] = array_values(self::$executors[$missionId->toString()]);
    }

    public static function serializeToArray(): array
    {
        return array_values(array_map(function (array $executors): array {
            /** @var Executor[] $executors */
            return array_values(array_map(function (Executor $executor): array {
                return $executor->arraySerialize();
            }, $executors));
        }, self::$executors));
    }

    public static function initFromArray(array $data): void
    {
        foreach ($data as $arrayExecutors) {
            foreach ($arrayExecutors as $arrayExecutor) {
                try {
                    $executor = Executor::arrayDeSerialize($arrayExecutor);
                } catch (NotFoundException $exception) {
                    continue;//親ミッションが存在しない(消えていた)場合ここで削除される
                }
                self::add($executor);
            }
        }
    }

    private static function register(UUID $missionId): void
    {
        self::$executors[$missionId->toString()] = [];
    }

    private function __construct()
    {
        //
    }
}
