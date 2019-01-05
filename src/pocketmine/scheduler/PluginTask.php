<?php
/**
                                                $$\   $$$$$$\   $$$$$$\  $$$$$$$$\ 
                                              $$$$ | $$ ___$$\ $$ ___$$\ \____$$  |
* $$$$$$\$$$$\   $$$$$$\  $$$$$$$\  $$\   $$\ \_$$ | \_/   $$ |\_/   $$ |    $$  / 
* $$  _$$  _$$\  \____$$\ $$  __$$\ $$ |  $$ |  $$ |   $$$$$ /   $$$$$ /    $$  /  
* $$ / $$ / $$ | $$$$$$$ |$$ |  $$ |$$ |  $$ |  $$ |   \___$$\   \___$$\   $$  /   
* $$ | $$ | $$ |$$  __$$ |$$ |  $$ |$$ |  $$ |  $$ | $$\   $$ |$$\   $$ | $$  /    
* $$ | $$ | $$ |\$$$$$$$ |$$ |  $$ |\$$$$$$$ |$$$$$$\\$$$$$$  |\$$$$$$  |$$  /     
* \__| \__| \__| \_______|\__|  \__| \____$$ |\______|\______/  \______/ \__/      
                                    $$\   $$ |                                     
                                    \$$$$$$  |                                     
                                     \______/  
*/  
declare(strict_types=1);

namespace pocketmine\scheduler;

use pocketmine\plugin\Plugin;

abstract class PluginTask extends Task
{
	/** @var Plugin */
	protected $owner;

	/**
	 * PluginTask constructor.
	 * @param Plugin $owner
	 */
	public function __construct(Plugin $owner)
	{
		$this->owner = $owner;
	}

	/**
	 * @return Plugin
	 */
	final public function getOwner(): Plugin
	{
		return $this->owner;
	}
}
