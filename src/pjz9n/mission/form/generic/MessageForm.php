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

namespace pjz9n\mission\form\generic;

use pjz9n\mission\language\LanguageHolder;
use pjz9n\pmformsaddon\AbstractModalForm;
use pocketmine\form\Form;
use pocketmine\Player;

class MessageForm extends AbstractModalForm
{
    /** @var Form|null */
    private $back;

    public function __construct(string $message, ?Form $back = null)
    {
        parent::__construct(
            LanguageHolder::get()->translateString("message"),
            $message,
            $back !== null ? LanguageHolder::get()->translateString("ui.back") : LanguageHolder::get()->translateString("ui.close"),
            LanguageHolder::get()->translateString("ui.close")
        );
        $this->back = $back;
    }

    public function onSubmit(Player $player, bool $choice): void
    {
        if ($this->back !== null) {
            if ($choice) {
                $player->sendForm($this->back);
            }
        }
    }
}
