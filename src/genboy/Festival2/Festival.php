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


}
