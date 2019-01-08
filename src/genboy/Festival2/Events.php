<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;


class Events implements Listener{

    /** @var Festival */
    private $plugin;

	public function __construct(Festival $plugin){

        $this->plugin = $plugin;

	}

	public function onJoin(PlayerJoinEvent $event) {

		$event->getPlayer()->sendMessage("Testing Festival 2 Remake here!");

	}

}
