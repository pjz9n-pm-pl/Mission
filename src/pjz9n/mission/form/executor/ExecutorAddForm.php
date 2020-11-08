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

use dktapps\pmforms\CustomFormResponse;
use pjz9n\mission\form\Elements;
use pjz9n\mission\form\generic\ErrorForm;
use pjz9n\mission\form\generic\MessageForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\executor\Executor;
use pjz9n\mission\mission\executor\ExecutorList;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\pmformsaddon\AbstractCustomForm;
use pjz9n\mission\util\FormResponseProcessFailedException;
use pocketmine\Player;
use ReflectionException;

class ExecutorAddForm extends AbstractCustomForm
{
    /** @var Mission */
    private $mission;

    /** @var Executor class string (for ide) */
    private $executorType;

    public function __construct(Mission $mission, string $executorType)
    {
        /** @var Executor $executorType for ide */
        parent::__construct(
            LanguageHolder::get()->translateString("executor.edit.add") . " > " . $executorType::getType(),
            array_merge($executorType::getCreateFormElements(), [
                Elements::getCancellToggle(),
            ])
        );
        $this->mission = $mission;
        $this->executorType = $executorType;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new ExecutorAddTypeSelectForm($this->mission));
            return;
        }
        try {
            $newExecutor = $this->executorType::createByFormResponse($response, $this->mission);
        } catch (FormResponseProcessFailedException $exception) {
            $player->sendForm(new ErrorForm($exception->getMessage(), new self($this->mission, $this->executorType)));
            return;
        }
        ExecutorList::add($newExecutor);
        $player->sendForm(new MessageForm(LanguageHolder::get()->translateString("executor.edit.add.success"), new ExecutorListForm($this->mission)));
    }
}
