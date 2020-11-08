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

namespace pjz9n\mission\command;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pjz9n\mission\command\sub\MissionEditCommand;
use pjz9n\mission\command\sub\MissionSettingCommand;
use pjz9n\mission\form\generic\ErrorForm;
use pjz9n\mission\form\progress\ProgressDetailForm;
use pjz9n\mission\form\progress\ProgressListForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\mission\progress\ProgressList;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class MissionCommand extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "mission",
            LanguageHolder::get()->translateString("command.mission.description"),
            ["mi"]
        );
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("mission.command.mission");
        $this->registerSubCommand(new MissionEditCommand());
        $this->registerSubCommand(new MissionSettingCommand());
        $this->registerArgument(0, new RawStringArgument("mission", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            LanguageHolder::get()->translateString("command.player.only");
            return;
        }
        if (isset($args["mission"])) {
            /** @var string $missionArgument This is PHPStan wants */
            $missionArgument = $args["mission"];
            foreach (MissionList::getAll() as $mission) {
                if (
                    $mission->getName() === $missionArgument
                    || $mission->getId()->toString() === $missionArgument
                    || $mission->getShortId() === $missionArgument
                ) {
                    $sender->sendForm(new ProgressDetailForm($sender, ProgressList::get($sender->getName(), $mission->getId())));
                    return;
                }
            }
        }
        if (count(ProgressList::getAll($sender->getName())) < 1) {
            $sender->sendForm(new ErrorForm(LanguageHolder::get()->translateString("mission.noavailable")));
        } else {
            $sender->sendForm(new ProgressListForm($sender));
        }
    }
}
