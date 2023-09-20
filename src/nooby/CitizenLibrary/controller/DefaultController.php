<?php

namespace nooby\CitizenLibrary\controller;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use nooby\CitizenLibrary\CitizenLibrary;

class DefaultController extends Controller {
	
	public function handlePlayerJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();
		$citizenFactory = CitizenLibrary::getInstance();
		$citizens = array_filter($citizenFactory->getFactory()->getCitizens(), function ($citizen) use($player){
			return $citizen->getPosition()->getWorld()->getFolderName() === $player->getPosition()->getWorld()->getFolderName();
		});
		foreach($citizens as $citizen){
			$citizen->spawnTo($player);
		}
	}
	
	public function handlePlayerQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		
		$citizenFactory = CitizenLibrary::getInstance();
		$citizens = array_filter($citizenFactory->getFactory()->getCitizens(), function ($citizen) use($player){
			return $citizen->getPosition()->getWorld()->getFolderName() === $player->getPosition()->getWorld()->getFolderName();
		});
		foreach($citizens as $citizen){
			$citizen->despairFrom($player);
		}
	}
	
	public function handleEntityTeleport(EntityTeleportEvent $event): void
	{
		$player = $event->getEntity();
		if (!($player instanceof Player)) {
			return;
		}
		
		$origin = $event->getFrom();
		$target = $event->getTo();
    $citizenFactory = CitizenLibrary::getInstance();

		$citizensDespair = array_filter($citizenFactory->getFactory()->getCitizens(), function ($citizen) use($origin){
			return $citizen->getPosition()->getWorld()->getFolderName() === $origin->getWorld()->getFolderName();
		});
		foreach($citizensDespair as $citizen){
			$citizen->despairFrom($player);
		}
		
		$citizensSpawn = array_filter($citizenFactory->getFactory()->getCitizens(), function ($citizen) use($target){
			return $citizen->getPosition()->getWorld()->getFolderName() === $target->getWorld()->getFolderName();
		});
		foreach($citizensSpawn as $citizen){
			$citizen->spawnTo($player);
		}
	}

    public function handleDataPacketReceive(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if ($packet instanceof InventoryTransactionPacket){
            if ($packet->trData instanceof UseItemOnEntityTransactionData){
                if ($packet->trData->getActionType() == UseItemOnEntityTransactionData::ACTION_INTERACT){
                    $citizen = CitizenLibrary::getInstance()->getFactory()->get($packet->trData->getActorRuntimeId());
                    if ($citizen == null || $citizen->getInvokeAttribute() == null) {
                        return;
                    }
                    $citizen->callInvoke($player);
                }
            }
        }
    }
    
}