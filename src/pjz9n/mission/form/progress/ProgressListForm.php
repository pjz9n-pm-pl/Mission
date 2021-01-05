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
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\progress\Progress;
use pjz9n\mission\mission\progress\ProgressList;
use pjz9n\mission\mission\sort\Sorter;
use pjz9n\mission\pmformsaddon\AbstractMenuForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ProgressListForm extends AbstractMenuForm
{
    /** @var Progress[] */
    private $progresses;

    /** @var string|null */
    private $group;

    public function __construct(Player $player, ?string $group = null)
    {
        $this->progresses = array_values(Sorter::sortProgresses($progresses ?? ProgressList::getAll($player->getName()), [
            Sorter::PINNED,
            Sorter::COMPLETED_REWARD_NOT_RECEIVED,
            Sorter::NOT_COMPLETED_REWARD_NOT_RECEIVED,
            Sorter::COMPLETED_REWARD_RECEIVED,
        ]));
        if ($group !== null) {
            $this->progresses = Sorter::filterProgressByGroup($this->progresses, $group);
        }
        $options = [];
        static $prefixes = [
            TextFormat::BOLD . TextFormat::YELLOW . "#" . TextFormat::RESET,
            TextFormat::BOLD . TextFormat::RED . "#" . TextFormat::RESET,
            TextFormat::BOLD . TextFormat::GRAY . "#" . TextFormat::RESET,
        ];
        foreach ($this->progresses as $progress) {
            $mission = $progress->getParentMission();
            if ($progress->canRewardReceive()) {
                $prefix = $prefixes[0];
            } else if ($progress->isProgressing()) {
                $prefix = $prefixes[1];
            } else {
                $prefix = $prefixes[2];
            }
            $prefix .= " ";
            $options[] = new MenuOption(
                ($progress->isPinned() ? TextFormat::GREEN . "★" . TextFormat::RESET . " " : "") . $prefix . $mission->getName() . TextFormat::EOL
                . "{$progress->getCurrentStep()}/{$mission->getTargetStep()} ({$progress->getProgressPercent()}％) "
                . LanguageHolder::get()->translateString("reward.recipt.count")
                . ": "
                . LanguageHolder::get()->translateString("ntimes", [
                    (string)($progress->getCurrentLoopCount()) . "/" . (string)($progress->getParentMission()->getLoopCount()),
                ])
            );
        }
        parent::__construct(
            LanguageHolder::get()->translateString("mission.list"),
            LanguageHolder::get()->translateString("group")
            . ": " . ($group ?? LanguageHolder::get()->translateString("unspecified")) . TextFormat::EOL
            . TextFormat::EOL
            . TextFormat::GREEN . "★" . TextFormat::RESET . " - "
            . LanguageHolder::get()->translateString("mission.pinned")
            . TextFormat::EOL
            . TextFormat::EOL
            . $prefixes[0] . " - "
            . LanguageHolder::get()->translateString("reward.can.receive")
            . TextFormat::EOL
            . $prefixes[1] . " - "
            . LanguageHolder::get()->translateString("mission.underchallenge")
            . TextFormat::EOL
            . $prefixes[2] . " - "
            . LanguageHolder::get()->translateString("mission.completed")
            . TextFormat::EOL
            . TextFormat::EOL . LanguageHolder::get()->translateString("mission.pleaseselect"),
            $options
        );
        $this->group = $group;
    }

    public function onSubmit(Player $player, int $selectedOption): void
    {
        $selectedProgress = $this->progresses[$selectedOption];
        $player->sendForm(new ProgressDetailForm($player, $selectedProgress, $this->group));
    }
}
