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

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use pjz9n\mission\form\Elements;
use pjz9n\mission\form\generic\MessageForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\Main;
use pjz9n\mission\pmformsaddon\AbstractCustomForm;
use pocketmine\Player;

class SettingMissionCompleteMessageForm extends AbstractCustomForm
{
    public function __construct()
    {
        parent::__construct(
            LanguageHolder::get()->translateString("setting.missioncomplete.message"),
            [
                new Label(
                    "detail",
                    LanguageHolder::get()->translateString("setting.missioncomplete.message.detail")
                ),
                new Toggle(
                    "enabled",
                    LanguageHolder::get()->translateString("enabled"),
                    Main::getInstance()->getConfig()->get("send-missioncomplete-message")),
                new Input(
                    "message",
                    LanguageHolder::get()->translateString("message"),
                    "",
                    (string)Main::getInstance()->getConfig()->get("missioncomplete-message")),
                Elements::getCancellToggle(),
            ]
        );
    }

    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new SettingForm());
            return;
        }
        Main::getInstance()->getConfig()->set("send-missioncomplete-message", $response->getBool("enabled"));
        Main::getInstance()->getConfig()->set("missioncomplete-message", $response->getString("message"));
        $player->sendForm(new MessageForm(LanguageHolder::get()->translateString("setting.success"), new SettingForm()));
    }
}
