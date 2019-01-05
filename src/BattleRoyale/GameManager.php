<?php

namespace BattleRoyale;

use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use BattleRoyale\Timer\BattleTask;
use BattleRoyale\Commands\Create;
use BattleRoyale\Commands\BattleRoyale;
use BattleRoyale\AirDrop\BoxChest;
use BattleRoyale\Listener\BattleRoyaleEvents;
use BattleRoyale\Items\Bandage;
use BattleRoyale\Items\Pickaxe;
use BattleRoyale\Items\Axe;
use BattleRoyale\Items\RoyalPotion;
use BattleRoyale\Items\Granate;
use BattleRoyale\Items\Fireball;
use BattleRoyale\Items\FireEntity;
use BattleRoyale\Items\Launcher;
use BattleRoyale\Items\EggLauncher;
use BattleRoyale\Items\RoyalCompass;
use BattleRoyale\Items\RoyaleEgg;
use BattleRoyale\Guns\IronGun;
use BattleRoyale\Guns\GoldenGun;
use BattleRoyale\Guns\DiamondGun;
use BattleRoyale\Guns\LeatherGun;
use BattleRoyale\Ammo\RoyalAmmo;
use BattleRoyale\Items\GranateEntity;
use BattleRoyale\AirDrop\BoxEntity;
use BattleRoyale\Utilities\Utils;

class GameManager extends PluginBase {

	static $instance;
	static $players = array();
	static $creators = array();

	public $arenas = array();

	public function onEnable(){
		$directory = $this->getDataFolder();
		if(!is_dir($directory."Games/") || !is_dir($directory."Maps/")){
			if(!is_dir($directory)){
				mkdir($directory);
			}
			if(!is_dir($directory."Games/")){
				mkdir($directory."Games/");
			}
			if(!is_dir($directory."Maps/")){
				mkdir($directory."Maps/");
			}
			$this->saveResource("config.yml");
		}
		$items = array(
			416 => LeatherGun::class, 
			417 => IronGun::class, 
			418 => GoldenGun::class, 
			419 => DiamondGun::class, 
			322 => Bandage::class, 
			274 => Pickaxe::class, 
			373 => RoyalPotion::class, 
			345 => RoyalCompass::class, 
			384 => Granate::class, 
			344 => RoyaleEgg::class, 
			288 => Launcher::class,
			275 => Axe::class,
			385 => Fireball::class
		);
		foreach($items as $id => $class){
			ItemFactory::registerItem(new $class(), true);
		}
		$entities = array(
			RoyalAmmo::class,  
			BoxEntity::class, 
			GranateEntity::class, 
			EggLauncher::class,
			FireEntity::class
		);
		foreach($entities as $entity){
			Entity::registerEntity($entity, true);
		}
		Tile::registerTile(BoxChest::class);
		new BattleRoyaleEvents($this);
		GameManager::$instance = $this;
		$commands = array(
			new Create($this), 
			new BattleRoyale($this)
		);
		foreach($commands as $class){
			$this->getServer()->getCommandMap()->register($class->getCommand(), $class);
		}
		foreach(scandir($folder = $directory."Games/") as $file){
			if($file == ".." || $file == "."){
				continue;
			}
			Utils::addArena(new Config($folder.$file, Config::YAML, []), str_replace(".yml", "", $file), $directory."Maps/");
		}
		$this->getScheduler()->scheduleRepeatingTask(new BattleTask($this), 20);
		$this->getLogger()->info("El plugin se ha cargado");
	}

	public static function getInstance(): GameManager{
		return GameManager::$instance;
	}

}
