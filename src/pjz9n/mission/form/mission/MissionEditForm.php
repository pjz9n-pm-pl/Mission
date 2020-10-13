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
use pjz9n\mission\util\Utils;
use pjz9n\pmformsaddon\AbstractCustomForm;
use pocketmine\Player;
use TypeError;

class MissionEditForm extends AbstractCustomForm
{
    /** @var Mission */
    private $mission;

    public function __construct(Mission $mission)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("mission.edit.generic"),
            [
                new Input("name", LanguageHolder::get()->translateString("mission.name"), "", $mission->getName()),
                new Input("detail", LanguageHolder::get()->translateString("mission.detail"), "", $mission->getDetail()),
                new Input("loopCount", LanguageHolder::get()->translateString("mission.maxachievementcount"), "", (string)$mission->getLoopCount()),
                new Input("targetStep", LanguageHolder::get()->translateString("mission.targetstep"), "", (string)$mission->getTargetStep()),
                Elements::getCancellToggle(),
            ]
        );
        $this->mission = $mission;
    }

    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new MissionActionSelectForm($this->mission));
            return;
        }
        $name = $response->getString("name");
        $detail = $response->getString("detail");
        try {
            $loopCount = Utils::filterToInteger($response->getString("loopCount"));
        } catch (TypeError $exception) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("mission.maxachievementcount"),
            ]), new self($this->mission)));
            return;
        }
        if ($loopCount < 1) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.min", [
                LanguageHolder::get()->translateString("mission.maxachievementcount"),
                1,
            ]), new self($this->mission)));
            return;
        }
        try {
            $targetStep = Utils::filterToInteger($response->getString("targetStep"));
        } catch (TypeError $exception) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("mission.targetstep"),
            ]), new self($this->mission)));
            return;
        }
        if ($targetStep < 1) {
            $player->sendForm(new ErrorForm(LanguageHolder::get()->translateString("error.validate.min", [
                LanguageHolder::get()->translateString("mission.targetstep"),
                1,
            ]), new self($this->mission)));
            return;
        }
        $this->mission->setName($name);
        $this->mission->setDetail($detail);
        $this->mission->setLoopCount($loopCount);
        $this->mission->setTargetStep($targetStep);
        $player->sendForm(new MessageForm(LanguageHolder::get()->translateString("mission.edit.generic.success"), new MissionActionSelectForm($this->mission)));
    }
}
