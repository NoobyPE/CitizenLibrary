<?php

namespace nooby\CitizenLibrary\entity;

use nooby\CitizenLibrary\utils\UUID;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\UpdateAdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\entity\{
  Entity,
  Location
};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;

use Ramsey\Uuid\UuidInterface;

class Tag
{
  use UUID;

  private Citizen $citizen;

  private string $nameTag;

  private int $entityId;

  private UuidInterface $uuid;

  private Location $location;

  private AttributeMap $attributeMap;

  public function __construct(Citizen $citizen)
  {
    $this->citizen = $citizen;
    $this->entityId = Entity::nextRuntimeId();
    $this->attributeMap = new AttributeMap();
    $this->uuid = $this->uuid();
  }

  public function sendNameTag(Player $player): void
  {
    $packet = new SetActorDataPacket();
    $packet->actorRuntimeId = $this->entityId;
	$packet->syncedProperties = new PropertySyncData([], []);
    $metadata = new EntityMetadataCollection();
	$metadata->setByte(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, 1);
    $metadata->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
    $packet->metadata = $metadata->getAll();
    $player->getNetworkSession()->sendDataPacket($packet);
  }

  public function syncNameTag(): void
  {
    foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
      if (in_array(spl_object_hash($onlinePlayer), $this->citizen->getViewers())) {
        $this->sendNameTag($onlinePlayer);
      }
    }
  }

  public function rename(string $newTag): self
  {
    $this->nameTag = $newTag;
    return $this;
  }

  public function spawnTo(Player $player): void
  {
	  $actorFlags = (
		  1 << EntityMetadataFlags::NO_AI
	  );

	  $actorMetadata = [
		  EntityMetadataProperties::FLAGS => new LongMetadataProperty($actorFlags),
		  EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01),
		  EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0.0),
		  EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.0),
		  EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->nameTag),
		  EntityMetadataProperties::VARIANT => new IntMetadataProperty(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())),
		  EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
	  ];

	  $player->getNetworkSession()->sendDataPacket(AddActorPacket::create($this->entityId, $this->entityId, EntityIds::FALLING_BLOCK, $this->getPosition()->asVector3(), null, 0, 0, 0, 0, [], $actorMetadata, new PropertySyncData([], []), []));
  }

  /**
    * @return string
    */
  public function getNameTag(): string
  {
    return $this->nameTag;
  }

  public function despairFrom(Player $player): void
  {
    $packet = new RemoveActorPacket();
    $packet->actorUniqueId = $this->entityId;
    $player->getNetworkSession()->sendDataPacket($packet);
  }

  /**
    * @param string $nameTag
    */
  public function setNameTag(string $nameTag): self
  {
    $this->nameTag = $nameTag;
    return $this;
  }

  /**
    * @return Position
    */
  public function getPosition(): Position
  {
    return $this->location->asPosition();
  }

  /**
    * @param Location $location
    */
  public function setLocation(Position $location): self
  {
    $this->location = $location;
    return $this;
  }

  function getLocation(): Location
  {
    return $this->location->asLocation();
  }

}