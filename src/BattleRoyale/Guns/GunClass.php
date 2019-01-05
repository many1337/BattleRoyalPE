<?php

namespace BattleRoyale\Guns;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\item\ItemBlock;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\entity\Entity;
use BattleRoyale\EntityManager;

abstract class GunClass extends Item {

  static protected $max;
  static protected $zoom;
  static protected $ammo;
  static protected $damage;
  static protected $reload;
  static protected $reloading;
  static protected $time;

  public function __construct(int $id, int $meta, string $name = "Unknown"){
    parent::__construct($id, $meta, $name);
  }

  public function getAmmo(): int{
    return static::$ammo;
  }

  public function canZoom(): bool{
    return static::$zoom;
  }

  public function setAmmo(int $value): void{
    static::$ammo = $value;
  }

  public function getReloadTime(): int{
    return static::$reload;
  }

  public function isReloading(): bool{
    return static::$reloading;
  }

  public function setReloading(bool $value){
    static::$reloading = $value;
  }

  public function getMax(): int{
    return static::$max;
  }

  public function getCurrentTime(){
    return static::$time;
  }

  public function setCurrentTime(int $value): void{
    static::$time = $value;
  }

  public function getDamageValue(): int{
    return static::$damage;
  }

  public function hasAmmo(Player $player): int{
    $has = null;
    for($i = 0; $i < $player->getInventory()->getDefaultSize() - 4; ++$i){
      $item = $player->getInventory()->getItem($i);
      if($item->getId() === 397){
        $has = $item;
        break;
      }else{
        continue;
      }
    }
    if($has instanceof ItemBlock){
      $count = $has->getCount();
      if($count >= $this->getMax()){
        $count = $this->getMax();
      }else if($count > 0){
        $count = $this->getMax() - (abs($this->getMax() - $count));
      }else{
        $count = 0;
      }
      $player->getInventory()->removeItem(ItemBlock::get($has->getId(), $has->getDamage(), $count));
      return $count;
    }else{
      return 0;
    }
  }

  public function checkStatus(Player $player){
    if($this->isReloading()){
      $end = microtime(true);
      if(($time = round($end - $this->getCurrentTime(), 3)) >= $this->getReloadTime()){
        $this->setReloading(false);
        $this->setCurrentTime(0);
        return true;
      }else{
        $player->sendPopup(TextFormat::GOLD."Tu arma se esta recargando, ".TextFormat::GREEN.$time.TextFormat::RED." segundos restantes...");
        return false;
      }
    }else{
      if($this->getAmmo() <= 0){
        if(($ammo = $this->hasAmmo($player)) <= 0){
          $player->sendPopup(TextFormat::RED."No tienes municion, ".TextFormat::WHITE."$ammo");
          return false;
        }else{
          $this->setReloading(true);
          $this->setCurrentTime(microtime(true));
          $this->setAmmo($ammo);
          $this->checkStatus($player);
        }
      }else{
        return true;
      }
    }
  }

  public function getMaxStackSize(): int{
    return 1;
  }

  public function shootClass(Item $item, Player $player): void{
    $motion = 0.0;
    switch($item->getId()){
      case 416:
      $motion = 1.8;
      break;
      case 417:
      $motion = 2.1;
      break;
      case 418:
      $motion = 2.5;
      break;
      case 419:
      $motion = 3.5;
      break;
    }
    $bullet = Entity::createEntity("RoyalAmmo", $player->getLevel(), EntityManager::getCompoundMotion($player), $player);
    $bullet->setMotion($bullet->getMotion()->multiply($motion));
    $bullet->setDamageValue($this->getDamageValue());
    $bullet->spawnToAll();
    $player->getLevel()->addSound(new BlazeShootSound($player), array_merge(array($player), $player->getViewers()));
    $this->setAmmo($this->getAmmo() - 1);
    if($this->getAmmo() < 1){
      $this->checkStatus($player);
    }
  }

  public function useGun(Player $player): void{
    if($this->checkStatus($player)){
      $this->shootClass($player->getInventory()->getItemInHand(), $player);
      $player->setXpLevel($this->getAmmo());
    }
  }

}