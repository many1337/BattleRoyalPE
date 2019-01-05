<?php

namespace BattleRoyale\Guns;

use pocketmine\nbt\tag\CompoundTag;

class LeatherGun extends GunClass {

  static $max = 10;
  static $zoom = false;
  static $ammo = 10;
  static $damage = 2;
  static $reload = 5;
  static $reloading = false;
  static $time = 0;

  public function __construct($meta = 0, $count = 1){
    parent::__construct(416, $meta, "Pistola");
  }

}
