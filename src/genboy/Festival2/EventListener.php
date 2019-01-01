<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use genboy\Festival2\Language;
use genboy\Festival2\Level;
use genboy\Festival2\Flag;

class EventListener implements Listener{

    private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

		if(!($sender instanceof Player)){
			$sender->sendMessage('Please run this command in game');
			return true;
		}
		if(!isset($args[0])){
			$sender->sendMessage('Festival2 plugin in development - by Genboy.');
			$sender->sendMessage($command->getUsage());
			return true;
		}

    }

}
