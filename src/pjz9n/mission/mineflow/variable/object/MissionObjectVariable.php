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

namespace pjz9n\mission\mineflow\variable\object;

use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\reward\Reward;

class MissionObjectVariable extends ObjectVariable
{
    public function __construct(Mission $value, ?string $str = null)
    {
        parent::__construct($value, $str);
    }

    public function __toString(): string
    {
        return $this->getMission()->getName();
    }

    public function getValueFromIndex(string $index): ?Variable
    {
        $mission = $this->getMission();
        switch ($index) {
            case "id":
                return new StringVariable($mission->getId()->toString());
            case "name":
                return new StringVariable($mission->getName());
            case "detail":
                return new StringVariable($mission->getDetail());
            case "rewards":
                return new ListVariable(array_values(array_map(function (Reward $reward): RewardObjectVariable {
                    return new RewardObjectVariable($reward);
                }, $mission->getRewards())), "rewards");
            case "loopCount":
                return new NumberVariable($mission->getLoopCount());
            case "targetStep":
                return new NumberVariable($mission->getTargetStep());
        }
        return null;
    }

    public function getMission(): Mission
    {
        /** @var Mission $value */
        $value = $this->getValue();
        return $value;
    }
}
