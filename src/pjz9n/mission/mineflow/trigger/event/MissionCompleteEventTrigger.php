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
use pjz9n\mission\event\MissionCompleteEvent;
use pjz9n\mission\mineflow\variable\Variables;

class MissionCompleteEventTrigger extends EventTrigger
{
    public function __construct(string $subKey = "")
    {
        parent::__construct(MissionCompleteEvent::class, $subKey);
    }

    /**
     * @param MissionCompleteEvent $event
     */
    public function getVariables($event): array
    {
        $player = $event->getPlayer();
        $misison = $event->getMission();
        $progress = $event->getProgress();
        return array_merge(
            DefaultVariables::getPlayerVariables($player),
            Variables::getMissionVariables($misison),
            Variables::getProgressVariables($progress),
        );
    }

    public function getVariablesDummy(): array
    {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "mission" => new DummyVariable("mission"),
            "progress" => new DummyVariable("progress"),
        ];
    }
}
