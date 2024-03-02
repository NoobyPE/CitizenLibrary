<?php

namespace nooby\CitizenLibrary\entity;

use nooby\CitizenLibrary\utils\UUID;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
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
    $metadata = new EntityMetadataCollection();
    $metadata->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
    $metadata->setGenericFlag(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, 1);
    $metadata->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, 1);
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
    /*$attributes = array_map(function(Attribute $attr): NetworkAttribute{
      return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
        }, $this->attributeMap->getAll());
    array_push($attributes, UpdateAdventureSettingsPacket::create(true, true, true, true, true));*/
    $metadata = new EntityMetadataCollection();
    $metadata->setGenericFlag(EntityMetadataFlags::FIRE_IMMUNE, true);
    $metadata->setGenericFlag(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, 1);
    $metadata->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, 1);
    $metadata->setGenericFlag(EntityMetadataFlags::IMMOBILE, 1);
    $metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
    $metadata->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())); //NOTE: I still have no idea what it is for, but they passed me the code xd
    $metadata->setFloat(EntityMetadataProperties::SCALE, 0.01);
    $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
    $metadata->setString(EntityMetadataProperties::NAMETAG, $this->getNameTag());
    $player->getNetworkSession()->sendDataPacket(AddActorPacket::create($this->entityId, $this->entityId, EntityIds::PLAYER, $this->getPosition()->asVector3(), $this->getLocation()->asVector3(), $this->citizen->getPitch(), $this->citizen->getYaw(), $this->citizen->getYaw(), 0, [], $metadata->getAll(), new PropertySyncData([], [], $this->entityId), []));
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