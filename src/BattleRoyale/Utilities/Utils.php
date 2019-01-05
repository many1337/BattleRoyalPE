<?php

namespace BattleRoyale\Utilities;

use BattleRoyale\GameManager;
use BattleRoyale\Game\Arena;
use BattleRoyale\Sessions\Playing;
use BattleRoyale\Sessions\Creator;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use ZipArchive;

class Utils {

	public static function getPlayer(string $player): ?Playing{
		return array_key_exists($player, GameManager::$players) ? GameManager::$players[$player] : null;
	}

	public static function resetPlayer(Playing $session, $cause = false, $died = false, $win = false, $over = false): void{
		$player = $session->getPlayer();
		$session::$custom = $cause;
		$session->getArena()->removePlayer($player->getName());
		if($over){
			$player->sendMessage(TextFormat::RED."Se ha agotado el tiempo de la partida!");
			$session->setPosition(100);
		}
		if($died){
			$player->addTitle(TextFormat::YELLOW."Has muerto...", TextFormat::GRAY."posicion: #".$session->getPosition());
		}
		if($win){
			$player->sendMessage(TextFormat::YELLOW."Felicitaciones has ganado esta partida!");
			$player->addTitle(TextFormat::YELLOW."Has ganado!", TextFormat::GRAY."posicion: #".$session->getPosition());
		}
		$player->sendMessage(TextFormat::YELLOW."> Calculando tus stats por favor espera...");
		$player->sendMessage($message = str_repeat(TextFormat::GRAY."=", 36));
		foreach($session->calculateStats() as $key => $value){
			$player->sendMessage(TextFormat::YELLOW.$key.TextFormat::GRAY." >> ".TextFormat::WHITE.$value);
		}
		$player->sendMessage($message);
		unset($message);
		$session->deleteSession();
	}

	public static function unzipLevel(string $file): ?Level{
		$zip = new ZipArchive;
		if(($plugin = GameManager::getInstance())->getServer()->isLevelLoaded($name = str_replace(".zip", "", $file))){
			$plugin->getServer()->unloadLevel($plugin->getServer()->getLevelByName($name));
		}
		if($zip->open($plugin->getDataFolder()."Maps/$file")){
			$zip->extractTo($plugin->getServer()->getDataPath()."worlds/$name");
			$plugin->getServer()->loadLevel($name);
		}else{
			$plugin->getLogger()->warning(TextFormat::RED."> Se ha producido un error al tratar de descomprimir este mundo!");
		}
		return $plugin->getServer()->getLevelByName($name);
	}

	public static function getVector(string $vector, $center = ":"): Vector3{
		$values = explode($center, $vector);
		if(count($values) < 3){
			return new Vector3(0, 0, 0);
		}else{
			return new Vector3($values[0], $values[1], $values[2]);
		}
	}

	public static function addArena($config, string $arena, string $directory): bool{
		if(count($config->getAll()) !== 7){
			GameManager::getInstance()->getLogger()->warning(TextFormat::RED."Faltan valores para la arena: ".$arena);
			return false;
		}
		if(!is_file($directory.$config->get("level", "~").".zip")){
			GameManager::getInstance()->getLogger()->warning(TextFormat::RED."Falta el mapa para la arena: ".$arena);
			return false;
		}
		if(is_null($config->get("max")) || !is_numeric($config->get("max"))){
			$config->set("max", 5);
			$config->save();
		}
		if(is_null($config->get("radius")) || !is_numeric($config->get("radius")) || $config->get("radius") < 150){
			$config->set("radius", 150);
			$config->save();
		}
		if(!is_array($config->get("chests", array())) || is_null($config->get("chests", array()))){
			$config->set("chests", array());
			$config->save();
		}
		if(is_null($config->get("level"))){
			GameManager::getInstance()->getLogger()->warning(TextFormat::RED."Falta el nombre del mapa para la arena: ".$arena);
			return false;
		}
		if(is_null($config->get("center")) || !is_string($config->get("center")) || substr_count($config->get("center"), ":") !== 2){
			$config->set("center", "0:100:0");
			$config->save();
		}
		if(is_null($config->get("lobby")) || !is_string($config->get("lobby")) || substr_count($config->get("lobby"), ":") !== 2){
			$config->set("lobby", "0:100:0");
			$config->save();
		}
		if(is_null($config->get("direction")) || !is_numeric($config->get("direction"))){
			$config->set("direction", 1);
			$config->save();
		}
		GameManager::getInstance()->arenas[$arena] = new Arena($config->getAll(), $arena);
		assert(isset(GameManager::arenas[$arena]) === true);
		GameManager::getInstance()->getLogger()->info(TextFormat::GREEN."Se ha agregado una nueva arena: ".$arena);
		return true;
	}

	public static function isCreating(string $player): ?Creator{
		return array_key_exists($player, GameManager::$creators) ? GameManager::$creators[$player] : null;
	}

}