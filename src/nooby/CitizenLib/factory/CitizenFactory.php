<?php

namespace nooby\CitizenLib\factory;

use nooby\CitizenLib\entity\Citizen;

class CitizenFactory {

    /**
     * @var Citizen[] $citizens
     */
	private array $citizens = [];

    /**
     * @param Citizen $citizen
     * @return void
     */
	public function add(Citizen $citizen): void 
	{
		$this->citizens[$citizen->getEntityId()] = $citizen;
	}

    /**
     * @param int $id
     * @return void
     */
	public function remove(int $id): void 
	{
		unset($this->citizens[$id]);
	}

    /**
     * @param int $id
     * @return Citizen|null
     */
	public function get(int $id): ?Citizen
	{
		return $this->citizens[$id] ?? null;
	}

    /**
     * @return Citizen[]
     */
	public function getCitizens(): array 
	{
		return $this->citizens;
	}
}