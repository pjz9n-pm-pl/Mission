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

namespace pjz9n\mission\mission\progress;

use DivisionByZeroError;
use pjz9n\mission\event\MissionCompleteEvent;
use pjz9n\mission\event\RewardReceiveEvent;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\reward\exception\FailedProcessRewardException;
use pjz9n\mission\util\ArraySerializable;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;
use ReflectionException;
use RuntimeException;

final class Progress implements ArraySerializable
{
    public static function arrayDeSerialize(array $data): self
    {
        return new self(
            MissionList::get(UUID::fromString($data["parentMissionId"])),
            $data["player"],
            $data["rewardReceived"],
            $data["currentLoopCount"],
            $data["currentStep"]
        );
    }

    /** @var Mission */
    private $parentMission;

    /** @var string */
    private $player;

    /** @var bool */
    private $rewardReceived;

    /** @var int */
    private $currentLoopCount;

    /** @var int */
    private $currentStep;

    public function __construct(Mission $parentMission, string $player, bool $rewardReceived = false, int $currentLoopCount = 0, int $currentStep = 0)
    {
        $this->parentMission = $parentMission;
        $this->player = $player;
        $this->rewardReceived = $rewardReceived;
        $this->currentLoopCount = $currentLoopCount;
        $this->currentStep = $currentStep;
    }

    public function getParentMission(): Mission
    {
        return $this->parentMission;
    }

    public function getPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerExact($this->player);
    }

    public function getPlayerName(): string
    {
        return $this->player;
    }

    public function isRewardReceived(): bool
    {
        return $this->rewardReceived;
    }

    public function setRewardReceived(bool $rewardReceived): void
    {
        $this->rewardReceived = $rewardReceived;
    }

    public function getCurrentLoopCount(): int
    {
        return $this->currentLoopCount;
    }

    public function setCurrentLoopCount(int $currentLoopCount): void
    {
        $this->currentLoopCount = $currentLoopCount;
    }

    public function addCurrentLoopCount(): void
    {
        $added = $this->getCurrentLoopCount() + 1;
        if ($added > $this->getParentMission()->getLoopCount()) {
            return;
        }
        $this->setCurrentLoopCount($added);
    }

    public function getCurrentStep(): int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $step): void
    {
        $this->currentStep = $step;
    }

    public function addStep(): void
    {
        $addedStep = $this->getCurrentStep() + 1;
        if ($addedStep > $this->getParentMission()->getTargetStep()) {
            return;
        }
        $this->setCurrentStep($addedStep);
        $this->checkCompleted();
    }

    public function isCompleted(): bool
    {
        return $this->getCurrentStep() >= $this->getParentMission()->getTargetStep();
    }

    /**
     * @throws FailedProcessRewardException
     * @throws ReflectionException
     */
    public function sendReward(): void
    {
        if (!$this->isCompleted()) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("error.mission.notcompleted"));
        }
        if ($this->isRewardReceived()) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("error.reward.already"));
        }
        if (($player = $this->getPlayer()) === null) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("error.player.offline"));
        }
        $rewards = $this->getParentMission()->getRewards();
        if (count($rewards) < 1) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("reward.noavailable"));
        }
        foreach ($rewards as $reward) {
            $reward->throwIfCantProcess($player);
        }
        foreach ($rewards as $reward) {
            $reward->process($player);
            (new RewardReceiveEvent($player, $reward))->call();
        }
        $this->setRewardReceived(true);
        $this->addCurrentLoopCount();
        if ($this->isResetable()) {
            $this->reset();
        }
    }

    public function checkContradiction(): void
    {
        if ($this->isResetable()) {
            $this->reset();
        }
    }

    public function isResetable(): bool
    {
        return $this->getCurrentLoopCount() < $this->getParentMission()->getLoopCount()
            && $this->isCompleted()
            && $this->isRewardReceived();
    }

    public function reset(): void
    {
        $this->setRewardReceived(false);
        $this->setCurrentStep(0);
    }

    public function canRewardReceive(): bool
    {
        return $this->isCompleted() && !$this->isRewardReceived();
    }

    public function isProgressing(): bool
    {
        return !$this->canRewardReceive() && !$this->isCompleted();
    }

    public function getProgressPercent(): int
    {
        try {
            return (int)floor($this->getCurrentStep() / $this->getParentMission()->getTargetStep() * 100);
        } catch (DivisionByZeroError $exception) {
            return 0;
        }
    }

    public function arraySerialize(): array
    {
        return [
            "parentMissionId" => $this->parentMission->getId()->toString(),
            "player" => $this->player,
            "rewardReceived" => $this->rewardReceived,
            "currentLoopCount" => $this->currentLoopCount,
            "currentStep" => $this->currentStep,
        ];
    }

    private function checkCompleted(): void
    {
        if ($this->getCurrentStep() >= $this->getParentMission()->getTargetStep()) {
            if (($player = $this->getPlayer()) === null) {
                return;
            }
            try {
                (new MissionCompleteEvent($player, $this))->call();
            } catch (ReflectionException $exception) {
                throw new RuntimeException($exception->getMessage());
            }
        }
    }
}
