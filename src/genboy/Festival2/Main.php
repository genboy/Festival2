<?php declare(strict_types = 1);
/** src/genboy/Festival/Main.php
 * Options: Msgtype, Msgdisplay, AutoWhitelist
 * Flags: god, pvp, flight, edit, touch, mobs, animals, effects, msg, passage, drop, tnt, shoot, hunger, perms, falldamage
 */
namespace genboy\Festival2;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\utils\TextFormat;

use genboy\Festival2\Language;
use genboy\Festival2\Config;
use genboy\Festival2\Flag;


class Main extends PluginBase {

    /** @var array[] */
	public  $config; // obj

    /** @var array[] */
	public $levels = []; // stack of level objects

	public $levellist  = []; // name associated list

	/** @var Area[]  */
	public $areas   = []; // stack of area objects

	public $arealist  = []; // name associated list


    public function onLoad() : void {
        // start a task
	}

	public function onEnable() : void {

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}

        /** load */
        $this->loadConfig();
        $this->loadLanguage();
        $this->loadLevels();
        $this->loadAreas();

        /** console info */
        $this->getLogger()->info( Language::translate("enabled-console-msg") );
        $this->getLogger()->info( Language::translate("language-selected") );

    }

	public function onDisable() : void {
        $this->getLogger()->info( Language::translate("disabled-console-msg") );
	}

     /** load Config
	 * @var plugin Options[]
     * @file resources/config.yml
	 */
    public function loadConfig(){

        $this->config = new Config($this);

    }

    /** load language
	 * @var plugin config[]
     * @file resources/translation en.json
     * @file resources/translation nl.json
	 * @var obj Language
	 */
    public function loadLanguage(){

      $languageCode = $this->config->options["Language"];
      $resources = $this->getResources("/translation"); // read files in resources /translation folder
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


    /** Load areas
	 * @var obj area
	 * @file areas.json
	 */
    public function loadAreas(){
        if(!file_exists($this->getDataFolder() . 'resources/' . "areas.json")){
			file_put_contents($this->getDataFolder() . 'resources/' . "areas.json", "[]");
		}
        $data = json_decode(file_get_contents($this->getDataFolder() . 'resources/' . "areas.json"), true);
		if( isset( $data ) && is_array( $data ) ){
            foreach($data as $datum){
                $flags = $datum["flags"]; // ..
                new Area($datum["name"], $datum["desc"], $flags, new Vector3($datum["pos1"]["0"], $datum["pos1"]["1"], $datum["pos1"]["2"]), new Vector3($datum["pos2"]["0"], $datum["pos2"]["1"], $datum["pos2"]["2"]), $datum["level"], $datum["whitelist"], $datum["commands"], $datum["events"], $this);
            }
        }
		$this->saveAreas(); // all save $this->areaList available :)
    }
    /** Save areas
	 * @var obj area
	 * @file areas.json
	 */
	public function saveAreas() : void{
		$areas = [];
		foreach($this->areas as $area){
			$areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];
            $this->arealist[strtolower( $area->getName() )] = $area; // name associated area list for inArea check
		}
		file_put_contents($this->getDataFolder() . 'resources/' . "areas.json", json_encode($areas));
	}


    /** Load levels
	 * @var obj area
	 * @file areas.json
	 */
    public function loadLevels(){
        if(!file_exists($this->getDataFolder() . 'resources/' . "levels.json")){
			file_put_contents($this->getDataFolder() . 'resources/' . "levels.json", "[]");
		}
    }

}
?>
