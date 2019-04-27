<?php declare(strict_types = 1);
/** src/genboy/Festival2/Cmd.php
 *
 * command helper
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
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
                break;

                case "tp":
                    if (!isset($args[1])){
                        //$o = TextFormat::RED . "You must specify an existing Area name";
                        $o = TextFormat::RED . "Please specify teleport destination area";
                        break;
                    }
                    if( isset( $this->plugin->areas[strtolower($args[1])] ) ){
                        $area = $this->plugin->areas[strtolower($args[1])];
                        $position = $sender->getPosition();
                        $perms = (isset($this->plugin->levels[$position->getLevel()->getName()]) ? $this->plugin->levels[ $position->getLevel()->getName() ]->getFlag('perms') : $this->plugin->defaults['perms']);
                        if( $perms || $area->isWhitelisted($playerName) || $sender->hasPermission("festival") || $sender->hasPermission("festival.command") || $sender->hasPermission("festival.command.fc.tp")){
                            $levelName = $area->getLevelName();
                            if( isset($levelName) && Server::getInstance()->loadLevel($levelName) != false){ //$this->plugin->getServer()->getLevelByName($levelName) != false ){ // ? Server::getInstance()->getLevealByName($levelName)
                                $o = TextFormat::GREEN .'Teleporting to area ' . $area->getName();
                                $cx = $area->getSecondPosition()->getX() + ( ( $area->getFirstPosition()->getX() - $area->getSecondPosition()->getX() ) / 2 );
                                $cz = $area->getSecondPosition()->getZ() + ( ( $area->getFirstPosition()->getZ() - $area->getSecondPosition()->getZ() ) / 2 );
                                $cy1 = min( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                                $cy2 = max( $area->getSecondPosition()->getY(), $area->getFirstPosition()->getY());
                                /*if( !$this->hasFallDamage($sender) ){
                                    $this->playerTP[$playerName] = true; // player tp active $this->areaMessage( 'Fall save on!', $sender );
                                }*/
                                $sender->teleport( new Position( $cx, $cy2 - 2, $cz, $area->getLevel() ) );
                            }else{
                                // level problem
                                $o = 'The Area level '.$levelName.' is not available';
                            }
                        }else{
                            // no permissions
                            $o = 'You do not have permission to use this';
                        }
                    }else{
                        // area problem
                        $o = 'The target Area not found or an argument is missing';
                    }
                break;

                case "form": // festival 2
                default:
                    $this->getUIForm($sender);
                break;

            }
            $sender->sendMessage($o);
            //$o = TextFormat::GREEN . "Manage Festival";
            //$sender->sendMessage($o);
        }
    }

    /** getUIForm
	 * @class FormUI
	 * @func FormUI->openUI
     */
    public function getUIForm($sender){
        $this->plugin->form->openUI($sender);
    }

}
