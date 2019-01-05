<?php  

namespace BattleRoyale\AirDrop;

use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;

class AirDropManager {

	public static function addAirDrop(Player $player, array $contents, int $id): void{
		$nbt = new CompoundTag();
		$nbt->setInt("x", $player->getX());
		$nbt->setInt("y", ($player->getY() - 3));
		$nbt->setInt("z", $player->getZ());
		$nbt->setString("id", Tile::CHEST);
		$nbt->setString("CustomName", "AirDrop Inventory");
		$chest = Tile::createTile("BoxChest", $player->getLevel(), $nbt);
		$chest::$entity = $id;
		$block = BlockFactory::get(54, 0);
		$block->x = $chest->x;
		$block->y = $chest->y;
		$block->z = $chest->z;
		$block->level = $player->getLevel();
		$player->getLevel()->sendBlocks(array($player), array($block));
		$chest->spawnTo($player);
		$chest->setNewInventory($contents);
		$player->addWindow($chest->getInventory());
		$chest->getInventory()->setContents($contents);
		
	}

	public static function updateInventory(int $id, array $contents, Level $level): void{
		if(!is_null($entity = $level->getEntity($id))){
			$entity->setInventory($contents);
		}
	}

}