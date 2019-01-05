<?php

namespace BattleRoyale\Guns;

use pocketmine\nbt\tag\CompoundTag;

class IronGun extends GunClass {

  static $max = 20;
  static $zoom = false;
  static $ammo = 20;
  static $damage = 4;
  static $reload = 7;
  static $reloading = false;
  static $time = 0;
  
  public function __construct($meta = 0, $count = 1){
    parent::__construct(417, $meta, "M416");
  }

}