<?php
declare(strict_types=1);

namespace nooby\CitizenLibrary\entity;

use nooby\CitizenLibrary\attributes\InvokeAttribute;
use nooby\CitizenLibrary\attributes\TagEditor;
use nooby\CitizenLibrary\CitizenLibrary;
use nooby\CitizenLibrary\task\EmoteRepeatingTask;
use nooby\CitizenLibrary\task\EmoteRepeatingTimerTask;
use nooby\CitizenLibrary\utils\UUID;

use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\network\mcpe\protocol\types\{
  PlayerPermissions,
  AbilitiesData,
  AbilitiesLayer,
  DeviceOS
};
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\entity\{
  EntityMetadataProperties,
  PropertySyncData
};
use pocketmine\world\Position;

use Ramsey\Uuid\UuidInterface;


class Citizen
{

	use UUID;

	private UuidInterface $uuid;

	private int $entityId;

	/** @var TagEditor */

	private TagEditor $tagEditor;

	private ?InvokeAttribute $invokeAttribute = null;

	private Position $position;
	
	/**
	 * @var array Player[]
	 */
	private array $viewers = [];

	public Skin $skin;
	
  public float $yaw;

  public float $pitch;

  public float $scale = 1.2;

	public function __construct()
	{
	  $this->uuid = $this->uuid();
		$this->entityId = Entity::nextRuntimeId();
		$this->tagEditor = new TagEditor($this);
	}

	public function callInvoke(Player $player): void 
	{
	  $this->invokeAttribute?->invoke($player);
	}

	public function spawnTo(Player $player): void
  {
    $skinAdapter = new LegacySkinAdapter();
    $packets[] = PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->uuid, $this->entityId, "", $skinAdapter->toSkinData($this->skin))]);
    $flags =
      1 << EntityMetadataFlags::CAN_SHOW_NAMETAG |
      1 << EntityMetadataFlags::ALWAYS_SHOW_NAMETAG |
      1 << EntityMetadataFlags::IMMOBILE;
    $actorMetadata = [
      EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
      EntityMetadataProperties::SCALE => new FloatMetadataProperty($this->scale)
    ];
    $packets[] = AddPlayerPacket::create(
      $this->uuid,
      "",
      $this->entityId,
      "",
      $this->position,
      null,
      $this->getPitch(),
      $this->getYaw(),
      $this->getYaw(),
      ItemStackWrapper::legacy(ItemStack::null()),
      0,
      $actorMetadata,
      new PropertySyncData([], []),
      UpdateAbilitiesPacket::create(new AbilitiesData(CommandPermissions::NORMAL, PlayerPermissions::VISITOR, $this->entityId, [
        new AbilitiesLayer(AbilitiesLayer::LAYER_BASE, array_fill(0, AbilitiesLayer::NUMBER_OF_ABILITIES, false), 0.0, 0.0)
      ])),
      [],
      "",
      DeviceOS::UNKNOWN
    );

    $packets[] = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]);
    foreach ($this->tagEditor->getLines() as $tag) {
       $tag->spawnTo($player);
    }

    if (!in_array(spl_object_hash($player), $this->viewers)) {
      $this->viewers[spl_object_hash($player)] = $player;
    }
    foreach ($packets as $pk) {
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }

	public function executeEmote(string $emoteId, bool $nonStop, int $seconds): void
	{
	  if ($nonStop == false) {
	    CitizenLibrary::getInstance()->getPlugin()->getScheduler()->scheduleRepeatingTask(new EmoteRepeatingTimerTask($emoteId, $this, $seconds), 20);
	  } else {
	    CitizenLibrary::getInstance()->getPlugin()->getScheduler()->scheduleRepeatingTask(new EmoteRepeatingTask($emoteId, $this, $seconds), 20);

	  }
	}

	public function toEntity(): ?Entity 
	{
		return $this->position->getWorld()->getEntity($this->entityId);
	}

  public function despairFrom(Player $player): void
  {
    $packet = new RemoveActorPacket();
    $packet->actorUniqueId = $this->entityId;
    $player->getNetworkSession()->sendDataPacket($packet);
    foreach($this->tagEditor->getLines() as $tag) {
      $tag->despairFrom($player);
    }
    unset($this->viewers[spl_object_hash($player)]);
  }

  public function getPosition(): Position
  {
    return $this->position;
  }

  /**
    * @return array Players[]
    */
  public function getViewers(): array
  {
    return $this->viewers;
  }

  public function isViewer(Player $player): bool
  {
    return array_key_exists(spl_object_hash($player), $this->viewers);
  }

  /**
    * @return int
    */
  public function getEntityId(): int
  {
    return $this->entityId;
  }

  /**
    * @return float
    */
  public function getScale(): float
  {
    return $this->scale;
  }

  /**
    * @param float $scale
    */
  public function setScale(float $scale): void
  {
    $this->scale = $scale;
  }

  /**     
    * @return Skin
    */
  public function getSkin(): Skin
  {
    return $this->skin;
  }

  /**
    * @param Skin $skin
    */
  public function setSkin(Skin $skin): void
  {
    $this->skin = $skin;
  }

  /**
    * @param Position $position
    */
  public function setPosition(Position $position): void
  {
    $this->position = $position;
  }

  /**
    * @return float
    */
  public function getYaw(): float
  {
    return $this->yaw;
  }

  /**
    * @param float $yaw
    */
  public function setYaw(float $yaw): void
  {
    $this->yaw = $yaw;
  }

  /**
    * @return float
    */
  public function getPitch(): float
  {
    return $this->pitch;
  }

  /**
    * @param float $pitch
    */
  public function setPitch(float $pitch): void
  {
    $this->pitch = $pitch;
  }

    /**

     * @return TagEditor

     */

  public function getTagEditor(): TagEditor
  {
    return $this->tagEditor;
  }

    /**

     * @param TagEditor $tagEditor

     */

  public function setTagEditor(TagEditor $tagEditor): void
  {
    $this->tagEditor = $tagEditor;
  }

    /**

     * @return InvokeAttribute|null

     */

  public function getInvokeAttribute(): ?InvokeAttribute
  {
    return $this->invokeAttribute;
  }

    /**

     * @param InvokeAttribute|null $invokeAttribute

     */

  public function setInvokeAttribute(?InvokeAttribute $invokeAttribute): void
  {
    $this->invokeAttribute = $invokeAttribute;
  }

}