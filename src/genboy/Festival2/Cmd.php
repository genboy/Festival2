<?php declare(strict_types = 1);
/** src/genboy/Festival2/Cmd.php
 *
 * command helper
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Cmd{

    private $plugin;

    /**  __construct
	 * @param CommandSender $sender
	 * @param Command $cmd
	 * @param string $label
	 * @param array $args
	 * @param Festival $plugin
     */
    public function __construct( CommandSender $sender, Command $cmd, string $label, array $args, Festival $plugin){

        $this->plugin = $plugin;

        if( $cmd->getName() == "fc" ) {
            $playerName = strtolower($sender->getName());
            $action = strtolower($args[0]);
            $o = "";

            switch($action){

                case "form": // festival 2
                default:
                    $this->getUIForm();
                break;

            }
            //$o = TextFormat::GREEN . "Manage Festival";
            //$sender->sendMessage($o);
        }
    }

    /** getUIForm
	 * @class FormUI
	 * @func FormUI->openUI
     */
    public function getUIForm(){
        $this->plugin->form->openUI($sender);
    }

}
