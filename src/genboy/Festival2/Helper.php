<?php declare(strict_types = 1);
/** src/genboy/Festival2/Helper.php
 *
 * global helper
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Level as FeLevel;
use genboy\Festival2\Area as FeArea;


use pocketmine\math\Vector3;

class Helper {

    private $plugin;

    /** __construct
	 * @param Festival
     */
    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        if(!is_dir($this->plugin->getDataFolder())){
            @mkdir($this->plugin->getDataFolder());
		}

        if( !is_dir($this->plugin->getDataFolder().'resources') ){
            @mkdir($this->plugin->getDataFolder().'resources');
		}

    }

    /** loadLevels
	 * @file resources/levels.json
     * @var plugin levels
	 * @class FeLevel
     */
    public function loadLevels(): bool{
        // create a list of current levels from saved json
        $ldata = $this->getSource( "levels" );
        if( isset($ldata) && is_array($ldata) ){
            foreach($ldata as $level){
                new FeLevel($level["name"], $level["desc"], $level["flags"], $this->plugin);
            }
            return true;
        }
        return false;
    }

    /** saveLevels
	 * @file resources/levels.json
     * @var plugin levels
	 * @param array $data
     */
    public function saveLevels(): void{
        // save current levels to json
        foreach($this->plugin->levels as $level){
            $levels[] = [ "name" => $level->getName(), "desc" => $level->getDesc(), "flags" => $level->getFlags() ];
        }
        $this->saveSource( 'levels', $levels );
    }

    /** loadAreas
	 * @file resources/areas.json
     * @func this getSource
	 * @var obj FeArea
	 * @param array $data
     */
    public function loadAreas(): bool{
        // create a list of current areas from saved json
        $adata = $this->getSource( "areas" );
        if( isset($adata) && is_array($adata) ){
            foreach($adata as $area){
                if( !isset($area["radius"]) ){
                    $area["radius"] = 0;
                }
                new FeArea($area["name"], $area["desc"], $area["flags"], new Vector3($area["pos1"]["0"], $area["pos1"]["1"], $area["pos1"]["2"]), new Vector3($area["pos2"]["0"], $area["pos2"]["1"], $area["pos2"]["2"]), $area["radius"], $area["level"], $area["whitelist"], $area["commands"], $area["events"], $this->plugin);
            }
            $this->plugin->getLogger()->info( "Festival has ".count($adata)." area's set!" );
            $this->saveAreas(); // make sure recent updates are saved
            return true;
        }
        return false;
    }

    /** Save areas
	 * @var obj Festival
	 * @var obj FeArea
	 * @file areas.json
	 */
	public function saveAreas() : void{
        // save current areas to json
		$areas = [];
        foreach($this->plugin->areas as $area){
            $areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "radius" => $area->getRadius(), "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];
        }
        $this->saveSource( "areas", $areas );
    }

    /** getAreaListSelected
     * @func this saveSource []
     */
    public function getAreaNameList( $sender = false, $cur = false ){
        // default array empty
        $options = [];
        $slct = 0;
        $c = 0;
        foreach( $this->plugin->areas as $nm => $area ){
            if($cur != false && $sender != false){
                if( isset( $this->plugin->players[strtolower( $sender->getName() )]["areas"][strtolower( $nm )] )  ){
                    $slct = $c;
                }
            }
            $options[] = $nm;
            $c++;
        }
        $lst = $options;
        if($cur != false){
            $lst = [ $options, $slct ];
        }
        return $lst;
    }



    /** getServerInfo
	 * @func plugin getServer()
     */
    public function getServerInfo() : ARRAY {
        $s = [];
        $s['ver']   = $this->plugin->getServer()->getVersion();
        $s['api']   = $this->plugin->getServer()->getApiVersion();
        return $s;
    }

    /** getServerWorlds
	 * @func plugin getServer()->getDataPath()
	 * @dir worlds
     */
    public function getServerWorlds() : ARRAY {
        $worlds = [];
        $worldfolders = array_filter( glob($this->plugin->getServer()->getDataPath() . "worlds/*") , 'is_dir');
        foreach( $worldfolders as $worldfolder) {
            $worlds[] = basename($worldfolder);
            $worldfolder = str_replace( $worldfolders, "", $worldfolder);
            if( $this->plugin->getServer()->isLevelLoaded($worldfolder) ) {
                continue;
            }
            /* Load all world levels
            if( !empty( $worldfolder ) ){
                $this->plugin->getServer()->loadLevel($worldfolder);
            } */
        }
        return $worlds;
    }


    /** saveConfig
	 * @class Helper
	 * @file resources/config.json
	 * @param array $data
     */
    public function saveConfig( $data ){
        $this->plugin->config = $data;
        $this->saveSource( 'config', $data );
    }

    /** getSource
	 * @param string $name
	 * @param (string $type)
	 * @func plugin getDataFolder()
     * @func yaml_parse_file
     * @func json_decode
     * @return array
     */
    public function getSource( $name , $type = 'json' ) : ARRAY {
        if( file_exists($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".". $type)){
            switch( $type ){
                case 'yml':
                case 'yaml':
                    $data = yaml_parse_file($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".yml"); // the old defaults
                break;
                case 'json':
                default:
                    $data = json_decode( file_get_contents( $this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json" ), true );
                break;
            }
        }
        if( isset( $data ) && is_array( $data ) ){
            return $data;
        }
        return [];
    }

    /** saveSource
	 * @param string $name
	 * @param array $data
	 * @param string $type default
	 * @func plugin getDataFolder()
     * @func FileConfig
     * @func json_encode
     * @func file_put_contents
     * @return array
     */
    public function saveSource( $name, $data, $type = 'json') : ARRAY {
        switch( $type ){
            case 'yml':
            case 'yaml':
                 $src = new FileConfig($this->plugin->getDataFolder(). "resources" . DIRECTORY_SEPARATOR . $name . ".yml", FileConfig::YAML, $data);
                 $src->save();
            break;
            case 'json':
            default:
		        file_put_contents( $this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json", json_encode( $data ) );
            break;
        }
        return $this->getSource( $name , $type );
    }

    /** newConfigPreset
     * @return array[ options, defaults ]
     */
    public function newConfigPreset() : ARRAY {

        // world / area defaults
        $c = [
        'options' =>[
            'itemid'    =>  201,     // Purpur Pillar itemid key held item
            'msgdsp'     => 'op',    // msg display off,op,listed,on
            'msgpos'     => 'msg',   // msg position msg,title,tip,pop
            'areadsp'    => 'op',    // area title display off,op,listed,on
            'autolist'   => 'on',    // area creator auto whitelist off,on
        ],
        'defaults' =>[

            'perms'     => true,
            'pass'      => true,
            'msg'       => true,
            'edit'      => true,
            'touch'     => true,
            'flight'    => true,
            'hurt'      => true,    // previous god flag
            'fall'      => true,    // previous falldamage flag
            'explode'   => true,
            'tnt'       => true,
            'fire'      => true,
            'shoot'     => true,
            'pvp'       => true,
            'effect'    => true,
            'hunger'    => true,
            'drop'      => true,
            'mobs'      => true,
            'animals'   => true,
            'cmd'       => true,

        ]];

        return $c;

    }

   /** formatOldConfigs
	 * @param array $c
	 * @func plugin getLogger()
     * @func $this isFlag()
     * @func $this loadLevels
     * @func file_put_contents
     * @return array
     */
    public function formatOldConfigs( $c ) : ARRAY {

        $p = $this->newConfigPreset();

        // overwrite default presets
        if( isset( $c['Options']['Msgdisplay'] ) ){
            $p['options']['msgdsp'] = "off";
            if( $c['Options']['Msgdisplay'] == true || $c['Options']['Msgdisplay'] == "on" ){
                $p['options']['msgdsp'] = "on";
            }
        }
        if( isset( $c['Options']['Msgtype'] ) ){
          $p['options']['msgpos'] = $c['Options']['Msgtype'];
        }
        if( isset( $c['Options']['Areadisplay'] ) ){
          $p['options']['areadsp'] = $c['Options']['Areadisplay'];
        }
        if( isset( $c['Options']['AutoWhitelist'] ) ){
          $p['options']['autolist'] = $c['Options']['AutoWhitelist'];
        }

        if( isset( $c['Default'] ) && is_array( $c['Default'] ) ){
            foreach( $c['Default'] as $fn => $set ){
                $flagname = $this->isFlag( $fn );
                if( isset($p['defaults'][$flagname]) ){
                    $p['defaults'][$flagname] = $set;
                }
            }
        }
        $this->plugin->getLogger()->info( "Festival config.yml option data used" );

        if( isset( $c['Worlds'] ) && is_array( $c['Worlds'] ) ){

            if( !$this->loadLevels() || !is_array($this->plugin->levels) ){ // might be loaded allready..

                // create levels: old config levels or default levels
                $worldlist = $this->plugin->helper->getServerWorlds();

                foreach( $worldlist as $ln){
                    $desc = "Festival Area ". $ln;
                    if( isset( $c['Worlds'][ $ln ] ) && is_array( $c['Worlds'][ $ln ] ) ){
                        $lvlflags = $c['Worlds'][ strtolower($ln) ];
                        $newflags = [];
                        foreach( $lvlflags as $f => $set ){
                            $flagname = $this->isFlag( $f );
                            $newflags[$flagname] = $set;
                        }
                        new FeLevel($ln, $desc, $newflags, $this->plugin);
                    }else{
                        $presets = $this->newConfigPreset();
                        new FeLevel($ln, $desc, $presets['defaults'], $this->plugin);
                    }
                }
                $this->saveLevels( $this->plugin->levels );
                $this->plugin->getLogger()->info( "Festival config.yml level data used" );
            }
        }

        return $p;
    }

      /** loadDefaultLevels
	 * @var plugin config
	 * @file resources/levels.json
	 * @func plugin getLogger()
	 * @func this saveLevels()
     * @var plugin levels
	 * @class FeLevel
     */
    public function loadDefaultLevels(){
        // create a list of current levels with loaded configs
        $config  = $this->plugin->config;
        $worldlist = $this->plugin->helper->getServerWorlds();
        if( is_array( $worldlist ) ){
            new FeLevel("DEFAULT", "Default world level", $config['defaults'], $this->plugin);

            foreach( $worldlist as $ln){
                $desc = "Festival Level ". $ln;
                new FeLevel($ln, $desc, $config['defaults'], $this->plugin);
            }
            $this->saveLevels();
        }
    }

    /** loadDefaultAreas
     * @func this saveSource []
     */
    public function loadDefaultAreas(){
        // default array empty
        $this->saveSource( "areas", [] );
    }



    /** isFlag
     * @param string
     * @return string
     */
    public function isFlag( $str ) : string {
        // flag names
        $names = [
            "god","God","save","hurt",
            "pvp","PVP",
            "flight", "fly",
            "edit","Edit","build","break","place",
            "touch","Touch","interact",
            "mobs","Mobs","mob",
            "animals","Animals","animal",
            "effects","Effects","magic","effect",
            "tnt","TNT",
            "explode","Explode","explosion","explosions",
            "fire","Fire","fires","burn",
            "hunger","Hunger","starve",
            "drop","Drop",
            "msg","Msg","message",
            "passage","Passage","pass","barrier",
            "perms","Perms","perm",
			"falldamage","Falldamage","nofalldamage","fd","nfd","fall",
            "shoot","Shoot", "launch",
            "cmdmode","CMD","CMDmode","commandmode","cmdm", "cmd",
        ];
        $str = strtolower( $str );
        $flag = false;
        if( in_array( $str, $names ) ) {
            $flag = $str;
            if( $str == "save" || $str == "hurt" || $str == "god"){
                $flag = "hurt";
            }
            if( $str == "fly" || $str == "flight"){
                $flag = "flight";
            }
            if( $str == "build" || $str == "break" || $str == "place" || $str == "edit"){
                $flag = "edit";
            }
            if( $str == "touch" || $str == "interact" ){
                $flag = "touch";
            }
            if( $str == "animals" || $str == "animal" ){
                $flag = "animals";
            }
            if( $str == "mob" || $str == "mobs"  ){
                $flag = "mobs";
            }
            if( $str == "magic" || $str == "effects" || $str == "effect" ){
                $flag = "effect";
            }
            if( $str == "message"  || $str == "msg"){
                $flag = "msg";
            }
            if( $str == "perm"  || $str == "perms" ){
                $flag = "perms";
            }
            if( $str == "passage" || $str == "barrier" || $str == "pass" ){
                $flag = "pass";
            }
            if( $str == "explosion" || $str == "explosions" || $str == "explode" ){
                $flag = "explode";
            }
            if( $str == "tnt"  ){
                $flag = "tnt";
            }
            if( $str == "fire" || $str == "fires" || $str == "burn" ){
                $flag = "fire";
            }
            if( $str == "shoot" || $str == "launch" ){
                $flag = "shoot";
            }
            if( $str == "effect" || $str == "effects" || $str == "magic"){
                $flag = "effects";
            }
            if( $str == "hunger" || $str == "starve" ){
                $flag = "hunger";
            }
			if( $str == "nofalldamage" || $str == "falldamage" || $str == "fd" || $str == "nfd" || $str == "fall"){
				$flag = "fall";
			}
            if( $str == "cmd" || $str == "cmdmode" || $str == "commandmode" || $str == "cmdm"){ // ! command is used as function..
                $flag = "cmd";
            }
        }
        return $flag;
    }



}
