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

    public function __construct( CommandSender $sender, Command $cmd, string $label, array $args, Festival $plugin){

        $this->plugin = $plugin;

        if( $cmd->getName() == "fc" ) {

            $playerName = strtolower($sender->getName());
            $action = strtolower($args[0]);
            $o = "";
            switch($action){
                case "config": // festival 2
                case "c":
                    $this->plugin->form->openConfig($sender);
                    $o = TextFormat::GREEN . "Config Festival";
                break;
                case "form": // festival 2
                default:
                    $this->plugin->form->openUI($sender);
                    $o = TextFormat::GREEN . "Manage Festival";
                break;

            }
            $sender->sendMessage($o);

        }

    }

}
