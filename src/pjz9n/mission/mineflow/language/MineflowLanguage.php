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

namespace pjz9n\mission\mineflow\language;

use aieuo\mineflow\utils\Language;
use InvalidStateException;

final class MineflowLanguage
{
    /** @var string */
    private static $localePath;

    /** @var string */
    private static $fallbackLanguage;

    public static function init(string $localePath, string $fallbackLanguage): void
    {
        self::$localePath = $localePath;
        self::$fallbackLanguage = $fallbackLanguage;
        self::update();
    }

    public static function update(): void
    {
        $language = Language::getLanguage();
        if (!in_array($language, self::getLanguageList(), true)) {
            $language = self::getFallbackLanguage();
        }
        Language::add(parse_ini_file(self::getLocalePath() . $language . ".ini"));
    }

    public static function getLocalePath(): string
    {
        if (self::$localePath === null) throw new InvalidStateException("Not initialized");
        return self::$localePath;
    }

    public static function getFallbackLanguage(): string
    {
        if (self::$fallbackLanguage === null) throw new InvalidStateException("Not initialized");
        return self::$fallbackLanguage;
    }

    private static function getLanguageList(): array
    {
        $list = [];
        foreach (glob(self::getLocalePath() . "*.ini") as $file) {
            if (is_file($file)) {
                $list[] = pathinfo($file)["filename"];
            }
        }
        return $list;
    }

    private function __construct()
    {
        //
    }
}
