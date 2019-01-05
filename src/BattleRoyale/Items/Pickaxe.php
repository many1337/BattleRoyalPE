<?php

namespace BattleRoyale\Items;

use pocketmine\item\Pickaxe as RoyalePickaxe;

class Pickaxe extends RoyalePickaxe {

	public $tier = 3;

	public function __construct($id = 274, $meta = 0, $name = "Pickaxe", $tier = 3){
		parent::__construct($id, $meta, $name, $tier);
	}

	public function useOn($object){
		return true;
	}
	
}
