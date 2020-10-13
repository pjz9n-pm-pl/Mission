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

use pjz9n\mission\exception\AlreadyExistsException;
use pjz9n\mission\exception\NotFoundException;
use pocketmine\utils\Utils;

class Executors
{
    /** @var string[] */
    private static $executors = [];

    public static function addDefaults(): void
    {
        self::add(EventExecutor::class);
    }

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$executors;
    }

    public static function add(string $executorClass): void
    {
        if (array_search($executorClass, self::$executors, true) !== false) {
            throw new AlreadyExistsException("ExecutorClass \"{$executorClass}\" already exists");
        }
        Utils::testValidInstance($executorClass, Executor::class);
        self::$executors[] = $executorClass;
    }

    public static function remove(string $executorClass): void
    {
        if (($searchKey = array_search($executorClass, self::$executors, true)) === false) {
            throw new NotFoundException("ExecutorClass \"{$executorClass}\" not found");
        }
        unset(self::$executors[$searchKey]);
        self::$executors = array_values(self::$executors);
    }

    private function __construct()
    {
        //
    }
}
