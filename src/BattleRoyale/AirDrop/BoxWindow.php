<?php  

namespace BattleRoyale\AirDrop;

use pocketmine\Player;
use pocketmine\inventory\ChestInventory;

class BoxWindow extends ChestInventory {

	public function __construct(BoxChest $chest){
		parent::__construct($chest);
	}

	public function onClose(Player $who): void{
		parent::onClose($who);
		AirDropManager::updateInventory($this->holder->getEntity(), $this->getContents(), $who->getLevel());
		$this->holder->sendReplacement($who);
		$this->holder->close();
	}

	public function onOpen(Player $who): void{
		parent::onOpen($who);
		$this->setContents($this->holder->getItemsData());
	}

}