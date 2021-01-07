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

namespace pjz9n\mission\mission\progress;

use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\util\StaticArraySerializable;
use pocketmine\utils\UUID;

final class ProgressList implements StaticArraySerializable
{
    /** @var Progress[][] */
    private static $progresses = [];

    /**
     * @return Progress[]
     */
    public static function getAll(string $player): array
    {
        self::sync($player);
        return self::$progresses[$player];
    }

    /**
     * @return Progress[]
     */
    public static function getAllById(UUID $missionId): array
    {
        $result = [];
        foreach (self::$progresses as $player => $progresses) {
            foreach ($progresses as $progress) {
                if ($missionId->equals($progress->getParentMission()->getId())) {
                    $result[] = $progress;
                }
            }
        }
        return $result;
    }

    public static function get(string $player, UUID $missionId): Progress
    {
        self::sync($player);
        if (!isset(self::$progresses[$player][$missionId->toString()])) {
            throw new NotFoundException("Progress {$missionId->toString()} not found");
        }
        return self::$progresses[$player][$missionId->toString()];
    }

    public static function getPinned(string $player): ?Progress
    {
        foreach (self::getAll($player) as $progress) {
            if ($progress->isPinned()) {
                return $progress;
            }
        }
        return null;
    }

    public static function add(string $player, Progress $progress): void
    {
        if (isset(self::$progresses[$player][$progress->getParentMission()->getId()->toString()])) {
            throw new AlreadyExistsException("Progress \"{$progress->getParentMission()->getId()->toString()}\" already exists");
        }
        self::$progresses[$player][$progress->getParentMission()->getId()->toString()] = $progress;
    }

    public static function remove(string $player, UUID $missionId): void
    {
        if (!isset(self::$progresses[$player][$missionId->toString()])) {
            throw new NotFoundException("Progress {$missionId->toString()} not found");
        }
        unset(self::$progresses[$player][$missionId->toString()]);
    }

    public static function sync(string $player): void
    {
        if (!self::isRegistered($player)) {
            self::register($player);
        }
        //足りないProgressを作成する
        $needMissionIds = array_diff(
            array_map(function (Mission $mission): UUID {
                return $mission->getId();
            }, MissionList::getAll()),
            array_map(function (Progress $progress): UUID {
                return $progress->getParentMission()->getId();
            }, self::$progresses[$player])
        );
        foreach ($needMissionIds as $needMissionId) {
            self::add($player, MissionList::get($needMissionId)->createNewProgress($player));
        }
        //親Missionが消えているProgressを削除する
        $excessMissionIds = array_diff(
            array_map(function (Progress $progress): UUID {
                return $progress->getParentMission()->getId();
            }, self::$progresses[$player]),
            array_map(function (Mission $mission): UUID {
                return $mission->getId();
            }, MissionList::getAll())
        );
        foreach ($excessMissionIds as $excessMissionId) {
            self::remove($player, $excessMissionId);
        }
        foreach (self::$progresses[$player] as $progress) {
            $progress->checkContradiction();
        }
    }

    public static function serializeToArray(): array
    {
        return array_map(function (array $progresses): array {
            /** @var Progress[] $progresses */
            return array_values(array_map(function (Progress $progress): array {
                return $progress->arraySerialize();
            }, $progresses));
        }, self::$progresses);
    }

    public static function initFromArray(array $data): void
    {
        foreach ($data as $player => $arrayProgresses) {
            foreach ($arrayProgresses as $arrayProgress) {
                try {
                    $progress = Progress::arrayDeSerialize($arrayProgress);
                } catch (NotFoundException $exception) {
                    continue;//親ミッションが存在しない(消えていた)場合ここで削除される
                }
                self::add($player, $progress);
            }
        }
    }

    private static function isRegistered(string $player): bool
    {
        return isset(self::$progresses[$player]);
    }

    private static function register(string $player): void
    {
        self::$progresses[$player] = [];
    }

    private function __construct()
    {
        //
    }
}
