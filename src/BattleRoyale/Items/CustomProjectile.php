<?php  

namespace BattleRoyale\Items;

use pocketmine\Player;
use pocketmine\entity\projectile\Projectile;
use pocketmine\network\mcpe\protocol\AddEntityPacket;

abstract class CustomProjectile extends Projectile {

	public $width = 0.25;
	public $height = 0.25;
	public $gravity = 0.03;
	public $drag = 0.01;

	public function spawnTo(Player $player): void{
		parent::spawnTo($player);
		$packet = new AddEntityPacket();
		$packet->type = static::NETWORK_ID;
		$packet->mation = $this->getMotion();
		$packet->position = $this->asVector3();
		$packet->entityRuntimeId = $this->getId();
		$packet->pitch = $this->pitch;
		$packet->yaw = $this->yaw;
		$packet->metadata = $this->getDataPropertyManager()->getAll();
		$player->dataPacket($packet);
	}

}