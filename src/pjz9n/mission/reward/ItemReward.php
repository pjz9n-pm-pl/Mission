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

namespace pjz9n\mission\reward;

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Toggle;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\reward\exception\FailedProcessRewardException;
use pjz9n\mission\util\FormResponseProcessFailedException;
use pjz9n\mission\util\Utils;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\UUID;
use TypeError;

class ItemReward extends Reward
{
    public static function getType(): string
    {
        return LanguageHolder::get()->translateString("reward.itemreward.type");
    }

    public static function getCreateFormElements(): array
    {
        return array_merge(parent::getCreateFormElements(), [
            new Input("id", LanguageHolder::get()->translateString("id")),
            new Input("meta", LanguageHolder::get()->translateString("meta"), "", "0"),
            new Input("amount", LanguageHolder::get()->translateString("amount")),
            new Toggle("autodetail", LanguageHolder::get()->translateString("reward.itemreward.autodetail"), true),
        ]);
    }

    public static function createByFormResponse(CustomFormResponse $response, UUID $parentMissionId)
    {
        $item = self::getItemFromFormResponse($response);
        return new self(
            $parentMissionId,
            $response->getBool("autodetail") ? (string)$item : $response->getString("detail"),
            $item
        );
    }

    /**
     * @return self
     */
    public static function arrayDeSerialize(array $data)
    {
        $item = $data["item"];
        return new self(
            UUID::fromString($data["parentMissionId"]),
            $data["detail"],
            Item::jsonDeserialize($item)
        );
    }

    /**
     * @throws FormResponseProcessFailedException
     */
    private static function getItemFromFormResponse(CustomFormResponse $response): Item
    {
        try {
            $id = Utils::filterToInteger($response->getString("id"));
        } catch (TypeError $exception) {
            throw new FormResponseProcessFailedException(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("id"),
            ]));
        }
        try {
            $meta = Utils::filterToInteger($response->getString("meta"));
        } catch (TypeError $exception) {
            throw new FormResponseProcessFailedException(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("meta"),
            ]));
        }
        try {
            $amount = Utils::filterToInteger($response->getString("amount"));
        } catch (TypeError $exception) {
            throw new FormResponseProcessFailedException(LanguageHolder::get()->translateString("error.validate.mustinteger", [
                LanguageHolder::get()->translateString("amount"),
            ]));
        }
        if (!Utils::isValidItem($id, $meta, $amount)) {
            throw new FormResponseProcessFailedException(LanguageHolder::get()->translateString("error.invalid.item"));
        }
        return ItemFactory::get($id, $meta, $amount);
    }

    /** @var Item */
    private $item;

    public function __construct(UUID $parentMissionId, string $detail, Item $item)
    {
        parent::__construct($parentMissionId, $detail);
        $this->item = $item;
    }

    public function throwIfCantProcess(Player $player): void
    {
        if (!$player->getInventory()->canAddItem($this->getItem())) {
            throw new FailedProcessRewardException(LanguageHolder::get()->translateString("error.inventory.cannotadd"), $this);
        }
    }

    public function process(Player $player): void
    {
        $player->getInventory()->addItem($this->getItem());
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getSettingFormElements(): array
    {
        $item = $this->getItem();
        return array_merge(parent::getSettingFormElements(), [
            new Input("id", LanguageHolder::get()->translateString("id"), "", (string)$item->getId()),
            new Input("meta", LanguageHolder::get()->translateString("meta"), "", (string)$item->getDamage()),
            new Input("amount", LanguageHolder::get()->translateString("amount"), "", (string)$item->getCount()),
            new Toggle("autodetail", LanguageHolder::get()->translateString("reward.itemreward.autodetail"), true),
        ]);
    }

    public function processSettingFormResponse(CustomFormResponse $response): void
    {
        parent::processSettingFormResponse($response);
        $this->setItem(self::getItemFromFormResponse($response));
        if ($response->getBool("autodetail")) {
            $this->setDetail((string)$this->getItem());
        }
    }

    public function arraySerialize(): array
    {
        return array_merge(parent::arraySerialize(), [
            "item" => $this->getItem()->jsonSerialize(),
        ]);
    }
}
