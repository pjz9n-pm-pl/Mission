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

namespace pjz9n\mission\mineflow\listener;

use pjz9n\mission\mission\MissionList;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class ReplaceFormUUID implements Listener
{
    public function modalFormReplace(DataPacketSendEvent $event): void
    {
        $packet = $event->getPacket();
        if (!($packet instanceof ModalFormRequestPacket)) return;
        $formData = json_decode($packet->formData, true);
        $formData["title"] = $this->replace($formData["title"]);
        switch ($formData["type"]) {
            case "form":
                $formData["content"] = $this->replace($formData["content"]);
                foreach ($formData["buttons"] as $key => $button) {
                    $formData["buttons"][$key]["text"] = $this->replace($button["text"]);
                }
                break;
            case "modal":
                $formData["content"] = $this->replace($formData["content"]);
                $formData["button1"] = $this->replace($formData["button1"]);
                $formData["button2"] = $this->replace($formData["button2"]);
                break;
            case "custom_form":
                //TODO
                break;
        }
        $packet->formData = json_encode($formData);
    }

    private function replace(string $target): string
    {
        foreach (MissionList::getAll() as $mission) {
            $target = str_replace($mission->getId()->toString(), $mission->getName(), $target);
        }
        //$target = str_replace(TriggerIds::TRIGGER_MISSION_REWARD, Language::get("trigger.type.missionreward"), $target);
        return $target;
    }
}
