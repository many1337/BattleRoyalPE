<?php

namespace BattleRoyale\Game;

use BattleRoyale\GameManager;
use BattleRoyale\Utilities\Utils;
use BattleRoyale\Zone\Storm;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use BattleRoyale\BossBar\BossManager;
use BattleRoyale\Sessions\Playing;
use BattleRoyale\AirDrop\BoxEntity;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class Arena {

	public $name;
	public $level;
	public $players;
	public $status;
	public $vector;
	public $lobby;
	public $max;
	public $chests;
	public $radius;
	public $direction;

	private $endtime;
	private $storm;
	private $restart;
	private $countdown;

	const WAITING = 0;
	const STARTING = 1;
	const RUNNING = 2;
	const RESTARTING = 3;

	public function __construct(array $config, string $arena){
		$this->name = $arena;
		$this->players = array();
		$this->max = (int) $config["max"];
		$this->radius = (int) $config["radius"];
		$this->chests = (array) $config["chests"];
		$this->level = GameManager::getInstance()->getServer()->getLevelByName((string) $config["level"]);
		$this->status = Arena::WAITING;
		$this->vector = Utils::getVector((string) $config["center"]);
		$this->lobby = Utils::getVector((string) $config["lobby"]);
		$this->direction = (bool) $config["direction"];
		$this->countdown = 60;
		$this->endtime = 0;
		$this->restart = 5;
		if(is_null($this->level)){
			if(($level = Utils::unzipLevel((string) $config["level"].".zip")) instanceof Level){
				$this->level = $level;
				$this->level->setTime(7000);
				$this->level->stopTime();
			}
		}
		$this->storm = new Storm($this);
		foreach($this->getLevel()->getEntities() as $entity){
			if($entity instanceof BoxEntity){
				$entity->close();
			}
		}
		$this->getStorm()->findZone();
	}

	public function isDirectionActivated(): bool{
		return $this->direction;
	}

	public function setLevel(Level $level): void{
		$this->level = $level;
	}

	public function getRadius(): int{
		return $this->radius;
	}

	public function getMaxPlayers(): int{
		return $this->max;
	}

	public function getVector(): Vector3{
		return $this->vector;
	}

	public function getChests(): array{
		return $this->chests;
	}

	public function getStorm(): Storm{
		return $this->storm;
	}

	public function isPlaying(string $player): bool{
		return array_key_exists($player, $this->getPlayers());
	}

	public function isAvailable(): bool{
		return ($this->getStatus() === Arena::WAITING || $this->getStatus() === Arena::STARTING) and $this->getCount() < $this->getMaxPlayers();
	}

	public function getName(): string{
		return $this->name;
	}

	public function getLevel(): Level{
		return $this->level;
	}

	private function resetGame(): void{
		if($this->getCount() > 0){
			foreach($this->getPlayers(true) as $session){
				Utils::resetPlayer($session, true, false, false, true);
			}
		}
		foreach($this->getLevel()->getEntities() as $entity){
			if($entity instanceof BoxEntity){
				$entity->close();
			}
		}
		if(($level = Utils::unzipLevel($this->getLevel()->getFolderName().".zip")) instanceof Level){
			$this->setLevel($level);
			$this->storm = new Storm($this);
			$this->getStorm()->findZone();
			$this->restart = 5;
			$this->endtime = 0;
			$this->setStatus(Arena::WAITING);
			$level->setTime(7000);
			$level->stopTime();
		}
	}

	public function getPlayers($session = false): array{
		if($session){
			return array_values($this->getPlayers());
		}else{
			return $this->players;
		}
	}

	public function broadcastStorm(): void{
		foreach($this->getPlayers(true) as $session){
			$session->sendMessage(TextFormat::GOLD."> El radio de la tormenta se ha reducido (-30), cuidado a donde vas...");
		}
	}

	public function getStatus(): int{
		return $this->status;
	}

	private function getLastSession(): Playing{
		return array_values($this->getPlayers())[0];
	}

	public function addPlayer(Playing $session): void{
		$this->players[$session->getPlayer()->getName()] = $session;
	}

	public function removePlayer(string $player): void{
		$session = $this->getPlayer($player);
		if($session instanceof Playing){
			$session->setPosition($this->getCount());
			unset($this->players[$player], $session);
		}
	}

	public function asPosition(Vector3 $vector): Position{
		return new Position((float) $vector->getX(), (float) $vector->getY(), (float) $vector->getZ(), $this->getLevel());
	}

	private function setStatus(int $value): void{
		$this->status = $value;
	}

	public function getPlayer(string $player): Playing{
		if($this->isPlaying($player)){
			return $this->players[$player];
		}
	}

	public function getCount(): int{
		return count(array_keys($this->getPlayers()));
	}

	public function getSpawn(): Vector3{
		return $this->lobby;
	}

	public function runGame(){
		if($this->getStatus() === Arena::WAITING){
			if($this->getCount() >= 2){
				if($this->countdown > 60){
					$this->countdown = 60;
				}
				foreach($this->getPlayers(true) as $session){
					$session->getPlayer()->sendMessage(TextFormat::GREEN."La partida comenzará pronto!");
				}
				$this->setStatus(Arena::STARTING);
				return false;
			}else{
				if($this->getCount() === 1){
					$this->getLastSession()->getPlayer()->sendTip(TextFormat::GOLD."Esperando por un jugador mas...");
				}
			}
		}
		if($this->getStatus() === Arena::STARTING){
			$this->countdown--;
			if($this->getCount() < 2){
				$this->countdown = 60;
				$this->setStatus(Arena::WAITING);
				return false;
			}
			foreach($this->getPlayers(true) as $session){
				$session->getPlayer()->sendTip(TextFormat::GREEN."Comenzando en: ".TextFormat::WHITE.$this->countdown);
			}
			if($this->countdown === 1){
				foreach($this->getPlayers(true) as $session){
					$session->sendMessage(TextFormat::BOLD.TextFormat::WHITE."Battle Royale ".TextFormat::RESET.TextFormat::GRAY."> ".TextFormat::RESET.TextFormat::YELLOW."La partida ha comenzado, buena suerte!");
					$session->getPlayer()->teleport($this->asPosition($this->getStorm()->getVector3()->add(0, 100)));
					$session->startGame();
					BossManager::addWindow($session->getPlayer());
				}
				$this->countdown = 60;
				ChestItems::setLevel($this->getLevel(), $this->getChests());
				$this->setStatus(Arena::RUNNING);
				return false;
			}
		}
		if($this->getStatus() === Arena::RUNNING){
			$this->endtime++;
			$this->getStorm()->updateZone();
			if($this->endtime % 150 === 0){
				if($this->getStorm()->removeZone()){
					$this->broadcastStorm();
				}
			}
			if($this->endtime % 180 === 0){
				$this->getStorm()->spawnAirDrop();
				foreach($this->getPlayers(true) as $session){
					$session->getPlayer()->sendMessage(TextFormat::YELLOW."> Ha aparecido una nueva caja, disfruta buscarla!");
				}
			}
			if($this->getCount() === 1){
				Utils::resetPlayer($this->getLastSession(), true, false, true);
				$this->setStatus(Arena::RESTARTING);
			}
			foreach($this->getPlayers(true) as $session){
				$lines = "\n".str_repeat(" ", 35);
				$player = $session->getPlayer();
				$storm = $this->getStorm()->getConvertedStatus();
				$status = $session->getZoneStatus();
				$kills = $session->getKills();
				$playersleft = count($this->getPlayers()) - 1;
				$timeleft = gmdate("i:s", ((60 * 20) - $this->endtime));
				$distance = $this->getStorm()->calculateDistance($player);
				$player->sendTip("\n".str_repeat(" ", 44).TextFormat::BOLD.
					TextFormat::GOLD."BATTLE ROYALE".$lines.
					TextFormat::YELLOW."Tormenta: ".$storm.$lines.
					TextFormat::YELLOW."Tu estado: ".$status.$lines.
					TextFormat::YELLOW."Tus matanzas: ".TextFormat::GREEN.$kills.$lines.
					TextFormat::YELLOW."Oponentes: ".TextFormat::GREEN.$playersleft.$lines.
					TextFormat::YELLOW."Tiempo restante: ".TextFormat::GREEN.$timeleft.$lines.
					TextFormat::YELLOW."Distancia a la tormenta: ".TextFormat::WHITE.$distance);
			}
			if($this->endtime % (5 * 60) === 0){
				foreach($this->getPlayers(true) as $session){
					$session->getPlayer()->sendMessage(TextFormat::YELLOW."Tiempo restante: ".TextFormat::GRAY.gmdate("i:s", ((60 * 20) - $this->endtime)));
				}
			}
			if($this->endtime % (20 * 60) === 0){
				foreach($this->getPlayers(true) as $session){
					Utils::resetPlayer($session, true, false, false, true);
				}
				$this->setStatus(Arena::RESTARTING);
			}
		}
		if($this->getStatus() === Arena::RESTARTING){
			$this->restart--;
			if($this->restart === 1){
				$this->resetGame();
			}
		}
	}

}

