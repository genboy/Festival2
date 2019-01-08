<?php declare(strict_types = 1);
/** src/genboy/Festival/Session.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerItemHeldEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;


class Sessions implements Listener{

    /** @var Festival */
    private $plugin;

    /** @arr players */
    public $players = [];

	public function __construct(Festival $plugin){

        $this->plugin = $plugin;

	}

    /**
     * @param PlayerJoinEvent $event
     */
	public function onJoin(PlayerJoinEvent $event) {

		$event->getPlayer()->sendMessage("Testing Festival 2 Remake here!");

        $this->players[ strtolower( $event->getPlayer()->getName() ) ] = ["name"=>$event->getPlayer()->getName(),"areas"=>[]];

	}

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void {

        unset( $this->players[ strtolower( $event->getPlayer()->getName() ) ] );

    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event): void {
    }

    public function onHold(PlayerItemHeldEvent $event): void { //onItemHeld

        if ($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $cdata = $this->plugin->data->getConfig();

        //$player->sendMessage("item ". $event->getItem()->getID() . " = " . $event->getItem()->getName() .")" );

        if( $event->getItem()->getID() ==  $cdata['options']['itemid'] ){  // == Item::BOW

            //$player->sendMessage("Festival Management item ". $event->getItem()->getName() .")" );

            $this->plugin->form->openUI($player);

        }

    }


}
