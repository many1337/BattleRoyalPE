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

class CallbackTask extends Task
{
	/** @var callable */
	protected $callable;
	/** @var array */
	protected $args;

	/**
	 * CallbackTask constructor.
	 * @param callable $callable
	 * @param array $args
	 */
	public function __construct(callable $callable, array $args = [])
	{
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}

	/**
	 * @return callable
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * @param int $currentTicks
	 */
	public function onRun($currentTicks)
	{
		call_user_func_array($this->callable, $this->args);
	}
}
