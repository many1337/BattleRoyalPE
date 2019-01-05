<?php

namespace BattleRoyale\Sessions;

use BattleRoyale\Game\Arena;
use BattleRoyale\GameManager;
use BattleRoyale\BossBar\BossManager;
use BattleRoyale\Utilities\Utils;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class Playing {

	public $kills;
	public $arena;
	public $player;
	public $playtime;
	public $falling;
	public $position;
	public $lasthit;
	public $points;
	public $nametag;
	public $zoom;

	static $custom;

	const HEALTH = 20;
	const FOOD = 20;
	const NAMETAG = "";

	public function __construct(Player $entity, Arena $game){
		$this->player = $entity;
		$this->kills = 0;
		$this->arena = $game;
		$this->position = 0;
		$this->points = 0;
		$this->zoom = false;
		$this->falling = false;
		$this->lasthit = array("", 0);
		$this->nametag = $entity->getNametag();
		$this->playtime = microtime(true);
		self::$custom = false;
		if($entity->getGameMode() !== $entity::ADVENTURE){
			$entity->setGameMode($entity::ADVENTURE);
		}
		$entity->teleport($game->asPosition($game->getSpawn()));
		$entity->removeAllEffects();
		$entity->setMaxHealth(self::HEALTH);
		$entity->setHealth($entity->getMaxHealth());
		$entity->setFood(self::FOOD);
		$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::JUMP), 99999*20, 1, false));
		$entity->getArmorInventory()->clearAll(true);
		$entity->getInventory()->clearAll(true);
		$entity->sendMessage(TextFormat::YELLOW."Te has unido correctamente a esta partida!");
	}

	public function isZoomActivated(): bool{
		return $this->zoom;
	}

	public function sendMessage(string $message): void{
		$this->getPlayer()->sendMessage($message);
	}

	public function isFalling(): bool{
		return $this->falling;
	}

	public function setFalling(bool $value): void{
		$this->falling = $value;
		$player = $this->getPlayer();
		if(!$value){
			$player->setAllowFlight(false);
			$player->getArmorInventory()->setChestplate(Item::get(Item::AIR, 0));
			if($player->getGenericFlag(Player::DATA_FLAG_GLIDING)){
				$player->setGenericFlag(Player::DATA_FLAG_GLIDING, false);
			}
		}
	}

	public function setZoomStatus(bool $value): void{
		$this->zoom = $value;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getKills(): int{
		return $this->kills;
	}

	public function getOriginalNametag(): string{
		return $this->nametag;
	}
	
	public function getZoneStatus(): string{
		$player = $this->getPlayer();
		if($this->getArena()->getStorm()->isInside($player)){
			return TextFormat::GREEN."Salvo";
		}else{
			$damage = 1;
			if($this->getArena()->getStorm()->getStatus() === 1){
				$damage = 2;
			}
			if(!$this->isFalling()){
				$event = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, $damage);
				$player->attack($event);
				GameManager::getInstance()->getServer()->getPluginManager()->callEvent($event);
			}
			return TextFormat::RED."En peligro";
		}
	}

	public function getArena(): Arena{
		return $this->arena;
	}

	public function addZoom(): void{
		$this->setZoomStatus(true);
		$this->getPlayer()->getArmorInventory()->setHelmet(Item::get(86, 0));
		$this->getPlayer()->addEffect(new EffectInstance(Effect::getEffect(2), 20*99, 5, false));
	}

	public function removeZoom(): void{
		$this->setZoomStatus(false);
		$this->getPlayer()->getArmorInventory()->setHelmet(Item::get(Item::AIR, 0));
		if($this->getPlayer()->hasEffect(2)){
			$this->getPlayer()->removeEffect(2);
		}
	}

	public function getSessionTime(): string{
		return gmdate("i:s", round(microtime(true) - $this->playtime, 3));
	}

	public function getPosition(): int{
		return $this->position;
	}

	public function setPosition(int $position): void{
		$this->position = $position;
	}

	public function startGame(): void{
		$this->setFalling(true);
		$player = $this->getPlayer();
		$player->setNameTag(self::NAMETAG);
		$player->setGameMode($player::SURVIVAL);
		$player->setAllowFlight(true);
		if(!$player->getGenericFlag(Player::DATA_FLAG_GLIDING)){
			$player->setGenericFlag(Player::DATA_FLAG_GLIDING, true);
			$player->getArmorInventory()->setChestplate(Item::get(444, 0, 1));
		}
		$player->getInventory()->setItem(0, Item::get(Item::SANDSTONE, 0, 64));
		$player->getInventory()->setItem(1, Item::get(Item::COMPASS, 0, 1)->setCustomName("Centro"));
		$player->getInventory()->setItem(2, Item::get(Item::COMPASS, 1, 1)->setCustomName("Buscador de AirDrops"));
		$player->getInventory()->setItem(3, Item::get(Item::STONE_AXE, 0, 1)->setCustomName("Acha de piedra"));
		$player->getInventory()->setItem(4, Item::get(Item::STONE_PICKAXE, 0, 1)->setCustomName("Pico de piedra"));
		$player->getInventory()->setItem(5, Item::get(Item::FEATHER, 0, 6)->setCustomName("Launcher"));
		$player->getInventory()->sendContents($player);
	}

	public function getLastHit(): string{
		if(round(microtime(true) - $this->lasthit[1], 3) <= 5 and $this->lasthit[0] !== ""){
			return $this->lasthit[0];
		}else{
			$this->lasthit = array("", microtime(true));
			return "";
		}
	}

	public function setLastHit(string $damager): void{
		if(!is_null(Utils::getPlayer($damager))){
			$this->lasthit = array($damager, microtime(true));
		}
	}

	public function addKill(): void{
		$this->kills += 1;
	}

	public function addRankingPoints(int $value): void{
		if(!($value >= GameManager::getInstance()->getConfig()->get("max.points", 100))){
			$this->points += $value;
		}
	}

	public function getRankingPoints(): int{
		return $this->points;
	}

	public function deleteSession(): void{
		$player = $this->getPlayer();
		if($this->isFalling()){
			$this->setFalling(false);
		}
		if($player->getGenericFlag(Player::DATA_FLAG_GLIDING)){
			$player->setGenericFlag(Player::DATA_FLAG_GLIDING, false);
		}
		BossManager::removeWindow($player);
		$player->removeAllEffects();
		$player->setHealth($player->getMaxHealth());
		$player->setFood(self::FOOD);
		$player->setXpLevel(0);
		$player->setNameTag($this->getOriginalNametag());
		$player->getArmorInventory()->clearAll(true);
		$player->getInventory()->clearAll(true);
		$player->teleport(GameManager::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		unset(GameManager::$players[$player->getName()]);
	}

	public function calculateStats(): array{
		$calculate["Tu posicion"] = $this->getPosition();
		$calculate["Matanzas en total"] = $this->getKills();
		$calculate["Porcentaje de matanzas"] = number_format(($this->getKills() / ($this->getArena()->getMaxPlayers() - 1)) * 100, 2)." / 100";
		$calculate["Tiempo en juego"] = $this->getSessionTime();
		$calculate["Puntos ganados"] = $this->getRankingPoints();
		return $calculate;
	}

}