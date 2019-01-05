<?php

namespace BattleRoyale\BossBar;

use pocketmine\entity\Attribute;

class BossSettings extends Attribute {

  public function __construct(){
    //Hola xD
  }

  public function getMinValue(): float{
    return 1;
  }

  public function getMaxValue(): float{
    return 600;
  }

  public function getValue(): float{
    return $this->getMaxValue();
  }

  public function getName(): string{
    return "minecraft:health";
  }

  public function getDefaultValue(): float{
    return $this->getValue();
  }

}
