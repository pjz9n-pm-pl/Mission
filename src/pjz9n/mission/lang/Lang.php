<?php

/*
 * Copyright (c) 2021 PJZ9n.
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

namespace pjz9n\mission\lang;

use pocketmine\lang\BaseLang;

final class Lang
{
    /** @var string */
    private static $path;

    /** @var string */
    private static $fallback;

    /** @var BaseLang */
    private static $lang;

    public static function init(string $path, string $fallback): void
    {
        self::$path = $path;
        self::$fallback = $fallback;

        self::$lang = new BaseLang($fallback, $path, $fallback);
    }

    public static function set(string $lang): void
    {
        self::$lang = new BaseLang($lang, self::$path, self::$fallback);
    }

    public static function get(): BaseLang
    {
        return self::$lang;
    }

    public static function getPath(): string
    {
        return self::$path;
    }

    private function __construct()
    {
    }
}
