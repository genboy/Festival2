<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Data;

use pocketmine\utils\TextFormat;

class Setup {

    /** @var Festival */
    private $plugin;

    private $data;

	public function __construct(Festival $plugin, Data $data){

        $this->plugin = $plugin;

        $this->data = $data;

        $this->checkPluginData();

	}

    public function checkPluginData(){

        $status = 1;
        if(!is_dir($this->plugin->getDataFolder())){
            @mkdir($this->plugin->getDataFolder());
            $status = 0;
		}

        if( !is_dir($this->plugin->getDataFolder().'resources') ){
            @mkdir($this->plugin->getDataFolder().'resources');
            $status = 0;
		}

        $srcfiles = ['config','levels','areas'];
        foreach( $srcfiles as $name ){
            if(!file_exists($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json")){
                file_put_contents($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $name . ".json", "");
                $status = 0;
            }
        }

        if( $status == 0 ){

            $this->plugin->getLogger()->info( "Festival installing..." );

        }

        $this->data->loadConfig();
        $cdata = $this->data->getConfig();
        if( !isset( $cdata ) || !isset( $cdata['options'] ) || !is_array( $cdata['options'] ) ){
            $this->newConfigs();

        }

        $this->data->loadLevels();
        $ldata = $this->data->getLevels();
        if( !isset( $ldata[0] ) ){
            $this->newLevels();
        }

        $this->data->loadAreas();
        $adata = $this->data->getAreas();
        if( !isset( $adata[0] ) ){
            $this->newAreas();
        }



    }

    public function newConfigs(){

        // check for config.yml
        $newconfig = $this->newConfigPreset();
        $ymldata = $this->plugin->helper->getSource( "config", "yml" );
        if( isset( $ymldata ) && isset( $ymldata['Options'] ) && is_array( $ymldata['Options'] ) ){
            $newconfig = $this->formatOldConfigs( $ymldata );
        }
        $this->data->saveConfig( $newconfig );

    }


    public function newLevels(){

        // create a list of current levels with loaded configs
        $config  = $this->data->getConfig();
        $worldlist = $this->plugin->helper->getServerWorlds();
        if( is_array( $worldlist ) ){
            $ldata[] = [ "name" => "DEFAULT", "desc" => "Level DEFAULT", "flags" => $config['defaults'] ];
            foreach( $worldlist as $ln){
                $ldata[] = [ "name" => $ln, "desc" => "Level ". $ln, "flags" => $config['defaults'] ];
            }
            $this->data->saveLevels( $ldata );
        }


    }

    public function newAreas(){

        // check for areas.config
        $this->data->saveAreas( [] );
        $this->plugin->getLogger()->info( "Festival no area's loaded; replace resources/areas.json" );

    }

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

    public function formatOldConfigs( $c ) : ARRAY {

        $p = $this->newConfigPreset();

        // overwrite default presets
        if( isset( $c['Options']['Msgdisplay'] ) ){
          $p['options']['msgdsp'] = $c['Options']['Msgdisplay'];
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
            $ldata = [];
            foreach( $c['Worlds'] as $ln => $lvlflags){
                /*if( $ln == "DEFAULT" ){
                    $ln = $this->plugin->getServer()->getDefaultLevel()->getName();
                }*/
                $newflags = [];
                foreach( $lvlflags as $f => $set ){
                    $flagname = $this->isFlag( $f );
                    $newflags[$flagname] = $this->isFlag( $f );
                }
                $ldata[] = [ "name" => $ln, "desc" => "Level ". $ln, "flags" => $newflags ];
            }
            $this->data->saveLevels( $ldata );

            $this->plugin->getLogger()->info( "Festival config.yml level data used" );
        }

        return $p;
    }

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
