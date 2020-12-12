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

use CortexPE\Commando\BaseSubCommand;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\util\InquiryKeyGenerateException;
use pjz9n\mission\util\Utils;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils as PMUtils;

class MissionInfoCommand extends BaseSubCommand
{
    public function __construct()
    {
        parent::__construct(
            "info",
            LanguageHolder::get()->translateString("command.mission.info.description"),
            ["i"]
        );
    }

    protected function prepare(): void
    {
        $this->setPermission("mission.command.mission.info");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $mineflow = Server::getInstance()->getPluginManager()->getPlugin("Mineflow");
        try {
            $key = Utils::generateInquiryKey();
            $rawKey = Utils::generateInquiryKey(false);
        } catch (InquiryKeyGenerateException $exception) {
            $key = LanguageHolder::get()->translateString("error") . ": " . $exception->getMessage();
            $rawKey = $key;
        }
        $sender->sendMessage(
            Utils::generateLine(LanguageHolder::get()->translateString("info.this.plugin")) . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.soft.name")
            . ": "
            . Server::getInstance()->getName() . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.pmmp.version")
            . ": "
            . Server::getInstance()->getPocketMineVersion() . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.pmmp.api.version")
            . ": "
            . Server::getInstance()->getApiVersion() . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.php.version")
            . ": "
            . PHP_VERSION . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.plugin.version")
            . ": "
            . $this->getPlugin()->getDescription()->getVersion() . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.os.type")
            . ": "
            . PMUtils::getOS() . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.mineflow.version")
            . ": "
            . ($mineflow === null ? "None" : $mineflow->getDescription()->getVersion()) . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("info.inquiry.key") . TextFormat::EOL
            . "----- BEGIN -----"
            . $key . TextFormat::EOL
            . "----- END -----" . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.inquiry.key.detail") . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.inquiry.key.warning1") . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.inquiry.key.warning2") . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.inquiry.key.warning3") . TextFormat::EOL
            . LanguageHolder::get()->translateString("info.inquiry.key.raw")
            . ": "
            . $rawKey . TextFormat::EOL
            . Utils::generateLine() . TextFormat::EOL
        );
    }
}
