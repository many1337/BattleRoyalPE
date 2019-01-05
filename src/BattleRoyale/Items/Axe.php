<?php 

namespace BattleRoyale\Items;

use pocketmine\item\Axe as RoyaleAxe;

class Axe extends RoyaleAxe {

	public $tier = 3;

	public function __construct($id = 275, $meta = 0, $name = "Axe", $tier = 3){
		parent::__construct($id, $meta, $name, $tier);
	}

	public function useOn($object){
		return true;
	}
	
}


