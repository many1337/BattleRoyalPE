<?php  

namespace BattleRoyale\AirDrop;

use pocketmine\Player;
use BattleRoyale\Utilities\Utils;
use BattleRoyale\Game\ChestItems;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use BattleRoyale\EntityManager;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;

class BoxEntity extends EntityManager {

  const NETWORK_ID = 71;
  
  public $height = 1;
  public $lenght = 1;
  public $weight = 1;
  public $width = 1;
  
  private $contents = array();

  protected function initEntity(CompoundTag $nbt) : void{
    ChestItems::fillAirDrop($this);
    parent::initEntity($nbt);
  }

  public function setInventory(array $contents): void{
  	$this->contents = $contents;
  }

  public function getContents(): array{
  	return $this->contents;
  }

  public function getName(): string{
    return ">> Air Drop <<";
  }

  public function attack(EntityDamageEvent $source): void{
    parent::attack($source);
    if($source instanceof EntityDamageByEntityEvent){
    	$player = $source->getDamager();
    	if($player instanceof Player){
    		if(!is_null(Utils::getPlayer($player->getName()))){
    			AirDropManager::addAirDrop($player, $this->getContents(), $this->getId());
    		}
    	}
    }
  }
  
}