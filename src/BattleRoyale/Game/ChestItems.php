<?php  

namespace BattleRoyale\Game;

use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\tile\Chest;
use pocketmine\level\Level;
use BattleRoyale\Utilities\Utils;
use BattleRoyale\AirDrop\BoxEntity;

class ChestItems {

	//custom_name => array(item_id, meta, stackable, max_count)

	private static $airdrop = array(
		"Espada legendaria" => array(Item::DIAMOND_SWORD, 0, false, 1),
		"Diamante legendario" => array(Item::DIAMOND, 0, true, 5),
		"Hierro legendario" => array(Item::IRON_INGOT, 0, true, 9),
		"Rifle" => array(Item::DIAMOND_HORSE_ARMOR, 0, false, 1),
		"Perchera de diamante" => array(Item::DIAMOND_CHESTPLATE, 0, false, 1),
		"Pantalones de diamante" => array(Item::DIAMOND_LEGGINGS, 0, false, 1),
		"Botas de diamante" => array(Item::DIAMOND_BOOTS, 0, false, 1),
		"Vendas (+6 corazones)" => array(Item::GOLDEN_APPLE, 1, true, 2),
	);

	private static $chestitems = array(
		"Pico normal" => array(Item::IRON_PICKAXE, 0, false, 1),
		"Manzana magica" => array(Item::APPLE, 0, true, 10),
		"Arco normal" => array(Item::BOW, 0, false, 1),
		"Flechas" => array(Item::ARROW, 0, true, 10),
		"Espada mortal de hierro" => array(Item::IRON_SWORD, 0, false, 1),
		"Espada de novato" => array(Item::WOODEN_SWORD, 0, false, 1),
		"Pico de novato" => array(Item::WOODEN_PICKAXE, 0, false, 1),
		"Acha para novato" => array(Item::WOODEN_AXE, 0, false, 1),
		"Espada de piedra" => array(Item::STONE_SWORD, 0, false, 1),
		"Pico de piedra" => array(Item::STONE_PICKAXE, 0, false, 1),
		"Pistola" => array(Item::LEATHER_HORSE_ARMOR, 0, false, 1),
		"M416" => array(Item::IRON_HORSE_ARMOR, 0, false, 1),
		"SMG Submachine" => array(Item::GOLD_HORSE_ARMOR, 0, false, 1),
		"Lingote de oro" => array(Item::GOLD_INGOT, 0, true, 5),
		"Palito" => array(Item::STICK, 0, true, 5),
		"Bol" => array(Item::BOWL, 0, false, 1),
		"Estofado de hongos" => array(Item::MUSHROOM_STEW, 0, false, 1),
		"Espada de oro" => array(Item::GOLD_SWORD, 0, false, 1),
		"Pico de oro" => array(Item::GOLD_PICKAXE, 0, false, 1),
		"Acha de oro" => array(Item::GOLD_AXE, 0, false, 1),
		"Cuerda" => array(Item::STRING, 0, true, 5),
		"Pan" => array(Item::BREAD, 0, true, 10),
		"Bloques" => array(Block::SANDSTONE, 0, true, 64),
		"Tunica de cuero" => array(Item::LEATHER_TUNIC, 0, false, 1),
		"Botas de cuero" => array(Item::LEATHER_BOOTS, 0, false, 1),
		"Pantalones de cuero" => array(Item::LEATHER_PANTS, 0, false, 1),
		"Perchera de mallas" => array(Item::CHAIN_CHESTPLATE, 0, false, 1),
		"Pantalones de mallas" => array(Item::CHAIN_LEGGINGS, 0, false, 1),
		"Botas de mallas" => array(Item::CHAIN_BOOTS, 0, false, 1),
		"Perchera de hierro" => array(Item::IRON_CHESTPLATE, 0, false, 1),
		"Pantalones de hierro" => array(Item::IRON_LEGGINGS, 0, false, 1),
		"Botas de hierro" => array(Item::IRON_BOOTS, 0, false, 1),
		"Perchera de oro" => array(Item::GOLD_CHESTPLATE, 0, false, 1),
		"Pantalones de oro" => array(Item::GOLD_LEGGINGS, 0, false, 1),
		"Botas de oro" => array(Item::GOLD_BOOTS, 0, false, 1),
		"Carne de cerdo cruda" => array(Item::RAW_PORKCHOP, 0, true, 10),
		"Carne de cerdo cocida" => array(Item::COOKED_PORKCHOP, 0, true, 9),
		"Vendas" => array(Item::GOLDEN_APPLE, 0, true, 7),
		"Cubo vacio" => array(Item::BUCKET, 0, false, 1),
		"Cubo de agua" => array(Item::BUCKET, 8, false, 1),
		"Cubo de lava" => array(Item::BUCKET, 10, false, 1),
		"Cuero" => array(Item::LEATHER, 0, true, 10),
		"Huevo constructor" => array(Item::EGG, 0, true, 2),
		"Pescado crudo" => array(Item::RAW_FISH, 0, true, 7),
		"Pescado cocido" => array(Item::COOKED_FISH, 0, true, 8),
		"Galleta" => array(Item::COOKIE, 0, true, 14),
		"Melon cortado" => array(Item::MELON, 0, true, 16),
		"Carne de res cruda" => array(Item::RAW_BEEF, 0, true, 8),
		"Carne de res cocida" => array(Item::STEAK, 0, true, 6),
		"Carne de pollo cruda" => array(Item::RAW_CHICKEN, 0, true, 10),
		"Carne de pollo cocida" => array(Item::COOKED_CHICKEN, 0, true, 7),
		"Carne podrida" => array(Item::ROTTEN_FLESH, 0, true, 3),
		"Escudo" => array(Item::POTION, 4, false, 1),
		"Velocidad" => array(Item::POTION, 14, false, 1),
		"Granada" => array(Item::EXPERIENCE_BOTTLE, 0, false, 1),
		"Bomba motolov" => array(Item::EXPERIENCE_BOTTLE, 1, false, 1),
		"Bola de fuego" => array(Item::FIRE_CHARGE, 0, false, 1),
		"Zanahorias" => array(Item::CARROT, 0, true, 11),
		"Patata cruda" => array(Item::POTATO, 0, true, 9),
		"Patata cocida" => array(Item::BAKED_POTATO, 0, true, 8),
		"Balas" => array(Item::MOB_HEAD, 0, true, 25),
		"Conejo crudo" => array(Item::RAW_RABBIT, 0, true, 7),
		"Conejo cocido" => array(Item::COOKED_RABBIT, 0, true, 6),
		"Madera para construccion" => array(Block::PLANKS, 0, true, 10),
		"Piedra para construccion" => array(Block::COBBLESTONE, 0, true, 10)
		//...
	);

