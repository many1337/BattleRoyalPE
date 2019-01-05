<?php

namespace BattleRoyale;

use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

abstract class EntityManager extends Living {

	public $width = 1;

	public function attack(EntityDamageEvent $source): void {
		if($source->getCause() !== EntityDamageEvent::CAUSE_VOID){
			$source->setCancelled();
		}else{
			if($this->isAlive()){
				$this->close();
			}
		}
	}

	public function spawnTo(Player $player): void{
		$packet = new AddEntityPacket();
		$packet->type = static::NETWORK_ID;
		$packet->motion = $this->getMotion();
		$packet->position = $this->asVector3();
		$packet->entityRuntimeId = $this->getId();
		$packet->pitch = $this->pitch;
		$packet->yaw = $this->yaw;
		$packet->metadata = $this->getDataPropertyManager()->getAll();
		$player->dataPacket($packet);
		parent::spawnTo($player);
	}

	public static function getCompoundMotion(Player $player): CompoundTag{
		$data = new CompoundTag("", [
			new ListTag("Pos", array(
				new DoubleTag("", $player->getX()), 
				new DoubleTag("", $player->getY() + $player->getEyeHeight()), 
				new DoubleTag("", $player->getZ())
			)),
			new ListTag("Motion", array(
				new DoubleTag("", -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)), 
				new DoubleTag("", -sin($player->pitch / 180 * M_PI)), 
				new DoubleTag("", cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))
			)),
			new ListTag("Rotation", array(
				new FloatTag("", $player->yaw), 
				new FloatTag("", $player->pitch)
			))
		]);
		return $data;
	}
}