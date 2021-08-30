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

namespace pjz9n\mission\mineflow\trigger;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\utils\Language;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\mission\MissionList;
use pocketmine\utils\UUID;

class MissionRewardTrigger extends Trigger
{
    public static function create(string $key, string $subKey = ""): self
    {
        return new self($key, $subKey);
    }

    public function __construct(string $key, string $subKey = "")
    {
        parent::__construct(TriggerIds::TRIGGER_MISSION_REWARD, $key, $subKey);
    }

    public function __toString(): string
    {
        try {
            $missionName = MissionList::get(UUID::fromString($this->getKey()))->getName();
        } catch (NotFoundException $exception) {
            $missionName = Language::get("trigger.type.missionreward.unknown");
        }
        return Language::get("trigger.type.missionreward") . ": " . $missionName;
    }
}
