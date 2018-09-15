<?php declare(strict_types = 1);
/** src/genboy/Festival/Main.php
 * Options: Msgtype, Msgdisplay, AutoWhitelist
 * Flags: god, pvp, flight, edit, touch, mobs, animals, effects, msg, passage, drop, tnt, shoot, hunger, perms, falldamage
 */
namespace genboy\Festival;


use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

use genboy\Festival\lang\Language;

class Main extends PluginBase implements Listener{

    /** @var array[] */
	public $options        = [];

    public function onLoad() : void {
	}

	public function onEnable() : void {


		$this->getServer()->getPluginManager()->registerEvents($this, $this);


        if(!file_exists($this->getDataFolder() . "config.yml")){
			$c = $this->getResource("config.yml");
			$o = stream_get_contents($c);
			fclose($c);
			file_put_contents($this->getDataFolder() . "config.yml", str_replace("DEFAULT", $this->getServer()->getDefaultLevel()->getName(), $o));
		}

        $c = yaml_parse_file($this->getDataFolder() . "config.yml");


		// innitialize configurations & update options
		if( isset( $c["Options"] ) && is_array( $c["Options"] ) ){
            if(!isset($c["Options"]["Language"])){
				$c["Options"]["Language"] = 'en';
            }
        }

        $this->options = $c["Options"];


        /** load language translation class */
        $this->loadLanguage();


        /** console output */
        $this->getLogger()->info( Language::translate("enabled-console-msg") );

    }


	public function onDisable() : void {

        /** console output */
        $this->getLogger()->info( Language::translate("disabled-console-msg") );

	}

    /** load language
	 * @var plugin config[]
     * @file resources en.json
     * @file resources nl.json
	 * @var obj Language
	 */
    public function loadLanguage(){
      $languageCode = $this->options["Language"];
      $resources = $this->getResources(); // read files in resources folder
      foreach($resources as $resource){
        if($resource->getFilename() === "en.json"){
          $default = json_decode(file_get_contents($resource->getPathname(), true), true);
        }
        if($resource->getFilename() === $languageCode.".json"){
          $setting = json_decode(file_get_contents($resource->getPathname(), true), true);
        }
      }
      if(isset($setting)){
        $langJson = $setting;
      }else{
        $langJson = $default;
      }
      new Language($this, $langJson);
    }

}
?>
