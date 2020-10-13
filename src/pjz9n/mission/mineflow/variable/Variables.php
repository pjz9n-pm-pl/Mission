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

namespace pjz9n\mission\mineflow\variable;

use pjz9n\mission\mineflow\variable\object\MissionObjectVariable;
use pjz9n\mission\mineflow\variable\object\ProgressObjectVariable;
use pjz9n\mission\mineflow\variable\object\RewardObjectVariable;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\progress\Progress;
use pjz9n\mission\reward\Reward;

final class Variables
{
    public static function getMissionVariables(Mission $mission, string $name = "mission"): array
    {
        return [$name => new MissionObjectVariable($mission)];
    }

    public static function getProgressVariables(Progress $progress, string $name = "progress"): array
    {
        return [$name => new ProgressObjectVariable($progress)];
    }

    public static function getRewardVariables(Reward $reward, string $name = "reward"): array
    {
        return [$name => new RewardObjectVariable($reward)];
    }

    private function __construct()
    {
        //
    }
}
