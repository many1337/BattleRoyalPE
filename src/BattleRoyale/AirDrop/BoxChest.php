<?php 

namespace BattleRoyale\AirDrop;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\level\Level;

class BoxChest extends Chest {

	static $entity;
	private $contents;

	public function __construct(Level $level, CompoundTag $compound){
		parent::__construct($level, $compound);
		$this->block = array($this->getBlock()->getId(), $this->getBlock()->getDamage());
	}

	public function setNewInventory(array $contents): void{
		$this->contents = $contents;
	}

	public function getItemsData(): ?array{
		return $this->contents;
	}

	public function sendReplacement(Player $who){
		$block = $this->getReplacement();
		$block->x = $this->x;
		$block->y = $this->y;
		$block->z = $this->z;
		$block->level = $this->getLevel();
		if(!is_null($block->level)){
			$block->level->sendBlocks(array($who), array($block));
		}
	}

	public function getEntity(): int{
		return BoxChest::$entity;
	}

	public function getInventory(): BoxWindow{
		return new BoxWindow($this);
	}
	
	public function getReplacement(): Block{
		return Block::get(...$this->block);
	}


}