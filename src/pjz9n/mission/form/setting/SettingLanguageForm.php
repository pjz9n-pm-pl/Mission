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
use pjz9n\mission\form\generic\MessageForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\pmformsaddon\AbstractMenuForm;
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SettingLanguageForm extends AbstractMenuForm
{
    /** @var string[] */
    private $availableLanguages;

    public function __construct()
    {
        $this->availableLanguages = BaseLang::getLanguageList(LanguageHolder::getLocalePath());
        unset($this->availableLanguages[LanguageHolder::get()->getLang()]);
        $options = [];
        $options[] = new MenuOption(LanguageHolder::get()->translateString("ui.back"));
        foreach ($this->availableLanguages as $lang => $name) {
            $options[] = new MenuOption("{$name} ({$lang})");
        }
        parent::__construct(
            LanguageHolder::get()->translateString("setting.language"),
            LanguageHolder::get()->translateString("nowvalue")
            . ": " . LanguageHolder::get()->getName() . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("setting.language.pleaseselect"),
            $options
        );
    }

    public function onSubmit(Player $player, int $selectedOption): void
    {
        if ($selectedOption === 0) {
            $player->sendForm(new SettingForm());
            return;
        }
        $selectedLanguage = array_keys($this->availableLanguages)[$selectedOption - 1];
        LanguageHolder::setLanguage($selectedLanguage);
        $player->sendForm(new MessageForm(LanguageHolder::get()->translateString("language.selected", [
            LanguageHolder::get()->getName(),
            LanguageHolder::get()->getLang(),
        ]), new self()));
    }
}
