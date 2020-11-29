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

namespace pjz9n\mission\mineflow\flowitem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use pjz9n\mission\exception\NotFoundException;
use pjz9n\mission\mineflow\category\CategoryIds;
use pjz9n\mission\mineflow\flowitem\FlowItemIds;
use pjz9n\mission\mission\Mission;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\mission\progress\ProgressList;
use pocketmine\utils\UUID;

class AddMissionStep extends FlowItem implements PlayerFlowItem
{
    use PlayerFlowItemTrait;

    protected $id = FlowItemIds::ADD_MISSION_STEP;

    protected $name = "action.addMissionStep.name";

    protected $detail = "action.addMissionStep.detail";

    protected $detailDefaultReplace = ["player", "mission", "step"];

    protected $category = CategoryIds::MISSION;

    /** @var UUID|null */
    private $missionId;

    /** @var int|null */
    private $step;

    public function __construct(string $player = "", ?UUID $missionId = null, ?int $step = null)
    {
        $this->setPlayerVariableName($player);
        $this->missionId = $missionId;
        $this->step = $step;
    }

    public function getMissionId(): ?UUID
    {
        return $this->missionId;
    }

    public function setMissionId(?UUID $missionId): void
    {
        $this->missionId = $missionId;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(?int $step): void
    {
        $this->step = $step;
    }

    public function isDataValid(): bool
    {
        return $this->missionId !== null && $this->step !== null;
    }

    public function getDetail(): string
    {
        if (!$this->isDataValid()) {
            return $this->getName();
        }
        try {
            $mission = MissionList::get($this->getMissionId());
        } catch (NotFoundException $exception) {
            return $this->getName();
        }
        $missionName = $mission->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $missionName, $this->getStep()]);
    }

    public function execute(Recipe $origin)
    {
        $this->throwIfCannotExecute();

        $missionId = UUID::fromString($origin->replaceVariables($this->getMissionId()->toString()));
        $step = $origin->replaceVariables((string)$this->getStep());

        try {
            MissionList::get($missionId);
        } catch (NotFoundException $exception) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addMissionStep.mission.notFound"));
        }

        $this->throwIfInvalidNumber($step);
        $step = (int)$step;
        $this->throwIfInvalidPlayer(($player = $this->getPlayer($origin)));

        try {
            $progress = ProgressList::get($player->getName(), $missionId);
        } catch (NotFoundException $exception) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addMissionStep.mission.notFound"));
        }

        for ($i = 0; $i < $step; $i++) {
            $progress->addStep();
        }

        yield true;
    }

    public function getEditForm(array $variables = []): Form
    {
        return (new CustomForm($this->getName()))->setContents([
            new Label($this->getDescription()),
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new Dropdown(
                "@action.addMissionStep.form.mission",
                array_values(array_map(function (Mission $mission): string {
                    return $mission->getName();
                }, MissionList::getAll())),
                ($missionDefault = array_search($this->getMissionId(), array_values(array_map(function (Mission $mission): UUID {
                    return $mission->getId();
                }, MissionList::getAll())), true)) === false ? 0 : $missionDefault
            ),
            new ExampleNumberInput(
                "@action.addMissionStep.form.step",
                "1",
                (string)$this->getStep(),
                true,
                (float)1
            ),
            new CancelToggle(),
        ]);
    }

    public function parseFromFormData(array $data): array
    {
        /** @var Mission $selectedMission */
        $selectedMission = array_values(MissionList::getAll())[$data[2]];
        return ["contents" => [$data[1], $selectedMission->getId()->toString(), $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem
    {
        $this->setPlayerVariableName($content[0]);
        $this->setMissionId(UUID::fromString($content[1]));
        $this->setStep((int)$content[2]);
        return $this;
    }

    public function serializeContents(): array
    {
        return [
            $this->getPlayerVariableName(),
            $this->getMissionId()->toString(),
            $this->getStep(),
        ];
    }
}
