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

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use pjz9n\mission\form\Elements;
use pjz9n\mission\form\generic\ErrorForm;
use pjz9n\mission\form\generic\MessageForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\util\Utils;
use pjz9n\pmformsaddon\AbstractCustomForm;
use pocketmine\Player;
use TypeError;

class MissionAddForm extends AbstractCustomForm
{
    public function __construct()
    {
        parent::__construct(
            LanguageHolder::get()->translateString("mission.edit.add"),
            [
                new Input("name", LanguageHolder::get()->translateString("mission.name")),
                new Input("detail", LanguageHolder::get()->translateString("mission.detail")),
                new Input("loopCount", LanguageHolder::get()->translateString("mission.maxachievementcount"), "", "1"),
                new Input("targetStep", LanguageHolder::get()->translateString("mission.targetstep"), "", "1"),
                Elements::getCancellToggle(),
            ]
        );
    }

    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new MissionListForm());
            return;
        }
        $name = $response->getString("name");
        $detail = $response->getString("detail");
        try {
            $loopCount = Utils::filterToInteger($response->getString("loopCount"));
        } catch (TypeError $exception) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("mission.maxachievementcount"),
            ]), new self()));
            return;
        }
        if ($loopCount < 1) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.min", [
                LanguageHolder::get()->translateString("mission.maxachievementcount"),
                1,
            ]), new self()));
            return;
        }
        try {
            $targetStep = Utils::filterToInteger($response->getString("targetStep"));
        } catch (TypeError $exception) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("mission.targetstep"),
            ]), new self()));
            return;
        }
        if ($targetStep < 1) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.min", [
                LanguageHolder::get()->translateString("mission.targetstep"),
                1,
            ]), new self()));
            return;
        }
        $newMission = Mission::create($name, $detail, $loopCount, $targetStep);
        MissionList::add($newMission);
        $player->sendForm(new MessageForm(LanguageHolder::get()->translateString("mission.edit.add.success"), new MissionListForm()));
    }
}
