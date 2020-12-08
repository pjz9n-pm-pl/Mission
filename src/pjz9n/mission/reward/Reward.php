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
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Input;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\reward\exception\FailedProcessRewardException;
use pjz9n\mission\util\ArraySerializable;
use pjz9n\mission\util\FormResponseProcessFailedException;
use pocketmine\Player;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;

abstract class Reward implements ArraySerializable
{
    /** @var bool "Unique" means that you can use up to one in one mission. */
    protected static $unique = false;

    /**
     * 報酬の種類を取得する
     */
    abstract public static function getType(): string;

    public static function isUnique(): bool
    {
        return static::$unique;
    }

    /**
     * 作成フォーム用の要素を返す
     *
     * @return CustomFormElement[]
     */
    public static function getCreateFormElements(): array
    {
        return [
            new Input("detail", LanguageHolder::get()->translateString("detail")),
        ];
    }

    /**
     * 作成フォームの応答から生成したインスタンスを返す
     *
     * @return static
     *
     * @throws FormResponseProcessFailedException 応答の処理に失敗した場合
     */
    public static function createByFormResponse(CustomFormResponse $response, UUID $parentMissionId)
    {
        return new static($parentMissionId, $response->getString("detail"));
    }

    /**
     * @return self
     */
    public static function arrayDeSerialize(array $data)
    {
        $type = $data["type"];
        Utils::testValidInstance($type, self::class);
        if (self::class !== static::class) {
            //サブクラス::arrayDeSerialize()だった場合
            return new $type(UUID::fromString($data["parentMissionId"]), $data["detail"]);
        } else {
            //self::arrayDeSerialize()だった場合
            return $type::arrayDeSerialize($data);
        }
    }

    /** @var string */
    private $detail;

    /**
     * 報酬が処理出来ない状態の場合例外を投げる
     *
     * @throws FailedProcessRewardException
     */
    abstract public function throwIfCantProcess(Player $player): void;

    /**
     * 報酬を処理する
     */
    abstract public function process(Player $player): void;

    /** @var UUID */
    private $parentMissionId;

    public function __construct(UUID $parentMissionId, string $detail)
    {
        $this->parentMissionId = $parentMissionId;
        $this->detail = $detail;
    }

    public function getParentMissionId(): UUID
    {
        return $this->parentMissionId;
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
     * 設定フォーム用の要素を返す
     *
     * @return CustomFormElement[]
     */
    public function getSettingFormElements(): array
    {
        return [
            new Input("detail", LanguageHolder::get()->translateString("detail"), "", $this->getDetail()),
        ];
    }

    /**
     * 設定フォームの応答を処理する
     *
     * @throws FormResponseProcessFailedException 応答の処理に失敗した場合
     */
    public function processSettingFormResponse(CustomFormResponse $response): void
    {
        $this->setDetail($response->getString("detail"));
    }

    public function arraySerialize(): array
    {
        return [
            "type" => static::class,
            "parentMissionId" => $this->parentMissionId->toString(),
            "detail" => $this->detail,
        ];
    }
}
