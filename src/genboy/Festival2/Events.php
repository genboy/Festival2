<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;
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


use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\FallingSand;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\level\particle\FloatingTextParticle;




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

    /** onHurt
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onHurt(EntityDamageEvent $event) : void{
		$this->plugin->canDamage( $event );
	}

	/** onDamage
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
	 */
	public function onDamage(EntityDamageEvent $event) : void{
		$this->plugin->canDamage( $event );
	}

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event) : void{

        $cdata = $this->plugin->config;
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$playerName = strtolower($player->getName());
        $itemhand = $player->getInventory()->getItemInHand();

        if( isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["type"] )
          && $itemhand->getID() ==  $cdata['options']['itemid'] ){ // ? holding Festival tool
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

        }else{

            // block type allowed?

            // edit allowed?
            if(!$this->plugin->canEdit($player, $block)){
				$event->setCancelled();
			}

        }

    }


	/** Block break
	 * @param BlockBreakEvent $event
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{

        $cdata = $this->plugin->config;
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$playerName = strtolower($player->getName());
        $itemhand = $player->getInventory()->getItemInHand();

		if( isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["type"] )
            && $itemhand->getID() ==  $cdata['options']['itemid'] ){ // ? holding Festival tool
            $event->setCancelled();
            $newareatype = $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["type"];
            if( !isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos1"] ) ){ // add here the item-tool check

                $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos1"] = $block->asVector3();
                $o = TextFormat::GREEN . "Tab position 2 for new ". $newareatype ." area (diagonal end)";
                if( $newareatype == "sphere"){
                    $o = TextFormat::GREEN . "Tab distance position(2) to set radius for new ". $newareatype ." area center";
                }
                $player->sendMessage($o);
                return;
            }else if( !isset( $this->plugin->players[ strtolower( $playerName ) ]["makearea"]["pos2"] ) ){ // add here the item-tool check
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

        }else{

            // block type allowed?

            // edit allowed?
            if(!$this->plugin->canEdit($player, $block)){
				$event->setCancelled();
			}

        }
	}
	/** onBlockTouch
	 * @param PlayerInteractEvent $event
	 * @ignoreCancelled true
	 */
	public function onBlockTouch(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		if(!$this->plugin->canTouch($player, $block )){
			$event->setCancelled();
		}
	}
	/** onInteract
	 * @param PlayerInteractEvent $event
	 * @ignoreCancelled true

    public function onInteract( PlayerInteractEvent $event ): void{
        if ( !$this->canInteract( $event ) ) {
            $event->setCancelled();
        }
    }
    */

    /** Mob / Animal spawning
	 * @param EntitySpawnEvent $event
	 * @ignoreCancelled true
     */
    public function onEntitySpawn( EntitySpawnEvent $event ): void{
        $e = $event->getEntity();
        //($e instanceof Fire && !$this->canBurn( $e->getPosition() )) || (
        if( !$e instanceof Player && !$this->plugin->canEntitySpawn( $e ) ){
            //$e->flagForDespawn() to slow / ? $e->close(); private..
            $this->plugin->getServer()->getPluginManager()->callEvent(new EntityDespawnEvent($e));
            $e->despawnFromAll();
            if($e->chunk !== null){
                $e->chunk->removeEntity($e);
                $e->chunk = null;
            }
            if($e->isValid()){
                $e->level->removeEntity($e);
                $e->setLevel(null);
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

                        if( $area->getFlag("pass") || $area->getFlag("passage") ){ // Player area passage

                            $this->plugin->barrierLeaveArea($area, $event);
                            break;

                        }

                        if( !$area->getFlag("msg") ){
                            $msg = "Exit ". $area->getName();
                            $this->plugin->areaMessage( $msg , $player );
                        }
                        unset($this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )]);
                        break;
                    }

                }else if( $area->contains( $player->getPosition(), $player->getLevel()->getName() ) ){
                    // Player enter Area
                    if( !isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )] ) ){

                        if( $area->getFlag("pass") || $area->getFlag("passage") ){ // Player area passage

                            $this->plugin->barrierEnterArea($area, $event);
                            break;

                        }

                        if( !$area->getFlag("msg") ){
                            $msg = "Enter ". $area->getName();
                            $this->plugin->areaMessage( $msg , $player );
                        }

                        $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )] = $area;
                        break;
                    }
                    // Player enter Area Center
                    if( $area->centerContains( $player->getPosition(), $player->getLevel()->getName() ) ){
                        if( !isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] ) ){ // Player enter in Area
                            if( !$area->getFlag("msg") ){
                                $msg = "Enter ". $area->getName(). " center";
                                $this->plugin->areaMessage( $msg , $player );
                            }
                            $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] = $area;
                            break;
                        }
                    }else{
                        // Player leave Area Center
                        if( isset( $this->plugin->players[$playerName]["areas"][strtolower( $area->getName() )."center"] ) ){
                            if( !$area->getFlag("msg") ){
                                $msg = "Exit ". $area->getName(). " center";
                                $this->plugin->areaMessage( $msg , $player );
                            }
                            unset($this->plugin->players[$playerName]["areas"][strtolower( $area->getName() ). "center"]);
                            break;
                        }
                    }
                }
            }
        }
    }



}
