<?php

namespace BattleRoyale\BossBar;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

class BossManager {

  const NETWORK_EID = 9999;

  public static function addWindow(Player $player): void{
    $packet = new AddEntityPacket();
    $packet->entityRuntimeId = BossManager::NETWORK_EID;
    $packet->type = 37;
    $packet->position = $player->asVector3();
    $packet->yaw = $player->yaw;
    $packet->pitch = $player->pitch;
    $packet->motion = $player->getMotion();
    $packet->metadata[Entity::DATA_LEAD_HOLDER_EID] = array(Entity::DATA_TYPE_LONG, -1);
    $packet->metadata[Entity::DATA_FLAGS] = array(Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE);
    $packet->metadata[Entity::DATA_SCALE] = array(Entity::DATA_TYPE_FLOAT, 0.0);
    $packet->metadata[Entity::DATA_NAMETAG] = array(Entity::DATA_TYPE_STRING, BossManager::getDefaultString());
    $packet->metadata[Entity::DATA_BOUNDING_BOX_WIDTH] = array(Entity::DATA_TYPE_FLOAT, 0.0);
    $packet->metadata[Entity::DATA_BOUNDING_BOX_HEIGHT] = array(Entity::DATA_TYPE_FLOAT, 0.0);
    $player->dataPacket($packet);
    unset($packet);
    $packet = new BossEventPacket();
    $packet->bossEid = BossManager::NETWORK_EID;
    $packet->eventType = BossEventPacket::TYPE_SHOW;
    $packet->playerEid = 0;
    $packet->healthPercent = 1;
    $packet->title = BossManager::getDefaultString();
    $packet->unknownShort = 0;
    $packet->color = 0;
    $packet->overlay = 0;
    $player->dataPacket($packet);
    unset($packet);
    $packet = new UpdateAttributesPacket();
    $packet->entries[] = new BossSettings();
    $packet->entityRuntimeId = BossManager::NETWORK_EID;
    $player->dataPacket($packet);
  }

  public static function getDefaultString(): string{
    return TextFormat::BOLD.TextFormat::YELLOW."Battle ".TextFormat::WHITE."Royale";
  }

  public static function removeWindow(Player $player): void{
    $packet = new RemoveEntityPacket();
    $packet->entityUniqueId = BossManager::NETWORK_EID;
    $player->dataPacket($packet);
  }

  public static function setString(Player $player, string $message = ""): void{
    $packet = new SetEntityDataPacket();
    $packet->metadata[Entity::DATA_NAMETAG] = array(Entity::DATA_TYPE_STRING, $message);
    $packet->entityRuntimeId = BossManager::NETWORK_EID;
    $player->dataPacket($packet);
    unset($packet);
    $packet = new BossEventPacket();
    $packet->bossEid = BossManager::NETWORK_EID;
    $packet->eventType = BossEventPacket::TYPE_SHOW;
    $packet->playerEid = 0;
    $packet->healthPercent = 1;
    $packet->title = $message;
    $packet->unknownShort = 0;
    $packet->color = 0;
    $packet->overlay = 1;
    $player->dataPacket($packet);
  }

}
