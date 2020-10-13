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

namespace pjz9n\mission\mission\sort;

use pjz9n\mission\mission\progress\Progress;

final class Sorter
{
    public const COMPLETED_REWARD_NOT_RECEIVED = 0;

    public const COMPLETED_REWARD_RECEIVED = 1;

    public const NOT_COMPLETED_REWARD_NOT_RECEIVED = 2;

    public const NOT_COMPLETED_REWARD_RECEIVED = 3;

    /**
     * @param Progress[] $progresses
     * @param int[] $order
     */
    public static function sortProgresses(array $progresses, array $order): array
    {
        $result = [];
        foreach ($order as $type) {
            $result = array_merge($result, self::filterProgresses($progresses, $type));
        }
        return $result;
    }

    /**
     * @param Progress[] $progresses
     */
    public static function filterProgresses(array $progresses, int $type): array
    {
        $result = [];
        foreach ($progresses as $progress) {
            switch ($type) {
                case self::COMPLETED_REWARD_NOT_RECEIVED:
                    if ($progress->isCompleted() && !$progress->isRewardReceived()) $result[] = $progress;
                    break;
                case self::COMPLETED_REWARD_RECEIVED:
                    if ($progress->isCompleted() && $progress->isRewardReceived()) $result[] = $progress;
                    break;
                case self::NOT_COMPLETED_REWARD_NOT_RECEIVED:
                    if (!$progress->isCompleted() && !$progress->isRewardReceived()) $result[] = $progress;
                    break;
                case self::NOT_COMPLETED_REWARD_RECEIVED:
                    if (!$progress->isCompleted() && $progress->isRewardReceived()) $result[] = $progress;
                    break;

            }
        }
        return $result;
    }

    private function __construct()
    {
        //
    }
}
