<?php

namespace BattleRoyale\Listener;

use BattleRoyale\GameManager;
use BattleRoyale\Guns\GunClass;
use BattleRoyale\Utilities\Utils;
use BattleRoyale\BossBar\BossManager;
use BattleRoyale\Items\RoyalCompass;
use BattleRoyale\Items\Granate;
use BattleRoyale\Items\Launcher;
use BattleRoyale\Items\RoyaleEgg;
use BattleRoyale\Items\Fireball;
use BattleRoyale\Ammo\RoyalAmmo;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;


class BattleRoyaleEvents implements Listener {

	private $directions = array(
		22 => "N",
		67 => "NE",
		112 => "E",
		157 => "SE",
		202 => "S",
		247 => "SW",
		292 => "W",
		337 => "NW",
		359 => "N"
	);

	public function __construct(GameManager $plugin){
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function onMove(PlayerMoveEvent $event){ //...hmmm
		$player = $event->getPlayer();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if($player->isFlying()){
				$player->setFlying(false);
			}
			if($session->isFalling() and $player->isCollided){
				$session->setFalling(false);
			}
			$arena = $session->getArena();
			if($arena->getStatus() === $arena::RUNNING and $arena->isDirectionActivated()){
			    $toSend = "";
			    $direction = ($player->yaw + 270) % 360;
			    for($i = $direction - 25; $i < $direction + 25; ++$i){
			    	if(array_key_exists($i, $this->directions)){
			    		$toSend .= TextFormat::GREEN.$this->directions[$i].TextFormat::RESET;
			    	}else{
			    		$toSend .= " | ";
			    	}
			    }
				BossManager::setString($player, substr($toSend, 0, 50));
			}
		}
	}

