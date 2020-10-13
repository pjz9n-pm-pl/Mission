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

namespace pjz9n\mission\mineflow\trigger\event;

use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pjz9n\mission\event\RewardReceiveEvent;
use pjz9n\mission\mineflow\variable\Variables;

class RewardReceiveEventTrigger extends EventTrigger
{
    public function __construct(string $subKey = "")
    {
        parent::__construct(RewardReceiveEvent::class, $subKey);
    }

    /**
     * @param RewardReceiveEvent $event
     */
    public function getVariables($event): array
    {
        $player = $event->getPlayer();
        $reward = $event->getReward();
        return array_merge(
            DefaultVariables::getPlayerVariables($player),
            Variables::getRewardVariables($reward),
        );
    }

    public function getVariablesDummy(): array
    {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "reward" => new DummyVariable("reward", "reward"),
        ];
    }
}
