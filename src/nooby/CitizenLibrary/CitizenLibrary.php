<?php
declare(strict_types=1);

namespace nooby\CitizenLibrary;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

use nooby\CitizenLibrary\controller\Controller;
use nooby\CitizenLibrary\controller\DefaultController;
use nooby\CitizenLibrary\factory\CitizenFactory;

class CitizenLibrary {
	
	use SingletonTrait;
	
	private PluginBase $plugin;
	
	private CitizenFactory $citizenFactory;
	
	public static function create(PluginBase $plugin): self
	{
		return new self($plugin, new DefaultController());
	}
	
	public function __construct(PluginBase $plugin, Controller $customController)
	{
	  self::setInstance($this);
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
  public function getFactory(): CitizenFactory
  {
    return $this->citizenFactory;
  }
    
}