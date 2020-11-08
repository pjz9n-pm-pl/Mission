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

namespace pjz9n\mission\command\sub;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pjz9n\mission\form\mission\MissionActionSelectForm;
use pjz9n\mission\form\mission\MissionListForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\MissionList;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MissionEditCommand extends BaseSubCommand
{
    public function __construct()
    {
        parent::__construct(
            "edit",
            LanguageHolder::get()->translateString("command.mission.edit.description")
        );
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("mission.command.mission.edit");
        $this->registerArgument(0, new RawStringArgument("mission", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            LanguageHolder::get()->translateString("command.player.only");
            return;
        }
        if (isset($args["mission"])) {
            $missionArgument = $args["mission"];
            foreach (MissionList::getAll() as $mission) {
                if (
                    $mission->getName() === $missionArgument
                    || $mission->getId()->toString() === $missionArgument
                    || $mission->getShortId() === $missionArgument
                ) {
                    $sender->sendForm(new MissionActionSelectForm($mission));
                    return;
                }
            }
        }
        $sender->sendForm(new MissionListForm());
    }
}
