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

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pjz9n\mission\mission\progress\Progress;

class ProgressObjectVariable extends ObjectVariable
{
    public function __construct(Progress $value, ?string $str = null)
    {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable
    {
        $progress = $this->getProgress();
        switch ($index) {
            case "rewardReceived":
                return new BoolVariable($progress->isRewardReceived());
            case "currentLoopCount":
                return new NumberVariable($progress->getCurrentLoopCount());
            case "currentStep":
                return new NumberVariable($progress->getCurrentStep());
        }
        return null;
    }

    public function getProgress(): Progress
    {
        /** @var Progress $value */
        $value = $this->getValue();
        return $value;
    }
}
