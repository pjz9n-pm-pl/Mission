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

namespace pjz9n\mission\mission;

use InvalidArgumentException;
use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\mission\progress\Progress;
use pjz9n\mission\reward\Reward;
use pjz9n\mission\util\ArraySerializable;
use pocketmine\utils\UUID;

final class Mission implements ArraySerializable
{
    public static function create(
        string $name,
        string $detail,
        int $loopCount,
        int $targetStep,
        array $rewards = [],
        ?string $group = null
    ): self
    {
        return new self(UUID::fromRandom(), $name, $detail, $loopCount, $targetStep, $rewards, $group);
    }

    public static function arrayDeSerialize(array $data): self
    {
        return new self(
            UUID::fromString($data["id"]),
            $data["name"],
            $data["detail"],
            $data["loopCount"],
            $data["targetStep"],
            array_map(function (array $serializedReward): Reward {
                return Reward::arrayDeSerialize($serializedReward);
            }, $data["rewards"]),
            $data["group"] ?? null
        );
    }

    private static function checkValidLoopCount(int $loopCount): void
    {
        if ($loopCount < 1) {
            throw new InvalidArgumentException("loopCount must be greater than or equal to 1");
        }
    }

    private static function checkValidTargetStep(int $targetStep): void
    {
        if ($targetStep < 1) {
            throw new InvalidArgumentException("targetStep must be greater than or equal to 1");
        }
    }

    /** @var UUID */
    private $id;

    /** @var string|null */
    private $group;

    /** @var string */
    private $name;

    /** @var string */
    private $detail;

    /** @var Reward[] */
    private $rewards;

    /** @var int */
    private $loopCount;

    /** @var int */
    private $targetStep;

    /**
     * @param Reward[] $rewards
     */
    public function __construct(
        UUID $id,
        string $name,
        string $detail,
        int $loopCount,
        int $targetStep,
        array $rewards = [],
        ?string $group = null
    )
    {
        $this->id = $id;
        $this->group = $group;
        $this->name = $name;
        $this->detail = $detail;
        $this->rewards = array_values($rewards);
        self::checkValidLoopCount($loopCount);
        $this->loopCount = $loopCount;
        self::checkValidTargetStep($targetStep);
        $this->targetStep = $targetStep;
    }

    public function getId(): UUID
    {
        return $this->id;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): void
    {
        $this->group = $group;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @return Reward[]
     */
    public function getRewards(): array
    {
        return $this->rewards;
    }

    /**
     * @throws AlreadyExistsException
     */
    public function addReward(Reward $reward): void
    {
        if (array_search($reward, $this->rewards, true) !== false) {
            throw new AlreadyExistsException("Reward already exists");
        }
        $this->rewards[] = $reward;
    }

    /**
     * @throws NotFoundException
     */
    public function removeReward(Reward $reward): void
    {
        if (($searchKey = array_search($reward, $this->rewards, true)) === false) {
            throw new NotFoundException("Reward not found");
        }
        unset($this->rewards[$searchKey]);
        $this->rewards = array_values($this->rewards);
    }

    public function getLoopCount(): int
    {
        return $this->loopCount;
    }

    public function setLoopCount(int $loopCount): void
    {
        $this->checkValidLoopCount($loopCount);
        $this->loopCount = $loopCount;
    }

    public function getTargetStep(): int
    {
        return $this->targetStep;
    }

    public function setTargetStep(int $targetStep): void
    {
        $this->checkValidTargetStep($targetStep);
        $this->targetStep = $targetStep;
    }

    public function createNewProgress(string $player): Progress
    {
        return new Progress($this, $player);
    }

    public function getShortId(): string
    {
        return substr($this->getId()->toString(), 0, 8);
    }

    public function arraySerialize(): array
    {
        return [
            "id" => $this->id->toString(),
            "group" => $this->group,
            "name" => $this->name,
            "detail" => $this->detail,
            "rewards" => array_map(function (Reward $reward): array {
                return $reward->arraySerialize();
            }, $this->rewards),
            "loopCount" => $this->loopCount,
            "targetStep" => $this->targetStep,
        ];
    }
}
