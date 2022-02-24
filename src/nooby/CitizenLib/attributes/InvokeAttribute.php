<?php

namespace nooby\CitizenLib\attributes;

use pocketmine\player\Player;
use nooby\CitizenLib\entity\Citizen;

abstract class InvokeAttribute {
	
	private Citizen $citizen;

    public function __construct(Citizen $citizen)
    {
        $this->citizen = $citizen;
    }

    public abstract function invoke(Player $player): void;

    /**
     * @return Citizen
     */
    public function getCitizen(): Citizen
    {
        return $this->citizen;
    }
}
