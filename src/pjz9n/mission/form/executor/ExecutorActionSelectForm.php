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

namespace pjz9n\mission\form\executor;

use dktapps\pmforms\MenuOption;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\executor\Executor;
use pjz9n\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ReflectionException;

class ExecutorActionSelectForm extends AbstractMenuForm
{
    /** @var Executor */
    private $executor;

    /**
     * @throws ReflectionException
     */
    public function __construct(Executor $executor)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("executor.edit"),
            LanguageHolder::get()->translateString("executor.type")
            . ": " . $executor::getType() . TextFormat::EOL
            . LanguageHolder::get()->translateString("executor.detail")
            . ": " . $executor->getDetail() . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("executor.edit.pleaseselect"),
            [
                new MenuOption(LanguageHolder::get()->translateString("executor.edit.setting")),
                new MenuOption(LanguageHolder::get()->translateString("executor.edit.remove")),
                new MenuOption(LanguageHolder::get()->translateString("ui.back")),
            ]
        );
        $this->executor = $executor;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        switch ($selectedOption) {
            case 0:
                $player->sendForm(new ExecutorSettingForm($this->executor));
                break;
            case 1:
                $player->sendForm(new ExecutorRemoveConfirmForm($this->executor));
                break;
            case 2:
                $player->sendForm(new ExecutorListForm($this->executor->getParentMission()));
                break;
        }
    }
}