	public static function fillChest(Chest $chest): void{
		$chest->getInventory()->clearAll();
		for($position = 0; $position < $chest->getInventory()->getDefaultSize(); ++$position){
			if(mt_rand(0, 6) === 3){
				$name = array_rand(ChestItems::$chestitems);
				$data = ChestItems::$chestitems[$name];
				$item = Item::get($data[0], $data[1], $data[2] === true ? mt_rand(1, $data[3]) : 1);
				$item->setCustomName($name);
				$chest->getInventory()->setItem($position, $item);
			}
		}
	}

	public static function setLevel(Level $level, array $data): void{
		if(count($data) > 0){
			foreach($data as $vector){
				$vector3 = Utils::getVector($vector);
				if(!($level->getTile($vector3) instanceof Chest)){
					$level->setBlock($vector3, Block::get(Block::AIR));
					$level->setBlock($vector3, Block::get(Block::CHEST));
				}
				if(!is_null($level->getTile($vector3))){
					ChestItems::fillChest($level->getTile($vector3));
				}
			}
		}else{
			foreach($level->getTiles() as $chest){
				if($chest instanceof Chest){
					ChestItems::fillChest($chest);
				}
			}
		}
	}

	public static function fillAirDrop(BoxEntity $entity): void{
		$inventory = array();
		for($position = 0; $position < 10; ++$position){
			if(in_array(mt_rand(0, 6), array(1, 3))){
				$name = array_rand(ChestItems::$airdrop);
				$data = ChestItems::$airdrop[$name];
				$item = Item::get($data[0], $data[1], $data[2] === true ? mt_rand(1, $data[3]) : 1);
				$item->setCustomName($name);
				$inventory[] = $item;
			}
		}
		$entity->setInventory($inventory);
		unset($inventory);
	}

}