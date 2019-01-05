<?php

namespace BattleRoyale\Items;

use pocketmine\entity\Entity;
use pocketmine\math\RayTraceResult;
use pocketmine\Player;

class FireEntity extends CustomProjectile {

	const NETWORK_ID = 94;

	public function onHitEntity(Entity $entity, RayTraceResult $result): void{
		if($entity instanceof Player){
			parent::onHitEntity($entity, $result);
			$entity->setOnFire(10);
		}
	}

	public function getName(): string{
		return "Fireball";
	}

	public function onUpdate(int $currentTick): bool{
		parent::onUpdate($currentTick);
		if($this->isCollided){
			$this->close();
		}
		return true;
	}

}