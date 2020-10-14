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

namespace pjz9n\mission\mineflow\listener;

use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\utils\Language;
use pjz9n\mission\Main;
use pjz9n\mission\mineflow\language\MineflowLanguage;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class MineflowLanguageCommandListener implements Listener
{
    public function onCommand(CommandEvent $event): void
    {
        $command = $event->getCommand();
        //https://github.com/pmmp/PocketMine-MP/blob/3.6.2/src/pocketmine/command/SimpleCommandMap.php#L250-L261
        $args = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $command, $matches);
        foreach ($matches[0] as $k => $_) {
            for ($i = 1; $i <= 2; ++$i) {
                if ($matches[$i][$k] !== "") {
                    $args[$k] = stripslashes($matches[$i][$k]);
                    break;
                }
            }
        }
        $sentCommandLabel = "";
        $target = Server::getInstance()->getCommandMap()->matchCommand($sentCommandLabel, $args);
        if ($target === null) return;
        if ($target instanceof MineflowCommand && array_shift($args) === "language") {
            //https://github.com/aieuo/Mineflow/blob/b58e3247b2ca2d1aa7b3e6554b23692b30628dfe/src/aieuo/mineflow/command/subcommand/LanguageCommand.php#L11-L19
            if (!isset($args[0])) {
                return;
            }
            if (!Language::isAvailableLanguage($args[0])) {
                return;
            }
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick): void {
                MineflowLanguage::update();
            }), 1);//TODO: fixme If you don't need this 1tick delay stupid way, send a PR :)
        }
    }
}
