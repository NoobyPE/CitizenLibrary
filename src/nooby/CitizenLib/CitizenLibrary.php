<?php

namespace nooby\CitizenLib;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

use nooby\CitizenLib\controller\Controller;
use nooby\CitizenLib\controller\DefaultController;
use nooby\CitizenLib\factory\CitizenFactory;

class CitizenLibrary {
	
        use SingletonTrait;
	
	private PluginBase $plugin;
	private CitizenFactory $citizenFactory;
	
	public static function create(PluginBase $plugin): self
	{
		return new CitizenLibrary($plugin, new DefaultController());
	}
	
	public function __construct(PluginBase $plugin, Controller $customController)
	{
		self::setInstance(this);
		$this->plugin = $plugin;
		$this->citizenFactory = new CitizenFactory();
		$plugin->getServer()->getPluginManager()->registerEvents($customController, $plugin);
	

     }

    /**
     * @return PluginBase
     */
    public function getPlugin(): PluginBase
    {
        return $this->plugin;
    }

    /**
     * @return CitizenFactory
     */
    public function getCitizenFactory(): CitizenFactory
    {
        return $this->citizenFactory;
    }
}
