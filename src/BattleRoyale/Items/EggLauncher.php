<?php  

namespace BattleRoyale\Items;

use pocketmine\block\Block;

class EggLauncher extends CustomProjectile {

	const NETWORK_ID = 82;

	public function getName(): string{
		return "Constructor";
	}

	public function build(){
		for($x = 0; $x < 2; ++$x){
			for($z = 2; $z > 0; --$z){
				$this->level->setBlock($this->add($x, 0, $z), Block::get(Block::SANDSTONE, 0));
			}
		}
	}

	public function entityBaseTick(int $tickDiff = 1): bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if(!$this->isCollided){
			$this->build();
		}else{
			if($this->isAlive()){
				$this->close();
			}
		}
		return $hasUpdate;
	}

}