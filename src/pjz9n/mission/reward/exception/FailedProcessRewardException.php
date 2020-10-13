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

namespace pjz9n\mission\reward\exception;

use Exception;
use pjz9n\mission\reward\Reward;

class FailedProcessRewardException extends Exception
{
    /** @var Reward|null */
    private $reward;

    public function __construct($message, ?Reward $reward = null)
    {
        if ($reward !== null) {
            $message = $reward->getDetail() . ": " . $message;
        }
        parent::__construct($message, 0, null);
        $this->reward = $reward;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }
}
