<?php

namespace BattleRoyale\Guns;

use pocketmine\nbt\tag\CompoundTag;

class GoldenGun extends GunClass {

  static $max = 25;
  static $zoom = false;
  static $ammo = 25;
  static $damage = 3;
  static $reload = 5;
  static $reloading = false;
  static $time = 0;

  public function __construct($meta = 0, $count = 1){
    parent::__construct(418, $meta, "SMG Submachine");
  }

}