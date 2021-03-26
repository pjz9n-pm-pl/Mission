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

namespace pjz9n\mission\reward;

use aieuo\mineflow\trigger\TriggerHolder;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mineflow\trigger\MissionRewardTrigger;
use pjz9n\mission\mineflow\trigger\TriggerIds;
use pjz9n\mission\reward\exception\FailedProcessRewardException;
use pocketmine\Player;
use pocketmine\utils\UUID;

class MineflowReward extends Reward
{
    protected static $unique = true;

    public static function getType(): string
    {
        return LanguageHolder::get()->translateString("reward.mineflowreward.type");
    }

    public function __construct(UUID $parentMissionId, string $detail)
    {
        parent::__construct($parentMissionId, $detail);
    }

    public function throwIfCantProcess(Player $player): void
    {
        $holder = TriggerHolder::getInstance();
        if (!$holder->existsRecipeByString(TriggerIds::TRIGGER_MISSION_REWARD, $this->getParentMissionId()->toString())) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("reward.mineflowreward.recipe.notfound"), $this);
        }
    }

    public function process(Player $player): void
    {
        $holder = TriggerHolder::getInstance();
        $trigger = MissionRewardTrigger::create($this->getParentMissionId()->toString());
        $recipes = $holder->getRecipes($trigger);
        if ($recipes !== null) {
            $recipes->executeAll($player, $trigger->getVariables($this));
        }
    }
}