	public function changeGamemode(PlayerGameModeChangeEvent $event){
		$player = $event->getPlayer();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if($event->getNewGamemode() === Player::CREATIVE || $event->getNewGamemode() === Player::SPECTATOR){
				$player->sendMessage(TextFormat::RED."Este modo de juego no esta disponible, has sido expulsado!");
				Utils::resetPlayer($session, true);
			}
		}
	}

	public function onDamage(EntityDamageEvent $event){
		$player = $event->getEntity();
		if($player instanceof Player){
			if(!is_null($session = Utils::getPlayer($player->getName()))){
				if($session->getArena()->getStatus() !== $session->getArena()::RUNNING){
					$event->setCancelled();
					if($event->getCause() === EntityDamageEvent::CAUSE_VOID){
						$player->teleport($session->getArena()->asPosition($session->getArena()->getSpawn()));
					}
					return;
				}
				$headshot = false;
				$guncause = false;
				if($event instanceof EntityDamageByChildEntityEvent){
					$child = $event->getChild();
					$damager = $child->getOwningEntity();
					if($child instanceof RoyalAmmo){
						$guncause = true;
						if(!is_null(Utils::getPlayer($damager->getName()))){
							$session->setLastHit($damager->getName());
						}
						if($child->getY() >= ($player->getY() + $player->getEyeHeight())){
							$headshot = true;
							$event->setDamage($event->getDamage() + ($child->getDamageValue() * 2.5));
							$damager->sendMessage(TextFormat::YELLOW."> Headshot");
						}else{
							$event->setDamage($event->getDamage() + $child->getDamageValue());
						}
					}else{
						if(!is_null($damager)){
							$damager->sendPopup(TextFormat::GREEN."Has acertado!");
						}
					}
				}
				if($event instanceof EntityDamageByEntityEvent){
					if($guncause){
						$guncause = false;
					}
					if($headshot){
						$headshot = false;
					}
					$damager = $event->getDamager();
					if($damager instanceof Player){
						if(!is_null(Utils::getPlayer($damager->getName()))){
							$session->setLastHit($damager->getName());
						}else{
							$event->setCancelled();
						}
					}
				}
				if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
					if($session->isFalling()){
						$event->setCancelled();
					}
				}
				if($event->getCause() === EntityDamageEvent::CAUSE_VOID and $session->isFalling()){
					$event->setCancelled();
					$player->sendMessage(TextFormat::RED."Has caido al vacio, suerte en la proxima!");
					Utils::resetPlayer($session, true);
				}
				if($event->getDamage() >= $player->getHealth()){
					if($session->isFalling() and $event->getCause() !== EntityDamageEvent::CAUSE_MAGIC){
						return;
					}
					$event->setCancelled();
					$killer = false;
					if($session->getLastHit() !== ""){
						$killer = $session->getLastHit();
					}
					if(is_string($killer)){
						$kdata = Utils::getPlayer($killer);
						if(!is_null($kdata)){
							$kdata->addKill();
							$kdata->getPlayer()->sendMessage(TextFormat::GREEN."Has matado ha: ".TextFormat::RED.$player->getName());
							$player->sendMessage(TextFormat::RED."Has sido eliminado por: ".TextFormat::YELLOW.$killer);
						}
						foreach($session->getArena()->getPlayers(true) as $class){
							$class->sendMessage(TextFormat::YELLOW.$killer.TextFormat::GRAY." ha matado a ".TextFormat::RED.$player->getName());
						}
						unset($kdata, $killer);
					}
					foreach($session->getArena()->getPlayers(true) as $class){
						$class->sendMessage(TextFormat::RED.$player->getName().TextFormat::YELLOW." ha sido eliminado!");
					}
					Utils::resetPlayer($session, true, true);
				}
			}
		}
	}

	public function onQuit(PlayerQuitEvent $event){
		if(!is_null($session = Utils::getPlayer($event->getPlayer()->getName()))){
			Utils::resetPlayer($session, true);
		}
	}

	public function changeLevel(EntityLevelChangeEvent $event){
		$player = $event->getEntity();
		if($player instanceof Player){
			if(!is_null($session = Utils::getPlayer($player->getName()))){
				if(!$session::$custom){
					if($event->getTarget() !== $session->getArena()->getLevel()){
						Utils::resetPlayer($session, true);
				    }
				}
			}
		}
	}

	public function onKick(PlayerKickEvent $event){
		if(!is_null($session = Utils::getPlayer($event->getPlayer()->getName()))){
			Utils::resetPlayer($session, true);
		}
	}

	public function onHeld(PlayerItemHeldEvent $event){
		$player = $event->getPlayer();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if(($item = $event->getItem()) instanceof GunClass){
				$player->setXpLevel($item->getAmmo());
				if($item->canZoom() and $player->isSneaking()){
					$session->addZoom();
				}
			}else{
				if($player->getXpLevel() > 0){
					$player->setXpLevel(0);
				}
				if($session->isZoomActivated()){
					$session->removeZoom();
				}
			}
		}
	}

	public function onChat(PlayerChatEvent $event){
		if($event->isCancelled()){
			return;
		}
		$player = $event->getPlayer();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			$event->setFormat(TextFormat::GREEN.$player->getName()." > ".TextFormat::WHITE.$event->getMessage());
		}else if(!is_null($creator = Utils::isCreating($player->getName()))){
			$event->setCancelled();
			$args = explode(" ", $event->getMessage());
			if(empty($args)){
				return;
			}else{
				switch(strtolower($args[0])){

					case "level":
					case "mundo":
					$level = null;
					$server = GameManager::getInstance()->getServer();
					if(isset($args[1])){
						if(!$server->isLevelLoaded($args[1])){
							$server->loadLevel($args[1]);
							if(!is_null(($mundo = $server->getLevelByName($args[1])))){
								$level = $mundo;
								$player->teleport($level->getSafeSpawn());
							}else{
								$player->sendMessage(TextFormat::RED."Este mundo no existe!");
								return;
							}
						}
					}else{
						$level = $player->getLevel();
					}
					if(is_null($level)){
						return;
					}
					foreach(array_values(GameManager::getInstance()->arenas) as $class){
						if($level->getFolderName() === $class->getLevel()->getFolderName()){
							break;
							$player->sendMessage(TextFormat::RED."Estas en un mundo ya en uso!");
							return;
						}
					}
					if($level === $server->getDefaultLevel()){
						$player->sendMessage(TextFormat::RED."Debes seleccionar un mundo diferente...");
						return;
					}
					$creator->setLevel($level->getFolderName());
					$player->sendMessage(TextFormat::GREEN."Has seleccionado el mundo correctamente!");
					break;

					case "max":
					case "maximo";
					if(!isset($args[1])){
						$player->sendMessage(TextFormat::RED."Debes introducir el numero maximo de jugadores... uso: ".TextFormat::GREEN."max (maximo) <cantidad>");
					}else{
						if(!is_numeric($args[1])){
							$player->sendMessage(TextFormat::RED."El valor no es numerico...");
						}else{
							if($args[1] < 2){
								$player->sendMessage(TextFormat::RED."El valor debe ser mayor o igual a 2");
							}else{
								$creator->setMax($args[1]);
								$player->sendMessage(TextFormat::GREEN."Has seleccionado el numero maximo de jugadores correctamente!");
							}
						}
					}
					break;

					case "radius":
					case "radio";
					if(is_null($creator->options["level"]) || is_null($creator->options["center"])){
						$player->sendMessage(TextFormat::RED."Debes intrucir primero el mundo y el centro de la partida!");
					}else{
						$radius = floor($player->distance(Utils::getVector($creator->options["center"])));
						if($radius < 150){
							$player->sendMessage(TextFormat::RED."La distancia debe ser mayor o igual a 150, distancia introducida: $radius");
						}else{
							$creator->setRadius($radius);
							$player->sendMessage(TextFormat::GREEN."Has intrucido el radio de la tormenta para esta partida correctamente!");
						}
					}
					break;

					case "center":
					case "centro":
					if(is_null($creator->options["level"])){
						$player->sendMessage(TextFormat::RED."Debes selecconar el mundo primero...");
					}else{
						$creator->setCenter($player->getFloorX().":".$player->getY().":".$player->getFloorZ());
						$player->sendMessage(TextFormat::GREEN."Has seleccionado el centro correctamente!");
					}
					break;

					case "lobby":
					case "sala": //de espera xD
					if(is_null($creator->options["level"])){
						$player->sendMessage(TextFormat::RED."Debes seleccionar el nombre del mundo primero...");
					}else{
						$creator->setLobby($player->getFloorX().":".$player->getY().":".$player->getFloorZ());
						$player->sendMessage(TextFormat::GREEN."Has selecionado el lobby correctamente!");
					}
					break;

					case "options":
					case "opciones":
					foreach($creator->options as $key => $value){
						if(is_array($value)){
							$value = empty($value) ? "empty" : implode(", ", $value);
						}
						if(is_null($value)){
							$value = "not set";
						}
						$player->sendMessage(TextFormat::YELLOW.$key." > ".TextFormat::WHITE.$value);
					}
					break;

					case "finish":
					case "terminar":
					$creator->finishArena();
					break;

					case "leave":
					case "salir":
					$player->sendMessage(TextFormat::GRAY."Abandonando el modo creator...");
					unset(GameManager::$creators[$player->getName()]);
					break;

					default:
					$player->sendMessage(TextFormat::RED."Argumento desconocido, usa: ".TextFormat::GREEN."level (mundo), lobby (sala), finish (terminar), leave (salir), max (maximo) <number>, options (opciones), center (centro), radius (radio)");
					break; //...
				}
			}
		}else{
			//Hola, como estas?
		}
	}

	public function onDrop(PlayerDropItemEvent $event){
		if($event->isCancelled()){
			return;
		}
		if(!is_null($session = Utils::getPlayer($event->getPlayer()->getName()))){
			if($session->getArena()->getStatus() !== $session->getArena()::RUNNING || $session->isFalling()){
				$event->setCancelled();
			}
		}
	}

	public function onExahust(PlayerExhaustEvent $event){
		if($event->isCancelled()){
			return;
		}
		if(!is_null($session = Utils::getPlayer($event->getPlayer()->getName()))){
			if($session->getArena()->getStatus() !== $session->getArena()::RUNNING){
				$event->setCancelled();
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$player = $event->getPlayer();
			$session = Utils::getPlayer($player->getName());
			if(($item = $event->getItem()) instanceof GunClass){
				if(!is_null($session)){
					$item->useGun($player);
				}
			}
			if($item instanceof Launcher){
				if(!is_null($session)){
					$item->launchPlayer($player, $session->isFalling());
				}
			}
			if($item instanceof Fireball){
				$item->onClickAir($player, $player->getDirectionVector());
			}
			if($item instanceof RoyalCompass){
				if(!is_null($session)){
					$item->getAction($session);
				}
			}
			if($item->getId() === Block::WOODEN_PLANKS || $item->getId() === Block::COBBLESTONE){
				if(is_null($session)){
					return;
				}
				$block = $player->getLevel()->getBlock($player->add($player->getDirectionVector()->multiply(3.5)));
				if(($block->canBeFlowedInto())){
					$this->build($block, false, $player->pitch, $player->getDirection(), $item->getId());
					$player->getInventory()->removeItem(Item::get($item->getId(), $item->getDamage(), 1));
				}
			}
		}
	}

	public function onSneak(PlayerToggleSneakEvent $event){
		if($event->isCancelled()){
			return;
		}
		$player = $event->getPlayer();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if(($item = $player->getInventory()->getItemInHand()) instanceof GunClass){
				if($item->canZoom()){
					if($event->isSneaking()){
						$session->addZoom();
					}else{
						$session->removeZoom();
					}
				}
			}
		}
	}

	public function onBreak(BlockBreakEvent $event){
		if($event->isCancelled()){
			return;
		}
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if($session->getArena()->getStatus() !== $session->getArena()::RUNNING){
				$event->setCancelled();
			}else{
				if(!in_array($block->getId(), array(1, 4, 5, 6, 17, 18, 24, 31, 32, 37, 38, 39, 40, 110, 111, 161, 162, 175))){
					$event->setCancelled();
				}else{
					if($block->getId() === 17 || $block->getId() === 162){
						$event->setDrops(array(Item::get(Block::WOODEN_PLANKS, 0, mt_rand(1, 2))->setCustomName("Madera para construccion")));
					}
					if($block->getId() === 1){
						$event->setDrops(array(Item::get(Block::COBBLESTONE, 0, mt_rand(1, 2))->setCustomName("Piedra para construccion")));
					}
					if($block->getId() === Block::WOODEN_PLANKS || $block->getId() === Block::COBBLESTONE){
						$event->setDrops(array(Item::get(Item::AIR, 0)));
					}
				}
			}
		}else{
			if(!is_null($session = Utils::isCreating($player->getName()))){
				if(is_null($session::$options["level"])){
					return;
				}
				if($block->getId() === Block::CHEST){
					$session->removeChest($block->getX().":".$block->getY().":".$block->getZ());
				}
			}
		}
	}

	public function build(Block $block, bool $sneaking, int $pitch, int $direction, int $custom = 0){
		$blockid = $block->getId();
		if($custom > 0){
			$blockid = $custom;
		}
		$fx = $block->getX();
		$fz = $block->getZ();
		if(($pitch >= 55 || $pitch <= -25) and !$sneaking){
			for($x = -1; $x <= 1; ++$x){
				for($z = 1; $z >= -1; --$z){
					$xx = $fx + $x;
					$zz = $fz + $z;
					$target = $block->level->getBlock(new Vector3($xx, $block->getY(), $zz));
					if($target->canBeFlowedInto()){
						$block->level->setBlockIdAt($xx, $block->getY(), $zz, $blockid);
					}
				}
			}
		}else{
			if($sneaking){
				$time = 0;
				for($y = 0; $y <= 2; ++$y){
					switch($direction){
						case 0:
					    $fx = $block->getX() + $time;
					    break;
					    case 1:
					    $fz = $block->getZ() + $time;
					    break;
					    case 2:
					    $fx = $block->getX() - $time;
					    break;
					    case 3:
					    $fz = $block->getZ() - $time;
					    break;
					}
				    $target = $block->level->getBlock(new Vector3($fx, $block->getY() + $y, $fz));
				    if($target->canBeFlowedInto()){
					    $block->level->setBlockIdAt($fx, $block->getY() + $y, $fz, $blockid);
				    }
				    $time++;
			    }
			}else{
				for($x = -1; $x <= 1; ++$x){
					for($y = 0; $y <= 2; ++$y){
						if($direction === 0 || $direction === 2){
							$fz = $block->getZ() + $x;
						}else{
							$fx = $block->getX() + $x;
						}
						$target = $block->level->getBlock(new Vector3($fx, $block->getY() + $y, $fz));
						if($target->canBeFlowedInto()){
							$block->level->setBlockIdAt($fx, $block->getY() + $y, $fz, $blockid);
						}
					}
				}
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		if($event->isCancelled()){
			return;
		}
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!is_null($session = Utils::getPlayer($player->getName()))){
			if($session->getArena()->getStatus() !== $session->getArena()::RUNNING){
				$event->setCancelled();
			}else{
				if(in_array($block->getId(), array(Block::WOODEN_PLANKS, Block::COBBLESTONE))){
					$this->build($block, $player->isSneaking(), $player->pitch, $player->getDirection());
					$player->getInventory()->sendContents($player);
				}else{
					if($block->getId() !== Block::SANDSTONE){
						$event->setCancelled();
					}
				}
			}
		}else{
			if(!is_null($session = Utils::isCreating($player->getName()))){
				if(is_null($session::$options["level"])){
					return;
				}
				if($block->getId() === Block::CHEST){
					$session->addChest($block->getX().":".$block->getY().":".$block->getZ());
				}
			}
		}
	}

}
