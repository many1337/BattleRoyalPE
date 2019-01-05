<?php

namespace BattleRoyale\Items;

use pocketmine\item\ProjectileItem;

class Fireball extends ProjectileItem {

  public function __construct($meta = 0, $count = 1){
    parent::__construct(385, $meta, "Fireball");
  }

  public function getProjectileEntityType(): string{
    return "FireEntity";
  }

  public function getThrowForce(): float{
    return 1.5;
  }

}
