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

                case "pos1":
                    if( $sender->hasPermission("festival") || $sender->hasPermission("festival.acces") || $sender->hasPermission("festival2.access") ){
                        if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] ) ){
                            $type = $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"];
                            if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["pos1"] ) ){
                                $o = TextFormat::RED . "Allready selected first position!"; //$o = TextFormat::RED . "You're already selecting a position!";
                            }else{
                                if( $type == "cube"){
                                    $o = TextFormat::GREEN . "Tab position 1 for new ". $type ." area (right mouse block place)"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                                }else if( $type == "sphere" ){
                                    $o = TextFormat::GREEN . "Tab the center position for the new ". $type ." area (right mouse block place)"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                                }
                            }
                        }
                    }else{
                        $o = TextFormat::RED .  "no permission to do that"; //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
                    }
                    $sender->sendMessage($o);
                break;

                case "pos2":
                    if( $sender->hasPermission("festival") || $sender->hasPermission("festival.acces") || $sender->hasPermission("festival2.access") ){
                        if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] ) ){
                            $type = $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"];
                            if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["radius"] ) && $type == "sphere"){
                                $o = TextFormat::RED . "Allready selected radius! (distance to center position)"; //$o = TextFormat::RED . "You're already selecting a position!";
                            }else if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["pos2"] ) && $type == "cube"){
                                $o = TextFormat::RED . "Allready selected second position!"; //$o = TextFormat::RED . "You're already selecting a position!";
                            }else if( $type == "cube"){
                                $o = TextFormat::GREEN . "Tab position 2 for new ". $type ." area (diagonal end)"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                            }else if( $type == "sphere" ){
                                $o = TextFormat::GREEN . "Tab distance position to set radius for new ". $type ." area center"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                            }
                        }
                    }else{
                        $o = TextFormat::RED .  "no permission to do that"; //$o = TextFormat::RED . "You do not have permission to use this subcommand.";
                    }
                    $sender->sendMessage($o);
                break;

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
