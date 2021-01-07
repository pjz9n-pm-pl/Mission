<?php

/**
 * Copyright (c) 2021 PJZ9n.
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
 *
 * @name MissionAddon
 * @version 1.0.0
 * @main pjz9n\scorehud\addon\MissionAddon
 * @depend Mission
 */

declare(strict_types=1);

namespace pjz9n\scorehud\addon;

use JackMD\ScoreHud\addon\AddonBase;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\progress\ProgressList;
use pocketmine\Player;

class MissionAddon extends AddonBase
{
    public function getProcessedTags(Player $player): array
    {
        $pinnedProgress = ProgressList::getPinned($player->getName());
        return [
            "{pinned_mission}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getName(),
            "{pinned_mission_detail}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getDetail(),
            "{pinned_mission_id}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getId(),
            "{pinned_mission_shortid}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getShortId(),
            "{pinned_mission_targetstep}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getTargetStep(),
            "{pinned_mission_group}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getParentMission()->getGroup() ?? LanguageHolder::get()->translateString("unspecified"),
            "{pinned_mission_currentstep}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getCurrentStep(),
            "{pinned_mission_percent}" => $pinnedProgress === null
                ? LanguageHolder::get()->translateString("unspecified")
                : $pinnedProgress->getProgressPercent(),
        ];
    }
}
