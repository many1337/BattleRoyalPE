<?php 

namespace BattleRoyale\Items;

use pocketmine\item\Item;
use BattleRoyale\AirDrop\BoxEntity;
use BattleRoyale\Sessions\Playing;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;

class RoyalCompass extends Item {

	static $collected = array();

	public function __construct($meta = 0, $count = 1){
		parent::__construct(345, $meta, "Royale Compass");
	}

	public function getMaxStackSize(): int{
		return 1;
	}

	private function isCollected(int $id): bool{
		return in_array($id, RoyalCompass::$collected);
	}

	private function addCollected(int $id){
		RoyalCompass::$collected[] = $id;
	}

	private function setRoyaleSpawn(Player $player, $x, $z){
		$packet = new SetSpawnPositionPacket();
		$packet->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
		$packet->spawnForced = true;
		$packet->x = $x;
		$packet->y = 0;
		$packet->z = $z;
		$player->dataPacket($packet);
	}

	public function getAction(Playing $session){
		$player = $session->getPlayer();
		if($this->getDamage() === 1){
			$values = array();
			$player->sendMessage(TextFormat::YELLOW."> Buscando la caja mas cercana a ti...");
			foreach($player->getLevel()->getEntities() as $airdrop){
				if($airdrop instanceof BoxEntity){
					if(!$this->isCollected($airdrop->getId())){
						$values[$airdrop->getX().":".$airdrop->getZ().":".$airdrop->getId()] = round($player->distance($airdrop));
					}
				}
			}
			if(!empty($values)){
				$target = explode(":", array_search(min(array_values($values)), $values));
				unset($values);
				$this->addCollected(intval($target[2]));
				$this->setRoyaleSpawn($player, (int) $target[0], (int) $target[1]);
				$player->sendMessage(TextFormat::GREEN."Se ha encontrado una caja cerca de ti, sigue tu compass!");
			}else{
				$player->sendMessage(TextFormat::RED."No se ha encontrado ninguna caja.");
			}
		}else{
			if($this->getDamage() === 0){
				$player->sendPopup(TextFormat::YELLOW."Dirigiendo al centro");
				$vector = $session->getArena()->getStorm()->getVector3();
				$this->setRoyaleSpawn($player, $vector->getX(), $vector->getZ());
			}
		}
	}

}