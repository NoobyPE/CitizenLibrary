<?php

namespace nooby\CitizenLibrary\task;

use nooby\CitizenLibrary\entity\Citizen;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\scheduler\Task;

class EmoteRepeatingTask extends Task
{

  private string $emoteId;
  
  private Citizen $citizen;
  
  private int $interval;
  
  private int $modifiableInterval;

  public function __construct(string $emoteId, Citizen $citizen, int $interval)
  {
    $this->emoteId = $emoteId;
    $this->citizen = $citizen;
    $this->interval = $interval;
    $this->modifiableInterval = $interval;
  }

  public function onRun(): void
  {
    $this->modifiableInterval--;
    if ($this->modifiableInterval <= 0) {
      $pk = EmotePacket::create($this->citizen->getEntityId(), $this->emoteId, 0);
      foreach ($this->citizen->getViewers() as $viewer) {
        $viewer->getNetworkSession()->sendDataPacket($pk);
      }
    $this->modifiableInterval = $this->interval;
    }
  }
  
}