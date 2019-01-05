<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;


use genboy\Festival2\Festival;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent; // use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class EventListener implements Listener{

    /** @var Festival */
    private $plugin;

	public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

	}

	public function onJoin(PlayerJoinEvent $event) {
		$event->getPlayer()->sendMessage("This is an example event!"); # Sends the message to the player that joined the server
	}

}
