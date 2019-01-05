<?php

namespace BattleRoyale\Items;

use pocketmine\item\ProjectileItem;

class Granate extends ProjectileItem {

  public function __construct($meta = 0, $count = 1){
    parent::__construct(384, $meta, "Granada");
  }

  public function getProjectileEntityType(): string{
    return "GranateEntity";
  }

  public function getThrowForce(): float{
    return 1;
  }

}
