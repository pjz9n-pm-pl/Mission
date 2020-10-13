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
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\reward\Reward;
use pjz9n\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RewardActionSelectForm extends AbstractMenuForm
{
    /** @var Mission */
    private $mission;

    /** @var Reward */
    private $reward;

    public function __construct(Mission $mission, Reward $reward)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("reward.edit"),
            LanguageHolder::get()->translateString("reward.type")
            . ": " . $reward::getType() . TextFormat::EOL
            . LanguageHolder::get()->translateString("reward.detail")
            . ": " . $reward->getDetail() . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("reward.edit.pleaseselect"),
            [
                new MenuOption(LanguageHolder::get()->translateString("reward.edit.setting")),
                new MenuOption(LanguageHolder::get()->translateString("reward.edit.remove")),
                new MenuOption(LanguageHolder::get()->translateString("ui.back")),
            ]
        );
        $this->mission = $mission;
        $this->reward = $reward;
    }

    public function onSubmit(Player $player, int $selectedOption): void
    {
        switch ($selectedOption) {
            case 0:
                $player->sendForm(new RewardEditForm($this->mission, $this->reward));
                break;
            case 1:
                $player->sendForm(new RewardRemoveConfirmForm($this->mission, $this->reward));
                break;
            case 2:
                $player->sendForm(new RewardListForm($this->mission));
                break;
        }
    }
}
