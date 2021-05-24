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

namespace pjz9n\mission;

use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\Main as MFMain;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Category;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use InvalidStateException;
use pjz9n\mission\command\MissionCommand;
use pjz9n\mission\language\LanguageHolder;
use pjz9n\mission\listener\SendMessageListener;
use pjz9n\mission\listener\SyncProgressListener;
use pjz9n\mission\mineflow\category\CategoryIds;
use pjz9n\mission\mineflow\flowitem\action\AddMissionStep;
use pjz9n\mission\mineflow\language\MineflowLanguage;
use pjz9n\mission\mineflow\listener\ReplaceFormUUID;
use pjz9n\mission\mineflow\trigger\event\MissionCompleteEventTrigger;
use pjz9n\mission\mineflow\trigger\event\RewardReceiveEventTrigger;
use pjz9n\mission\mineflow\trigger\MissionRewardTrigger;
use pjz9n\mission\mineflow\trigger\TriggerIds;
use pjz9n\mission\mineflow\ui\MissionTriggerForm;
use pjz9n\mission\mission\executor\EventList;
use pjz9n\mission\mission\executor\ExecutorList;
use pjz9n\mission\mission\executor\Executors;
use pjz9n\mission\mission\MissionList;
use pjz9n\mission\mission\progress\ProgressList;
use pjz9n\mission\reward\Rewards;
use pjz9n\mission\util\SoftdependPlugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use ReflectionException;

class Main extends PluginBase
{
    /** @var bool */
    private static $isStartCompleted = false;

    /** @var self */
    private static $instance;

    /**
     * @internal
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new InvalidStateException("Plugin is not loaded");
        }
        return self::$instance;
    }

    /** @var Config */
    private $missionsConfig;

    /** @var Config */
    private $progressesConfig;

    /** @var Config */
    private $executorsConfig;

    /** @var string|null */
    private $missionsConfigHash = null;

    /** @var string|null */
    private $progressesConfigHash = null;

    /** @var string|null */
    private $executorsConfigHash = null;

    public function onLoad(): void
    {
        self::$instance = $this;
        if (SoftdependPlugin::isAvailableMineflow()) {
            //Mineflow
            Triggers::add(
                TriggerIds::TRIGGER_MISSION_REWARD,
                MissionRewardTrigger::class,
                new MissionTriggerForm()
            );
            Category::addCategory(CategoryIds::MISSION);
            FlowItemFactory::register(new AddMissionStep());
        }
    }

    /**
     * @throws HookAlreadyRegistered
     * @throws ReflectionException
     */
    public function onEnable(): void
    {
        //PacketHooker
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        //Config
        new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "language" => "default",
            "send-missioncomplete-message" => true,
            "missioncomplete-message" => "",
        ]);
        //Language
        $localePath = $this->getFile() . "resources/locale/";
        LanguageHolder::init($localePath, "eng", $this->getConfig());
        $this->getLogger()->info(LanguageHolder::get()->translateString("language.selected", [
            LanguageHolder::get()->getName(),
            LanguageHolder::get()->getLang(),
        ]));
        //Config Defaults
        if ($this->getConfig()->get("missioncomplete-message") === "") {
            $this->getConfig()->set("missioncomplete-message", LanguageHolder::get()->translateString("config.default.missioncomplete.message"));
        }
        //Reward
        Rewards::addDefaults();
        //Mission
        if (file_exists($this->getDataFolder() . "missions.json")) {
            $this->missionsConfigHash = sha1_file($this->getDataFolder() . "missions.json");
        }
        $this->missionsConfig = new Config($this->getDataFolder() . "missions.json");
        $this->missionsConfig->setJsonOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        MissionList::initFromArray($this->missionsConfig->getAll());
        //Progress
        if (file_exists($this->getDataFolder() . "progresses.json")) {
            $this->progressesConfigHash = sha1_file($this->getDataFolder() . "progresses.json");
        }
        $this->progressesConfig = new Config($this->getDataFolder() . "progresses.json");
        $this->progressesConfig->setJsonOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ProgressList::initFromArray($this->progressesConfig->getAll());
        //Executor
        Executors::addDefaults();
        EventList::addDefaults();
        if (file_exists($this->getDataFolder() . "executors.json")) {
            $this->executorsConfigHash = sha1_file($this->getDataFolder() . "executors.json");
        }
        $this->executorsConfig = new Config($this->getDataFolder() . "executors.json");
        $this->executorsConfig->setJsonOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ExecutorList::initFromArray($this->executorsConfig->getAll());
        //Listener
        $this->getServer()->getPluginManager()->registerEvents(new SyncProgressListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SendMessageListener($this->getConfig()), $this);
        //Command
        $this->getServer()->getCommandMap()->register($this->getName(), new MissionCommand($this));
        if (SoftdependPlugin::isAvailableMineflow()) {
            //Mineflow
            MFMain::getEventManager()->addTrigger(new MissionCompleteEventTrigger(), true);
            MFMain::getEventManager()->addTrigger(new RewardReceiveEventTrigger(), true);
            $localePath = $this->getFile() . "resources/mineflow/locale/";
            MineflowLanguage::init($localePath, "eng");
            //Mineflow related listener
            $this->getServer()->getPluginManager()->registerEvents(new ReplaceFormUUID(), $this);
        }
        //ScoreHud Addon
        if (SoftdependPlugin::isAvailableScoreHud()) {
            $scoreHudPlugin = Server::getInstance()->getPluginManager()->getPlugin("ScoreHud");
            if (version_compare($scoreHudPlugin->getDescription()->getVersion(), "5.0.0") <= 0) {
                file_put_contents(
                    $this->getDataFolder() . "../ScoreHud/addons/MissionAddon.php",
                    stream_get_contents($this->getResource("scorehud/addon/MissionAddon.php"))
                );
                $this->getLogger()->info(LanguageHolder::get()->translateString("scorehud.addon.enabled"));
            }
        }

        self::$isStartCompleted = true;
    }

    public function onDisable(): void
    {
        if (!self::$isStartCompleted) {
            return;
        }
        //Missions
        if ($this->missionsConfigHash !== null && $this->missionsConfigHash !== sha1_file($this->getDataFolder() . "missions.json")) {
            $this->getLogger()->warning("Data file " . $this->getDataFolder() . "missions.json" . " is changed. The changes have been discarded.");
        } else {
            $this->missionsConfig->setAll(MissionList::serializeToArray());
            $this->missionsConfig->save();
        }
        //Progresses
        if ($this->progressesConfigHash !== null && $this->progressesConfigHash !== sha1_file($this->getDataFolder() . "progresses.json")) {
            $this->getLogger()->warning("Data file " . $this->getDataFolder() . "progresses.json" . " is changed. The changes have been discarded.");
        } else {
            $this->progressesConfig->setAll(ProgressList::serializeToArray());
            $this->progressesConfig->save();
        }
        //Executors
        if ($this->executorsConfigHash !== null && $this->executorsConfigHash !== sha1_file($this->getDataFolder() . "executors.json")) {
            $this->getLogger()->warning("Data file " . $this->getDataFolder() . "executors.json" . " is changed. The changes have been discarded.");
        } else {
            $this->executorsConfig->setAll(ExecutorList::serializeToArray());
            $this->executorsConfig->save();
        }
        //Config
        $this->saveConfig();
    }
}
