<?php

namespace BattleRoyale\Sessions;

use BattleRoyale\GameManager;
use BattleRoyale\Utilities\Utils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Creator {

  public $arena;
  public $player;

  public $options = array(
    "level" => null, 
    "direction" => true, 
    "max" => null, 
    "lobby" => null, 
    "center" => null, 
    "radius" => null, 
    "chests" => array()
  );

  public function __construct(Player $player, string $arena){
    $this->arena = $arena;
    $this->player = $player;
  }

  public function getPlayer(): ?Player{
    return $this->player;
  }

  public function getChests(): array{
    return $this->options["chests"];
  }

  public function addChest(string $vector): void{
    $this->options["chests"][] = $vector; 
  }

  public function removeChest(string $vector): void{
    if(in_array($vector, $this->options["chests"])){
      unset($this->options["chests"][array_search($vector, $this->options["chests"])]);
    }
  }

  public function setLevel(string $levelname):void{
    $this->options["level"] = $levelname;
  }

  public function setRadius(int $value): void{
    $this->options["radius"] = $value;
  }

  public function setCenter(string $center): void{
    $this->options["center"] = $center;
  }

  public function setMax(int $amount): void{
    $this->options["max"] = $amount;
  }

  public function setLobby(string $lobby): void{
    $this->options["lobby"] = $lobby;
  }

  public function setStart(string $start): void{
    $this->options["start"] = $start;
  }

  public function finishArena(){
    foreach($this->options as $key => $value){
      if(is_null($value)){
        break;
        $this->getPlayer()->sendMessage(TextFormat::RED."Necesitas llenar todos los requisitos, valor faltante: ".TextFormat::YELLOW.$key);
        return;
      }
    }
    $path = ($plugin = GameManager::getInstance())->getServer()->getDataPath();
    $zip = new ZipArchive;
    $zip->open($plugin->getDataFolder()."Maps/".$this->options["level"].".zip", ZipArchive::CREATE);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path."worlds/".$this->options["level"]));
    foreach($files as $file){
      if(is_file($file)){
        $zip->addFile($file, str_replace("\\", "/", ltrim(substr($file, strlen($path."worlds/".$this->options["level"])), "/\\")));
      }
    }
    $zip->close();
    $config = new Config($plugin->getDataFolder()."Games/".$this->getArena().".yml", Config::YAML, []);
    foreach($this->options as $key => $value){
      $config->set($key, $value);
      $config->save();
    }
    unset(GameManager::getInstance()::$creators[$this->getPlayer()->getName()]);
    Utils::addArena($config, $this->getArena(), $plugin->getDataFolder()."Maps/");
  }

  public function getArena(): string{
    return $this->arena;
  }

}
