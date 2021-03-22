<?php

/*
 * Copyright (c) 2021 PJZ9n.
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

namespace pjz9n\mission;

use pjz9n\mission\lang\Lang;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase
{
    public function onEnable(): void
    {
        new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "locale" => "eng",
        ]);

        Lang::init($this->getFile() . "resources/locale/", "eng");
        Lang::set($this->getConfig()->get("locale"));
        $this->getLogger()->info(Lang::get()->translateString("language.selected", [
            Lang::get()->getName(),
            Lang::get()->getLang(),
        ]));
    }
}
