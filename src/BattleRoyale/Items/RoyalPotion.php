<?php 

namespace BattleRoyale\Items;

use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;

class RoyalPotion extends RoyaleFood {

	public function __construct($meta = 0, $count = 1){
		parent::__construct(373, $meta, "Royale Potion");
	}

	public function onConsume(Living $entity){
		if($entity instanceof Player){
			$potion = $this->getEffect();
			if(is_null($potion)){
				return;
			}
			if($entity->hasEffect($potion->getId())){
				$effect = $entity->getEffect($potion->getId());
				if($this->canReplaceAmplifier()){
					$effect->setAmplifier($potion->getAmplifier());
				}else{
					if($effect->getAmplifier() < $potion->getAmplifier()){
						$effect->setAmplifier($potion->getAmplifier());
					}
				}
				$effect->setDuration($effect->getDuration() + $potion->getDuration());
				$effect->setVisible($potion->isVisible());
				$entity->addEffect($effect);
			}else{
				$entity->addEffect($potion);
			}
			parent::onConsume($entity);
		}
	}

	public function canReplaceAmplifier(): bool{
		return $this->getDamage() === 4 || $this->getDamage() === 14;
	}

	public function getEffect(): EffectInstance{
		if($this->getDamage() === 4){
			$effect = Effect::getEffect(11);
			return new EffectInstance($effect, 45 * 20, 0, false);
		}
		if($this->getDamage() === 14){
			$effect = Effect::getEffect(1);
			return new EffectInstance($effect, 60 * 20, 0, false);
		}
	}

}