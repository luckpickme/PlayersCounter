<?php

namespace sheluvmyshe\lectory;

use sheluvmyshe\lectory\task\UpdateAsyncTask;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase implements Listener
{
    use SingletonTrait;

    private int $playersCount = 0;

    public function onEnable(): void
    {
        self::setInstance($this);

        $config = $this->getConfig();
        $updateTime = $config->get("update-time", 30);
        $linkedServers = $config->get("linked-servers", []);

        if(empty($linkedServers)) {
            $this->getLogger()->info("§cYou didn't specify the server data in the configuration, so the plugin is disabled.");
            return;
        }

        $info = "§dThe account of the players from another server is working successfully:§b " . implode(", ", $linkedServers);
        $this->getLogger()->info($info);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function(int $currentTick) use ($linkedServers): void {
                $this->getServer()->getAsyncPool()->submitTask(new UpdateAsyncTask($linkedServers));
            }),
            20 * $updateTime
        );
    }

    public function setPlayersCount(int $count): void
    {
        $this->playersCount = $count;
    }

    public function getPlayersCount(): int
    {
        return $this->playersCount;
    }

    public function getTotalPlayerCount(): int
    {
        return count($this->getServer()->getOnlinePlayers()) + $this->getPlayersCount();
    }

    public function onQueryRegenerate(QueryRegenerateEvent $event): void
    {
        $event->setPlayerCount($this->getTotalPlayerCount());
    }
}