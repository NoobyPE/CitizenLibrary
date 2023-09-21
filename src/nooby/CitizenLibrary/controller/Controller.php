<?php

namespace nooby\CitizenLibrary\controller;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

abstract class Controller implements Listener {

  public abstract function handleDataPacketReceive(DataPacketReceiveEvent $event);

  public abstract function handlePlayerJoin(PlayerJoinEvent $event);

  public abstract function handlePlayerQuit(PlayerQuitEvent $event);

  public abstract function handleEntityTeleport(EntityTeleportEvent $event);
  
}