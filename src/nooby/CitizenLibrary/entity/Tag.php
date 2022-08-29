<?php

namespace nooby\CitizenLibrary\entity;

use nooby\CitizenLibrary\utils\UUID;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\world\Position;
use Ramsey\Uuid\UuidInterface;

class Tag
{
    use UUID;

    private Citizen $citizen;
    private string $nameTag;
    private int $entityId;
    private UuidInterface $uuid;
    private Position $position;
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
        $packet = new AddActorPacket();
        $packet->type = "minecraft:player";
        $packet->actorRuntimeId = $this->entityId;
        $packet->actorUniqueId = $this->entityId;
        $packet->yaw = 0;
        $packet->headYaw = 0;
        $packet->pitch = 0;
        $packet->position = $this->position->asVector3();
        $packet->motion = null;
        $metadata = new EntityMetadataCollection();
        $metadata->setGenericFlag(EntityMetadataFlags::FIRE_IMMUNE, true);
        $metadata->setGenericFlag(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, 1);
        $metadata->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, 1);
        $metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $metadata->setInt(EntityMetadataProperties::VARIANT, RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::AIR()->getFullId()));
        $metadata->setFloat(EntityMetadataProperties::SCALE, 0.004);
        $metadata->setString(EntityMetadataProperties::NAMETAG, $this->getNameTag());
        $metadata->setGenericFlag(EntityMetadataFlags::IMMOBILE, 1);
        $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
        $packet->attributes = [AdventureSettingsPacket::create(0, 0, 0, 0, 0, $this->entityId)];
        $packet->attributes = array_map(function(Attribute $attr): NetworkAttribute{
            return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue());
        }, $this->attributeMap->getAll());
        $packet->metadata = $metadata->getAll();
        $player->getNetworkSession()->sendDataPacket($packet);
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
    public function setNameTag(string $nameTag): void
    {
        $this->nameTag = $nameTag;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     */
    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }
}
