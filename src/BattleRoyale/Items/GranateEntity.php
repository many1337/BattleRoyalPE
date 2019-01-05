<?php

namespace BattleRoyale\Items;

use pocketmine\utils\TextFormat;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\block\Block;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\particle\HugeExplodeParticle;

class GranateEntity extends CustomProjectile {

	const NETWORK_ID = 68;

	private $type = 0;

	public function getName(): string{
		return TextFormat::YELLOW."> Granada <";
	}

	public function setType(int $type){
		$this->type = $type;
	}

	public function getType(){
		return $this->type;
	}

	public function explode(){
		if(is_null($this->level)){
		    return;	
		}
		if($this->getType() === 0){
			$explosion = new Explosion(new Position($this->x, $this->y, $this->z, $this->level), 4);
			$explosion->explodeA();
			unset($explosion);
		    $explosion = new Explosion(new Position($this->x, $this->y, $this->z, $this->level), 2);
		    $explosion->explodeB();
		}else{
			$this->level->addSound(new BlazeShootSound($this), $this->getViewers());
			for($x = 0; $x < 3; ++$x){
				for($z = 0; $z < 3; ++$z){
					$block = $this->level->getBlock($this->add($x, 0, $z));
					if($block->getId() !== 0){
						$block = $block->getSide(1);
						if($block->getId() !== 0){
							$block = null;
						}
					}
					if(!is_null($block)){
						if(mt_rand(0, 2) === 1){
							$this->level->setBlock($block, Block::get(Block::FIRE, 0));
						}
					}
				}
			}
		}
		if($this->isAlive()){
			$this->close();
		}
	}

	public function onUpdate(int $currentTick): bool{
		parent::onUpdate($currentTick);
		if($this->isCollided){
			$this->explode();
		}
		return true;
	}

}