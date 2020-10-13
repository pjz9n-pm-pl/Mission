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

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use pjz9n\mission\form\Elements;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\reward\Reward;
use pjz9n\mission\reward\Rewards;
use pjz9n\pmformsaddon\AbstractCustomForm;
use pocketmine\Player;

class RewardAddTypeSelectForm extends AbstractCustomForm
{
    /** @var Mission */
    private $mission;

    /** @var string[] class string */
    private $rewardTypes;

    public function __construct(Mission $mission)
    {
        $this->rewardTypes = array_values(Rewards::getAll());
        $options = array_map(function (string $rewardClass): string {
            /** @var Reward $rewardClass for ide */
            return $rewardClass::getType();
        }, $this->rewardTypes);
        parent::__construct(
            LanguageHolder::get()->translateString("reward.edit.add"),
            [
                new Dropdown("rewardType", LanguageHolder::get()->translateString("reward.type"), $options),
                Elements::getCancellToggle(),
            ]
        );
        $this->mission = $mission;
    }

    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new RewardListForm($this->mission));
            return;
        }
        $selectedRewardType = $this->rewardTypes[$response->getInt("rewardType")];
        $player->sendForm(new RewardAddForm($this->mission, $selectedRewardType));
    }
}
