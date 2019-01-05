<?php

namespace BattleRoyale\Guns;

use pocketmine\nbt\tag\CompoundTag;

class DiamondGun extends GunClass {

  static $max = 15;
  static $zoom = true;
  static $ammo = 15;
  static $damage = 5;
  static $reload = 10;
  static $reloading = false;
  static $time = 0;

  public function __construct($meta = 0, $count = 1){
    parent::__construct(419, $meta, "Rifle");
  }

}