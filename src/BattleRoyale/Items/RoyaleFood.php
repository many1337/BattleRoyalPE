<?php

namespace BattleRoyale\Items;

use pocketmine\item\Food;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\Player;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\network\mcpe\protocol\EntityEventPacket;

class RoyaleFood extends Food {

	public function requiresHunger(): bool{
		return false;
	}

	public function canBeConsumedBy(Entity $entity): bool{
		return $entity instanceof Player;
	}

	public function getFoodRestore(): int{
		return 0.0;
	}

	public function getSaturationRestore(): float{
		return 0.0;
	}

	public function onConsume(Living $player){
		$packet = new EntityEventPacket();
		$packet->entityRuntimeId = $player->getId();
		$packet->event = EntityEventPacket::USE_ITEM;
		$player->dataPacket($packet);
		$player->getLevel()->getServer()->broadcastPacket($player->getViewers(), $packet);
		unset($packet);
		$player->getLevel()->getServer()->getPluginManager()->callEvent(new PlayerItemConsumeEvent($player, $this));
		$player->getInventory()->setItemInHand($this->getResidue());
	}

}