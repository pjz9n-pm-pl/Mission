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
use pjz9n\mission\util\FormResponseProcessFailedException;
use pjz9n\pmformsaddon\AbstractCustomForm;
use pocketmine\Player;
use ReflectionException;

class ExecutorSettingForm extends AbstractCustomForm
{
    /** @var Executor */
    private $executor;

    public function __construct(Executor $executor)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("executor.edit.setting"),
            array_merge(
                $executor->getSettingFormElements(), [
                    Elements::getCancellToggle(),
                ]
            )
        );
        $this->executor = $executor;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, CustomFormResponse $response): void
    {
        if ($response->getBool("cancel")) {
            $player->sendForm(new ExecutorActionSelectForm($this->executor));
            return;
        }
        try {
            $this->executor->processSettingFormResponse($response);
        } catch (FormResponseProcessFailedException $exception) {
            $player->sendForm(new ErrorForm($exception->getMessage(), new self($this->executor)));
            return;
        }
        $player->sendForm(new MessageForm(
            LanguageHolder::get()->translateString("executor.edit.setting.success"),
            new ExecutorActionSelectForm($this->executor)
        ));
    }
}
