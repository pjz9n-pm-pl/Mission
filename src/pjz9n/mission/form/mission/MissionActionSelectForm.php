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

namespace pjz9n\mission\form\mission;

use dktapps\pmforms\MenuOption;
use pjz9n\mission\form\executor\ExecutorListForm;
use pjz9n\mission\form\reward\RewardListForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\executor\ExecutorList;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\pmformsaddon\AbstractMenuForm;
use pjz9n\mission\util\Utils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ReflectionException;

class MissionActionSelectForm extends AbstractMenuForm
{
    /** @var Mission */
    private $mission;

    public function __construct(Mission $mission)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("mission.edit"),
            LanguageHolder::get()->translateString("shortid")
            . ": " . $mission->getShortId() . TextFormat::EOL
            . LanguageHolder::get()->translateString("group")
            . ": " . ($mission->getGroup() ?? LanguageHolder::get()->translateString("unspecified")) . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.name")
            . ": " . $mission->getName() . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.detail")
            . ": " . $mission->getDetail() . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.maxachievementcount")
            . ": " . $mission->getLoopCount() . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.targetstep")
            . ": " . $mission->getTargetStep() . TextFormat::EOL
            . LanguageHolder::get()->translateString("reward") . TextFormat::EOL
            . Utils::getRewardsItemizationList($mission->getRewards()) . TextFormat::EOL
            . LanguageHolder::get()->translateString("executor") . TextFormat::EOL
            . Utils::getExecutorsItemizationList(ExecutorList::getAll($mission->getId())) . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("mission.edit.pleaseselect"),
            [
                new MenuOption(LanguageHolder::get()->translateString("mission.edit.generic")),
                new MenuOption(LanguageHolder::get()->translateString("mission.edit.reward")),
                new MenuOption(LanguageHolder::get()->translateString("executor.edit")),
                new MenuOption(LanguageHolder::get()->translateString("mission.edit.remove")),
                new MenuOption(LanguageHolder::get()->translateString("ui.back")),
            ]
        );
        $this->mission = $mission;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        switch ($selectedOption) {
            case 0:
                $player->sendForm(new MissionEditForm($this->mission));
                break;
            case 1:
                $player->sendForm(new RewardListForm($this->mission));
                break;
            case 2:
                $player->sendForm(new ExecutorListForm($this->mission));
                break;
            case 3:
                $player->sendForm(new MissionRemoveConfirmForm($this->mission));
                break;
            case 4:
                $player->sendForm(new MissionListForm());
                break;
        }
    }
}
