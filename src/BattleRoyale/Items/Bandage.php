<?php

namespace BattleRoyale\Items;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class Bandage extends RoyaleFood {

  public function __construct($meta = 0, $count = 1){
    parent::__construct(322, $meta, "Bandage");
  }

  public function onConsume(Living $player){
    if($player->getHealth() >= $player->getMaxHealth()){
      $player->sendPopup("Tu salud esta al maximo!");
      return;
    }else{
      if($player->hasEffect(Effect::REGENERATION)){
        $effect = $player->getEffect(Effect::REGENERATION);
        $effect->setDuration($effect->getDuration() + $this->getEffect()->getDuration());
        $player->addEffect($effect);
      }else{
        $player->addEffect($this->getEffect());
      }
      parent::onConsume($player);
    }
  }

  public function getEffect(): EffectInstance{
    $effect = new EffectInstance(Effect::getEffect(Effect::REGENERATION), 1, 3, false);
    if($this->getDamage() === 0){
      $effect->setDuration(1 * 20);
    }else{
      $effect->setDuration(3 * 20);
    }
    return $effect;
  }

}
