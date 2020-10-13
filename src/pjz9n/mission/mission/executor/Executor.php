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

namespace pjz9n\mission\mission\executor;

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\util\ArraySerializable;
use pjz9n\mission\util\FormResponseProcessFailedException;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use ReflectionClass;
use ReflectionException;

abstract class Executor implements ArraySerializable
{
    /**
     * Executorの種類を取得する
     */
    abstract public static function getType(): string;

    /**
     * 作成フォーム用の要素を返す
     *
     * @return CustomFormElement[]
     */
    abstract public static function getCreateFormElements(): array;

    /**
     * 作成フォームの応答から生成したインスタンスを返す
     *
     * @return static
     *
     * @throws FormResponseProcessFailedException 応答の処理に失敗した場合
     */
    abstract public static function createByFormResponse(CustomFormResponse $response, Mission $parentMission);

    /**
     * @return self
     */
    public static function arrayDeSerialize(array $data)
    {
        $type = $data["type"];
        Utils::testValidInstance($type, self::class);
        if (self::class !== static::class) {
            //サブクラス::arrayDeSerialize()だった場合
            return new $type(MissionList::get(UUID::fromString($data["parentMissionId"])));
        } else {
            //self::arrayDeSerialize()だった場合
            return $type::arrayDeSerialize($data);
        }
    }

    /** @var Mission */
    private $parentMission;

    /**
     * 登録処理
     *
     * 例: イベントリスナ登録など
     */
    public function register(): void
    {
        //
    }

    /**
     * 設定フォーム用の要素を返す
     *
     * @return CustomFormElement[]
     */
    abstract public function getSettingFormElements(): array;

    /**
     * 設定フォームの応答を処理する
     *
     * @throws FormResponseProcessFailedException 応答の処理に失敗した場合
     */
    abstract public function processSettingFormResponse(CustomFormResponse $response): void;

    public function __construct(Mission $parentMission)
    {
        $this->parentMission = $parentMission;
    }

    public function getParentMission(): Mission
    {
        return $this->parentMission;
    }

    /**
     * @throws ReflectionException
     */
    public function getDetail(): string
    {
        return ($refrectionClass = new ReflectionClass($this))->getShortName();
    }

    public function arraySerialize(): array
    {
        return [
            "type" => static::class,
            "parentMissionId" => $this->parentMission->getId()->toString(),
        ];
    }
}
