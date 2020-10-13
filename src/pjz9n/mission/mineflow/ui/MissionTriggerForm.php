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

namespace pjz9n\mission\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\ui\BaseTriggerForm;
use aieuo\mineflow\ui\RecipeForm;
use aieuo\mineflow\ui\TriggerForm;
use aieuo\mineflow\utils\Language;
use pjz9n\mission\mineflow\trigger\MissionRewardTrigger;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pocketmine\Player;

class MissionTriggerForm extends TriggerForm
{
    public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void
    {
        (new ListForm(Language::get("form.trigger.addedTriggerMenu.title", [$recipe->getName(), $trigger->getKey()])))
            ->setContent((string)$trigger)
            ->addButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe, Trigger $trigger) {
                switch ($data) {
                    case 0:
                        (new RecipeForm())->sendTriggerList($player, $recipe);
                        break;
                    case 1:
                        (new BaseTriggerForm())->sendConfirmDelete($player, $recipe, $trigger);
                        break;
                }
            })->addArgs($recipe, $trigger)->addMessages($messages)->show($player);
    }

    public function sendMenu(Player $player, Recipe $recipe): void
    {
        $this->sendSelectMission($player, $recipe);
    }

    public function sendSelectMission(Player $player, Recipe $recipe, array $errors = []): void
    {
        (new CustomForm(Language::get("trigger.missionreward.select.title", [$recipe->getName()])))
            ->setContents([
                new Dropdown("@trigger.missionreward.select.dropdown", array_values(array_map(function (Mission $mission): string {
                    return $mission->getName();
                }, MissionList::getAll()))),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, Recipe $recipe) {
                if ($data[1]) {
                    (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe);
                    return;
                }
                /** @var Mission $selectedMission */
                $selectedMission = array_values(MissionList::getAll())[$data[0]];
                $trigger = MissionRewardTrigger::create($selectedMission->getId()->toString());
                if ($recipe->existsTrigger($trigger)) {
                    $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                    return;
                }
                $recipe->addTrigger($trigger);
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
            })->addArgs($recipe)->addErrors($errors)->show($player);
    }
}
