<?php

namespace BattleRoyale\Ammo;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Snowball;
use pocketmine\level\particle\FlameParticle;

class RoyalAmmo extends Snowball {

  private $playerDamage = 0;

  public function onUpdate(int $currentTick): bool{
    parent::onUpdate($currentTick);
    if(!$this->closed){
      $this->level->addParticle(new FlameParticle($this), $this->getViewers());
    }
    return true;
  }

  public function getDamageValue(): int{
    return $this->playerDamage;
  }

  public function setDamageValue(int $value){
    $this->playerDamage = $value;
  }

  public function canCollideWith(Entity $entity): bool{
    return $entity !== $this->getOwningEntity();
  }

}
