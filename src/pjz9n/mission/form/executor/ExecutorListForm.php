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
use pjz9n\mission\form\mission\MissionActionSelectForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\executor\Executor;
use pjz9n\mission\mission\executor\ExecutorList;
use pjz9n\mission\mission\Mission;
use pjz9n\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;
use ReflectionException;

class ExecutorListForm extends AbstractMenuForm
{
    /** @var Mission */
    private $mission;

    /** @var Executor[] */
    private $executors;

    /**
     * @throws ReflectionException
     */
    public function __construct(Mission $mission)
    {
        $this->executors = array_values(ExecutorList::getAll($mission->getId()));
        $options = [];
        $options[] = new MenuOption(LanguageHolder::get()->translateString("executor.edit.add"));
        foreach ($this->executors as $executor) {
            $options[] = new MenuOption($executor->getDetail());
        }
        $options[] = new MenuOption(LanguageHolder::get()->translateString("ui.back"));
        parent::__construct(
            LanguageHolder::get()->translateString("executor.list"),
            LanguageHolder::get()->translateString("executor.pleaseselect"),
            $options
        );
        $this->mission = $mission;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        if ($selectedOption === 0) {
            $player->sendForm(new ExecutorAddTypeSelectForm($this->mission));
            return;
        }
        if (count($this->executors) < $selectedOption) {
            $player->sendForm(new MissionActionSelectForm($this->mission));
            return;
        }
        $selectedExecutor = $this->executors[$selectedOption - 1];
        $player->sendForm(new ExecutorActionSelectForm($selectedExecutor));
    }
}
