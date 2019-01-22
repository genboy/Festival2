<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\Player;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;


class Events implements Listener{

    /** @var Festival */
    private $plugin;

	public function __construct(Festival $plugin){

        $this->plugin = $plugin;

	}
    /**
     * @param PlayerJoinEvent $event
     */
	public function onJoin(PlayerJoinEvent $event) {
        // sessionstart
        $event->getPlayer()->sendMessage("Testing Festival 2 Remake here!");
        $this->plugin->players[ strtolower( $event->getPlayer()->getName() ) ] = ["name"=>$event->getPlayer()->getName(),"areas"=>[]];
	}

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void {
        unset( $this->plugin->players[ strtolower( $event->getPlayer()->getName() ) ] );
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event): void {
    }

    /**
     * @param PlayerItemHeldEvent $event
     */
    public function onHold(PlayerItemHeldEvent $event): void { //onItemHeld

        if ($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $cdata = $this->plugin->config;

        if( $event->getItem()->getID() ==  $cdata['options']['itemid'] && !isset( $this->plugin->players[ strtolower( $player->getName() ) ]["makearea"] ) ) {
            $this->plugin->form->openUI($player);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event) : void{

		$block = $event->getBlock();
		$player = $event->getPlayer();
		$playerName = strtolower($player->getName());

        if( isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["type"] ) ){
            $event->setCancelled();
            $newareatype = $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["type"];
            if( !isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos1"] ) ){

                $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos1"] = $block->asVector3();
                $o = TextFormat::GREEN . "Tab position 2 for new ". $newareatype ." area (diagonal end)";
                if( $newareatype == "sphere"){
                    $o = TextFormat::GREEN . "Tab distance position(2) to set radius for new ". $newareatype ." area center";
                }
                $player->sendMessage($o);
                return;
            }else if( !isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos2"] ) ){
                $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos2"] = $block->asVector3();
                $p1 = $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos1"];
                $p2 = $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos2"];

                $radius = intval( 0 );
                if( $newareatype == "sphere" ){
                    $dy = $p1->getY() - $p2->getY();
                    $dz = $p1->getZ() - $p2->getZ();
                    $dx = $p1->getX() - $p2->getX();
                    $df = sqrt( ($dy*$dy)+($dx*$dx) );
                    $radius = intval(  sqrt( ($df*$df)+($dz*$dz) ) );
                }
                $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["radius"] = $radius;
                // back to form
                $this->plugin->form->areaNewForm( $player , ["type"=>$newareatype,"pos1"=>$p1,"pos2"=>$p2,"radius"=>$radius], $msg = "New area setup:");
                return;
            }

        }

    }

    public function onMove(PlayerMoveEvent $event) : void{

		$player = $event->getPlayer();
		$playerName = strtolower( $player->getName() );

		if( !isset( $this->plugin->players[ $playerName ] ) ){
            $this->plugin->players[ $playerName ] = ["name"=>$playerName,"areas"=>[]];
		}
        if( isset( $this->plugin->areas ) && is_array( $this->plugin->areas ) ){

            foreach($this->plugin->areas as $area){

                // Player enter or leave area
                if( !$area->contains( $player->getPosition(), $player->getLevel()->getName() ) ){

                    // Player leave Area
                    if( isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )] )  ){
                        if( !$area->getFlag("msg") ){
                            $msg = "Exit ". $area->getName();
                            $this->areaMessage( $msg , $player );
                        }
                        unset($this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )]);
                        break;
                    }

                }else{
                    // Player enter Area
                    if( !isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )] ) ){
                        if( !$area->getFlag("msg") ){
                            $msg = "Enter ". $area->getName();
                            $this->areaMessage( $msg , $player );
                        }
                        $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )] = $area;
                        break;
                    }
                    // Player enter Area Center
                    if( $area->centerContains( $player->getPosition(), $player->getLevel()->getName() ) ){
                        if( !isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] ) ){ // Player enter in Area
                            if( !$area->getFlag("msg") ){
                                $msg = "Enter ". $area->getName(). " center";
                                $this->areaMessage( $msg , $player );
                            }
                            $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] = $area;
                            break;
                        }
                    }else{
                        // Player leave Area Center
                        if( isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] ) ){
                            if( !$area->getFlag("msg") ){
                                $msg = "Exit ". $area->getName(). " center";
                                $this->areaMessage( $msg , $player );
                            }
                            unset($this->plugin->players[$playerName]["areas"][strtolower( $area->getName() ). "center"]);
                            break;
                        }
                    }
                }
            }
        }
    }

    /** AreaMessage
	* define message type
	 * @param string $msg
	 * @param PlayerMoveEvent $ev->getPLayer()
	 * @param array $options
	 * @return true function
	 */
	public function areaMessage( $msg , $player ){
        $mt = $this->plugin->config['options']['msgpos'];
        switch($mt){
            case "title":
                $player->addTitle($msg); // $player->addTitle("Title", "Subtitle", $fadeIn = 20, $duration = 60, $fadeOut = 20);
            break;
            case "tip":
                $player->sendTip($msg);
            break;
            case "title":
                $player->sendPopup($msg);
            break;
            case "msg":
            default:
                $player->sendMessage($msg);
            break;
		}
	}

}
