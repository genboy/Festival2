<?php declare(strict_types = 1);

/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;


use genboy\Festival2\Festival;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class EventListener implements Listener{

    private $plugin;

	public function __construct(Festival $plugin){

		$this->plugin = $plugin;

        $this->plugin->getLogger()->info( "Festival EventListener loaded" );

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
