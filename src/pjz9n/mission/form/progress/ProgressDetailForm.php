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

namespace pjz9n\mission\form\progress;

use dktapps\pmforms\MenuOption;
use pjz9n\mission\form\generic\ErrorForm;
use pjz9n\mission\form\generic\MessageForm;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\progress\Progress;
use pjz9n\mission\reward\exception\FailedProcessRewardException;
use pjz9n\mission\util\Utils;
use pjz9n\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ReflectionException;

class ProgressDetailForm extends AbstractMenuForm
{
    /** @var Player */
    private $player;

    /** @var Progress */
    private $progress;

    public function __construct(Player $player, Progress $progress)
    {
        $mission = $progress->getParentMission();
        if ($progress->canRewardReceive()) {
            $stateMessage = TextFormat::BOLD . TextFormat::YELLOW
                . LanguageHolder::get()->translateString("reward.can.receive")
                . TextFormat::RESET . TextFormat::EOL;
        } else if ($progress->isProgressing()) {
            $stateMessage = TextFormat::BOLD . TextFormat::RED
                . LanguageHolder::get()->translateString("mission.underchallenge")
                . TextFormat::RESET . TextFormat::EOL;
        } else {
            $stateMessage = TextFormat::BOLD . TextFormat::GRAY
                . LanguageHolder::get()->translateString("mission.completed")
                . TextFormat::RESET . TextFormat::EOL;
        }
        $reciptState = $progress->isRewardReceived()
            ? LanguageHolder::get()->translateString("ui.yes")
            : LanguageHolder::get()->translateString("ui.no");
        parent::__construct(
            LanguageHolder::get()->translateString("mission.detail"),
            $stateMessage . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.name")
            . ": " . $mission->getName() . TextFormat::EOL
            . LanguageHolder::get()->translateString("detail")
            . ": " . $mission->getDetail() . TextFormat::EOL
            . LanguageHolder::get()->translateString("mission.levelofachievement")
            . ": {$progress->getCurrentStep()}/{$mission->getTargetStep()} ({$progress->getProgressPercent()}％) " . TextFormat::EOL
            . LanguageHolder::get()->translateString("reward.recipt.state")
            . ": " . $reciptState . TextFormat::EOL
            . LanguageHolder::get()->translateString("reward.recipt.count")
            . ": "
            . LanguageHolder::get()->translateString("ntimes", [(string)($progress->getCurrentLoopCount())])
            . " ("
            . LanguageHolder::get()->translateString("max")
            . " " . LanguageHolder::get()->translateString("ntimes", [$mission->getLoopCount()])
            . ")" . TextFormat::EOL
            . LanguageHolder::get()->translateString("reward") . TextFormat::EOL
            . Utils::getRewardsItemizationList($mission->getRewards()) . TextFormat::EOL,
            [
                new MenuOption(TextFormat::DARK_GRAY . LanguageHolder::get()->translateString("reward.recipt")),
                new MenuOption(LanguageHolder::get()->translateString("ui.back")),
            ]
        );
        $this->player = $player;
        $this->progress = $progress;
    }

    /**
     * @throws ReflectionException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        switch ($selectedOption) {
            case 0:
                try {
                    $this->progress->sendReward();
                } catch (FailedProcessRewardException $exception) {
                    $player->sendForm(new ErrorForm($exception->getMessage(), new self($this->player, $this->progress)));
                    return;
                }
                $message = LanguageHolder::get()->translateString("reward.recipt.success") . TextFormat::EOL
                    . Utils::getRewardsItemizationList($this->progress->getParentMission()->getRewards());
                $player->sendForm(new MessageForm($message, new ProgressListForm($player)));
                break;
            case 1:
                $player->sendForm(new ProgressListForm($player));
                break;
        }
    }
}
