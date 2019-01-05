<?php declare(strict_types = 1);
/**
 * src/genboy/Festival/Festival.php
 *
 * Main class plugin
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Setup;
use genboy\Festival2\Core;
use genboy\Festival2\FormUI;
use genboy\Festival2\EventListener;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class Festival extends PluginBase implements Listener {

    // obj for status control
    public  $setup;

    // obj for configurations
    public  $core;

    // obj for data control
    public  $helper;

    // obj for listener
    public  $listener;

    // obj for data storage
    public  $data;

    // obj for Forms
    public  $form;

    // obj for application control
    public  $api;

    public function onLoad() : void {

	}

	public function onEnable() : void {

        //$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->helper = new Helper($this); // $this->plugin->data load

        $this->listener = new EventListener($this); // $this->plugin->data load

        $this->setup = new Setup($this);

        $this->form = new FormUI($this);

        $this->core = new Core($this);

        $this->getLogger()->info( "Festival 2 (in development) enabled & ready" );

    }

    public function onDisable() : void {

        $this->getLogger()->info( "Festival 2 disabled" );

	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{

        if(!($sender instanceof Player)){

            $sender->sendMessage(TextFormat::RED . "Festival Board Command must be used in-game.");
			return true;

		}

		if( $cmd->getName() == "festival" || $cmd->getName() == "fc" ) {

            if( $sender->hasPermission("festival2.access" ) ){

                $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");

                if ($plug === null || $plug->isDisabled() ) {

                    $sender->sendMessage("Festival Board needs Festival plugin (https://github.com/genboy/Festival)");
                    return true;

                }else{

                    //$sender->sendMessage("This is an Festival Board example"); # Sends to the sender
                    $this->form->selectForm($sender);
                    return true;

                }

            }else{

                $sender->sendMessage("No permission to use this!"); # Sends to the sender

            }

		}
	}

}
