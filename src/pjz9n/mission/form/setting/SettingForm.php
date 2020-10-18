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

namespace pjz9n\mission\form\setting;

use dktapps\pmforms\MenuOption;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;

class SettingForm extends AbstractMenuForm
{
    public function __construct()
    {
        parent::__construct(
            LanguageHolder::get()->translateString("setting"),
            LanguageHolder::get()->translateString("setting.pleaseselect"),
            [
                new MenuOption(LanguageHolder::get()->translateString("setting.language")),
                new MenuOption(LanguageHolder::get()->translateString("setting.missioncomplete.message")),
            ]
        );
    }

    public function onSubmit(Player $player, int $selectedOption): void
    {
        switch ($selectedOption) {
            case 0:
                $player->sendForm(new SettingLanguageForm());
                break;
            case 1:
                $player->sendForm(new SettingMissionCompleteMessageForm());
                break;
        }
    }
}
