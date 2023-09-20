<?php

namespace nooby\CitizenLibrary\attributes;

use nooby\CitizenLibrary\entity\Tag;
use pocketmine\world\Position;
use nooby\CitizenLibrary\entity\Citizen;

class TagEditor {
	
	private Citizen $citizen;

    /**
     * @var Tag[] $lines
     */
 	  private array $lines = [];
	
	  const ONE_BREAK_LINE = 0.32;

    public function __construct(Citizen $citizen)
    {
        $this->citizen = $citizen;
    }

    public function size(): int
	  {
		  return count($this->lines);
	  }
	
	  public function getLine(int $index): Tag 
	  {
		return $this->lines[$index];
	  }
	
	  public function putLine(string $nameTag, int $separator = 1): TagEditor
	  {
	  	$tag = new Tag($this->citizen);
		  $tag->setNameTag($nameTag);
		
		  if ($this->size() == 0) {
		    $position = $this->citizen->getPosition()->add(0, ($this->citizen->getScale() * 1.8), 0);
	  	} else {
	  	  $position = $this->lines[$this->size() - 1]->getPosition()->add(0, (self::ONE_BREAK_LINE * $separator), 0);
	  	}
      $tag->setPosition(new Position($position->x, $position->y, $position->z, $this->citizen->getPosition()->getWorld()));
      $this->lines[] = $tag;
      return $this;
	  }
    /**
     * @return Tag[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return Citizen
     */
    public function getCitizen(): Citizen
    {
        return $this->citizen;
    }

    /**
     * @param Citizen $citizen
     */
    public function setCitizen(Citizen $citizen): void
    {
        $this->citizen = $citizen;
    }
    
}