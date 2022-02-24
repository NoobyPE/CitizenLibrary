<?php

namespace nooby\CitizenLib\task;

use nooby\CitizenLib\entity\Citizen;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\scheduler\Task;

class EmoteRepeatingTimerTask extends Task {

    private string $emoteId;
    private Citizen $citizen;
    private bool | int $seconds;

    public function __construct(string $emoteId, Citizen $citizen, bool | int $seconds)
    {
        $this->emoteId = $emoteId;
        $this->citizen = $citizen;
        $this->seconds = $seconds;
    }

    public function onRun(): void
    {
        $pk = EmotePacket::create($this->citizen->getEntityId(), $this->emoteId, 0);
        foreach ($this->citizen->getViewers() as $viewer) {
            $viewer->getNetworkSession()->sendDataPacket($pk);
        }
        $this->seconds--;
        if ($this->seconds == 0) {
            $this->onCancel();
        }
    }
}