<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Cmd;
use genboy\Festival2\Helper;
use genboy\Festival2\FormUI;
use genboy\Festival2\Events;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Festival extends PluginBase {

    public $config;

    public $defaults;

    public $form;

    public $levels;

    public $areas;

    public $players;

    public function onEnable() : void {

        $this->getServer()->getPluginManager()->registerEvents( new Events($this), $this );

        $this->helper = new Helper($this);

        $this->form = new FormUI($this);

        $this->dataSetup();
    }

    /** dataSetup
	 * @class Helper
	 * @func Helper getSource
	 * @var $plugin->options
     */
    public function dataSetup(): bool{

        // check config file and defaults
        $config = $this->helper->getSource( "config" );
        if( isset( $config["options"] ) && is_array( $config["options"] ) ){
            $this->config = $config;
        }else{

            $oldconfig = $this->helper->getSource( "config", "yml" );

            //var_dump( $oldconfig );

            if( isset( $oldconfig["Options"] ) && is_array( $oldconfig["Options"] ) && isset( $oldconfig["Default"] ) && is_array( $oldconfig["Default"] ) ){
                $this->config = $this->helper->formatOldConfigs( $oldconfig );
            }else{
                $this->config = $this->helper->newConfigPreset();
                $this->getLogger()->info( "Festival config.yml not found, default configurations loaded!" );
            }
        }
        $this->helper->saveSource( "config", $this->config );

        // check level defaults
        if( !is_array($this->levels) ){
            $this->helper->loadDefaultLevels();
        }

        // check areas
        if( !$this->helper->loadAreas() || !is_array( $this->areas) ){
            $this->helper->loadDefaultAreas();
            $this->getLogger()->info( "Festival has no area's to load, yet!" );
        }
        return true;

    }

    /** onCommand
	 * @param CommandSender $sender
	 * @param Command $cmd
	 * @param string $label
	 * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        if(!($sender instanceof Player)){
            $sender->sendMessage(TextFormat::RED . "Command must be used in-game.");
			return true;
		}
		if(!isset($args[0])){
            $this->form->openUI($sender);
		}else{
            new Cmd( $sender, $cmd, $label, $args, $this ); // command helper
        }
		return true;
    }

    /**
	 * Area event barrier enter
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function barrierEnterArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
        if( $player->isOp() || $area->isWhitelisted( strtolower( $player->getName() )  ) || $player->hasPermission("festival") || $player->hasPermission("festival.access") ){
            // permission
            if($area->isWhitelisted( strtolower( $player->getName() )  )){
                $msg = TextFormat::GREEN .  "Whitelisted enter barrier area" . " " . $area->getName();
            }else{
                $msg = TextFormat::GREEN .  "Op enter barrier area" . " " . $area->getName();
            }
            $this->players[strtolower( $player->getName() )]["areas"][strtolower( $area->getName() )] = $area;
            $this->areaMessage( $msg, $player );
        }else{
            // barrier
		    $ev->getPlayer()->teleport($ev->getFrom()); // teleport to previous position
            if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
                if( $this->skippTime( 2, strtolower($player->getName()) ) ){
                    $msg = TextFormat::YELLOW .  "Cannot enter area" . " " . $area->getName();
                    $this->areaMessage( $msg, $player );
                }
            }
        }
		return;
	}

	/** Area event barrier leave
	 * @param area Area
	 * @param PlayerMoveEvent $ev
	 * @return false
	 */
	public function barrierLeaveArea(Area $area, PlayerMoveEvent $ev): void{
		$player = $ev->getPlayer();
        if( $player->isOp() || $area->isWhitelisted( strtolower( $player->getName() )  ) || $player->hasPermission("festival") || $player->hasPermission("festival.access") ){
            // permission
           if($area->isWhitelisted( strtolower( $player->getName() )  )){
                $msg = TextFormat::GREEN .  "Whitelisted leave barrier area" . " " . $area->getName();
            }else{
                $msg = TextFormat::GREEN .  "Op leave barrier area" . " " . $area->getName();
            }
            unset( $this->players[strtolower( $player->getName() )]["areas"][strtolower( $area->getName() )] );
            $this->areaMessage( $msg, $player );
        }else{
            // barrier
            $ev->getPlayer()->teleport($ev->getFrom()); // teleport to previous position inside area
            if( !$area->getFlag("msg")  || $this->msgOpDsp( $area, $player ) ){
                if( $this->skippTime( 2, strtolower($player->getName()) ) ){
                    $msg = TextFormat::YELLOW . "Cannot leave area" . " " . $area->getName();
                }
                if( $msg != ''){
                    $this->areaMessage( $msg, $player );
                }
            }
        }
		return;
	}




    /** Player Damage Impact
	 * @param EntityDamageEvent $event
	 * @ignoreCancelled true
     */
	public function canDamage(EntityDamageEvent $ev) : bool{
        if($ev->getEntity() instanceof Player){
			$player = $ev->getEntity();
			$playerName = strtolower($player->getName());

			if( !$this->canGetHurt( $player ) ){
                if( $player->isOnFire() ){
                    $player->extinguish();
                }
				$ev->setCancelled();
                //$this->areaMessage( 'You can not get hurt..', $player );
                return false;
			}
            /*
            if( !$this->canBurn( $player->getPosition() )){
                if( $player->isOnFire() ){
                    $player->extinguish(); // 1.0.7-dev
				    $ev->setCancelled();
                    return false;
                }
			}
            if(!$this->canPVP($ev)){ // v 1.0.6-13
				$ev->setCancelled();
                return false;
			}
			if( isset($this->playerTP[$playerName]) && $this->playerTP[$playerName] == true ){
				unset( $this->playerTP[$playerName] ); //$this->areaMessage( 'Fall save off', $player );
				$ev->setCancelled();
                return false;
			}
            */
            //$this->areaMessage( 'You can get hurt..', $player );
		}

        return true;
    }

    /** Hurt
	 * @param Entity $entity
	 * @return bool
	 */
	public function canGetHurt(Player $player) : bool{
		$o = true;
        if( $player instanceof Player){
            $g = (isset($this->levels[ strtolower( $player->getLevel()->getName() ) ]) ? $this->levels[ strtolower( $player->getLevel()->getName() ) ]->getFlag("god") : $this->defaults['god']);
            if($g){
                $o = false;
            }
            $playername =  strtolower($player->getName());

            foreach ($this->areas as $area) {
                if ($area->contains(new Vector3($player->getX(), $player->getY(), $player->getZ()), $player->getLevel()->getName() )) {
                    if($area->getFlag("god")){
                        $o = false;
                    }
                    if(!$area->getFlag("god") && $g ){
                        $o = true;
                    }
                    if($area->isWhitelisted($playername)){
                        $o = false;
                    }
                }
            }

        }
		return $o;
	}



    /** pvp */
    /** flight */


	/** canEdit
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canEdit(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
		$o = true;
		$e = (isset($this->levels[ strtolower( $position->getLevel()->getName() ) ]) ? $this->levels[ strtolower( $position->getLevel()->getName() ) ]->getFlag("edit") : $this->defaults['edit']);
		if($e){
			$o = false;
		}
        $playername = strtolower($player->getName());
        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if($area->getFlag("edit")){
                    $o = false;
                }
                if(!$area->getFlag("edit") && $e){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }
		return $o;
	}


	/** canTouch
	 * @param Player   $player
	 * @param Position $position
	 * @return bool
	 */
	public function canTouch(Player $player, Position $position) : bool{
		if($player->hasPermission("festival") || $player->hasPermission("festival.access")){
			return true;
		}
        $playername = strtolower($player->getName());
		$o = true;
		$t = (isset($this->levels[ strtolower( $position->getLevel()->getName() ) ]) ? $this->levels[ strtolower( $position->getLevel()->getName() ) ]->getFlag("touch") : $this->defaults['touch']);

		if($t){
			$o = false;
		}
        foreach ($this->areas as $area) {
            if ($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $position->getLevel()->getName() )) {
                if($area->getFlag("touch")){
                    $o = false;
                }
                if(!$area->getFlag("touch") && $t){
                    $o = true;
                }
                if($area->isWhitelisted($playername)){
                    $o = true;
                }
            }
        }
		return $o;
	}



    /** mobs */
    /** animals */
    /** effects */
    /** msg */
    /** passage */
    /** drop */
    /** explode */
    /** tnt */
    /** fire */
    /** shoot */
    /** hunger */
    /** perms */
    /** falldamage */
    /** cmdmode */


    /**
	 * OpMsg define message persistent display
	 * @param Area $area
	 * @param PlayerMoveEvent $ev->getPLayer()
	 * @param array $options
	 * @return bool
	 */
	public function msgOpDsp( $area, $player ){
		if( isset( $this->config->options['msgdsp'] ) && $player->isOp() ){
			if( $this->config->options['msgdsp'] == 'on' ){
				return true;
			}else if( $this->config->options['msgdsp'] == 'op' && $area->isWhitelisted(strtolower($player->getName())) ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

    /** skippTime
	 * delay function for str player $nm repeating int $sec
	 * @param string $sec
	  * @return false
	 */
    public function skippTime($sec, $nm){
		$t = false;
        if(!isset($this->skipsec[$nm])){
            $this->skipsec[$nm] = time();
        }else{
            if( ( ( time() - $sec ) > $this->skipsec[$nm]) || !$this->skipsec[$nm] ){
                $this->skipsec[$nm] = time();
                $t = true;
            }
        }
		return $t;
	}

    /** AreaMessage
	* define message type
	 * @param string $msg
	 * @param PlayerMoveEvent $ev->getPLayer()
	 * @param array $options
	 * @return true function
	 */
	public function areaMessage( $msg , $player ){
        $mt = $this->config['options']['msgpos'];
        switch($mt){
            case "title":
                $player->addTitle($msg); // $player->addTitle("Title", "Subtitle", $fadeIn = 20, $duration = 60, $fadeOut = 20);
            break;
            case "tip":
                $player->sendTip($msg);
            break;
            case "pop":
                $player->sendPopup($msg);
            break;
            case "msg":
            default:
                $player->sendMessage($msg);
            break;
		}
	}

}
