<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Cmd;
use genboy\Festival2\Helper;
use genboy\Festival2\FormUI;
use genboy\Festival2\Events;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use pocketmine\event\player\PlayerMoveEvent;

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
