<?php

namespace sheluvmyshe\lectory\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Exception;
use sheluvmyshe\lectory\Loader;
use sheluvmyshe\lectory\libs\PMQuery;

class UpdateAsyncTask extends AsyncTask
{
    private string $linkedServers;

    public function __construct(array $linkedServers) {
        $this->linkedServers = json_encode($linkedServers);
    }

    public function onRun(): void {
        $linkedServers = json_decode($this->linkedServers, true);
        $playerCount = 0;
        foreach ($linkedServers as $server) {
            $serverInfos = explode(":", $server);
            $ip = $serverInfos[0];
            $port = (int)$serverInfos[1];

            try {
                $queryData = PMQuery::query($ip, $port);
                $playerCount += $queryData['Players'];
            } catch (Exception) {
                continue;
            }
        }
        $this->setResult($playerCount);
    }

    public function onCompletion(Server $server): void {
        $result = $this->getResult();
        if($result >= 0) {
            Loader::getInstance()->setPlayersCount($result);
        }
    }
}