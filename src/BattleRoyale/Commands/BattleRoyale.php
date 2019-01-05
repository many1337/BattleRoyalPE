<?php

namespace BattleRoyale\Commands;

use BattleRoyale\GameManager;
use BattleRoyale\Sessions\Playing;
use BattleRoyale\Utilities\Utils;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\PLayer;

class BattleRoyale extends PluginCommand {

	public function __construct(GameManager $plugin){
		parent::__construct("battleroyale", $plugin);
		$this->setDescription("Comandos para ver/entrar a la partida");
		$this->setAliases(array("br", "royale", "battle"));
	}

	public function getCommand(): string{
		return "/battleroyale";
	}

	public function execute(CommandSender $sender, string $label, array $args): bool{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED."No puedes utilizar este comando en la consola!");
			return false;
		}
		if(!is_null(Utils::isCreating($sender->getName()))){
			$sender->sendMessage(TextFormat::GOLD."No puedes ver/entrar a una partida en el modo creador, para salir usa ' leave (salir) '");
			return false;
		}
		switch(strtolower($args[0])){

			case "join":
			case "entrar":
			if(is_null(Utils::getPlayer($sender->getName()))){
				if(isset($args[1])){
					if(array_key_exists($args[1], GameManager::getInstance()->arenas)){
						$game = GameManager::getInstance()->arenas[$args[1]];
						if($game->isAvailable()){
							$game->addPlayer($session = new Playing($sender, $game));
							GameManager::$players[$sender->getName()] = $session;
						}else{
							$sender->sendMessage(TextFormat::RED."No puedes entrar a este juego...");
						}
					}else{
						$sender->sendMessage(TextFormat::RED."Esta partida no existe...");
					}
				}else{
					$sender->sendMessage(TextFormat::YELLOW."Debes introducir el nombre de la partida");
				}
			}else{
				$sender->sendMessage(TextFormat::YELLOW."Ya estas en una partida, puedes usar /battleroyale leave (salir) para salir de esta partida!");
			}
			break;

			case "leave":
			case "salir":
			if(!is_null($session = Utils::getPlayer($sender->getName()))){
				$sender->sendMessage(TextFormat::GREEN."Abandonando esta partida, por favor espera...");
				Utils::resetPlayer($session, true);
			}else{
				$sender->sendMessage(TextFormat::RED."No estas en ninguna partida... XD");
			}
			break;

			case "status";
			case "estado":
			if(isset($args[1])){
				if(array_key_exists($args[1], GameManager::getInstance()->arenas)){
					$game = GameManager::getInstance()->arenas[$args[1]];
					$sender->sendMessage(
						TextFormat::YELLOW."Juego: ".TextFormat::GRAY.$game->getName()."\n".
						TextFormat::YELLOW."Jugadores: ".TextFormat::GREEN.$game->getCount()."\n".
						TextFormat::YELLOW."Puestos disponibles: ".TextFormat::WHITE.($game->getMaxPlayers() - $game->getCount())."\n".
						TextFormat::YELLOW."Maximo de jugadores: ".TextFormat::GRAY.$game->getMaxPlayers()."\n".
						TextFormat::YELLOW."Lista de jugadores: ".TextFormat::RED.implode("\n", array_keys($game->getPlayers(false)))
					);
				}else{
					$sender->sendMessage(TextFormat::GOLD."Este juego no existe!");
				}
			}else{
				$sender->sendMessage(TextFormat::RED."Debes seleccionar una partida!");
			}
			break;

			case "list":
			case "lista":
			$free = array();
			foreach(GameManager::getInstance()->arenas as $name => $class){
				if($class->isAvailable()){
					$free[] = $name;
				}
			}
			$sender->sendMessage(TextFormat::GREEN."Juegos disponibles: ".TextFormat::WHITE.implode(", ", $free));
			$sender->sendMessage(TextFormat::YELLOW."Juegos en total: ".TextFormat::GRAY.implode(", ", array_keys(GameManager::getInstance()->arenas)));
			break;

			case "information":
			case "informacion":
			$sender->sendMessage(TextFormat::YELLOW."Author: ".TextFormat::AQUA."@BEcraft_MCPE");
			break;

			default:
			$sender->sendMessage(TextFormat::GRAY."Comandos: ".TextFormat::YELLOW."join <arena>, list, status <arena>");
			break;

		}
		return true;
	}

}