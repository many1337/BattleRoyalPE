<?php  

namespace BattleRoyale\Items;

use pocketmine\item\Egg;

class RoyaleEgg extends Egg {

	public function getProjectileEntityType(): string{
		return "EggLauncher";
	}

	public function getThrowForce(): float{
		return 1.5;
	}

}