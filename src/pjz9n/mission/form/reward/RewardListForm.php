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

namespace pjz9n\mission\form\reward;

use dktapps\pmforms\MenuOption;
use pjz9n\mission\form\mission\MissionActionSelectForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\reward\Reward;
use pjz9n\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;

class RewardListForm extends AbstractMenuForm
{
    /** @var Mission */
    private $mission;

    /** @var Reward[] */
    private $rewards;

    public function __construct(Mission $mission)
    {
        $this->rewards = array_values($mission->getRewards());
        $options = [];
        $options[] = new MenuOption(LanguageHolder::get()->translateString("reward.edit.add"));
        foreach ($this->rewards as $reward) {
            $options[] = new MenuOption($reward::getType() . ": " . $reward->getDetail());
        }
        $options[] = new MenuOption(LanguageHolder::get()->translateString("ui.back"));
        parent::__construct(
            LanguageHolder::get()->translateString("reward.list"),
            LanguageHolder::get()->translateString("reward.pleaseselect"),
            $options
        );
        $this->mission = $mission;
    }

    public function onSubmit(Player $player, int $selectedOption): void
    {
        if ($selectedOption === 0) {
            $player->sendForm(new RewardAddTypeSelectForm($this->mission));
            return;
        }
        if (count($this->rewards) < $selectedOption) {
            $player->sendForm(new MissionActionSelectForm($this->mission));
            return;
        }
        $selectedReward = $this->rewards[$selectedOption - 1];
        $player->sendForm(new RewardActionSelectForm($this->mission, $selectedReward));
    }
}
