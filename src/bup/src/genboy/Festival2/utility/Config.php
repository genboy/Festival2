<?php declare(strict_types = 1);
/** src/genboy/Festival2/config.php */
namespace genboy\Festival2\utility;

use pocketmine\utils\Config as FileConfig;

use genboy\Festival2\Festival;

class Config{

	private $plugin;

    public $presets;

    public $options;

    public $defaults;

    public $worlds;

    public function __construct( Festival $plugin){

        $this->plugin = $plugin;

        $this->presets = $this->newPresets();

        $this->loadConfig();

    }

    public function loadConfig() : void{

        // get Festival configurations
        if( !$this->checkSetup() ){

            // loading new setup
            $this->checkYMLConfig();

        }
        // var_dump($this->presets);

    }

    public function checkSetup() : bool {

        $d = $this->plugin->data->getResources(); // from data

        if( isset( $d['config']['options'] ) && is_array( $d['config']['options'] ) && isset( $d['config']['defaults'] ) && is_array( $d['config']['defaults'] ) ){

            $this->options = $d['config']['options']; //var_dump($d['config']);
            $this->defaults = $d['config']['defaults']; //var_dump($d['config']);
            $this->worlds = $d['config']['worlds']; //var_dump($d['config']);

            $this->plugin->getLogger()->info( "Festival Config loaded" );

            $this->plugin->status = ['ready'];

            return true;

        }
        return false;

    }

    public function checkYMLConfig() : void{

        $this->plugin->getLogger()->info( "Festival Presets check" );

        // check & import from old yml config
        if( file_exists($this->plugin->getDataFolder() . "config.yml") ){
            $c = yaml_parse_file($this->plugin->getDataFolder() . "config.yml"); // some original old defaults
		}else if( file_exists($this->plugin->getDataFolder() . "resources/" . "config.yml") ){
            $c = yaml_parse_file($this->plugin->getDataFolder() . "resources/" . "config.yml"); // the old defaults
		}

        if( isset( $c["Options"] ) && is_array( $c["Options"] ) && isset( $c["Default"] ) && is_array( $c["Default"] ) ){

            $this->presets = $this->formatOldConfigs( $c ); // overwrite presets with old configs in new format

            $this->options =$this->presets['options']; // overwrite presets with old configs in new format
            $this->defaults = $this->presets['defaults']; // overwrite presets with old configs in new format
            $this->worlds = $this->presets['worlds']; // overwrite presets with old configs in new format

            $this->plugin->data->saveSource( 'config', $this->presets, 'json');

            $this->plugin->status = ['install', 'yml config setup'];

            $this->plugin->getLogger()->info( "Festival YML Presets loaded" );

        }else{

            $this->presets = $this->newPresets();
            $this->options =$this->presets['options']; // default presets
            $this->defaults = $this->presets['defaults'];
            $this->worlds = $this->presets['worlds'];

            $this->plugin->data->saveSource( 'config', $this->presets, 'json');

            $this->plugin->status = ['install', 'default config setup'];

            $this->plugin->getLogger()->info( "Festival Default Presets loaded" );
        }

    }

    public function changeConfig( $var, $val ){

        $config = new FileConfig($this->plugin->getDataFolder(). "resources" . DIRECTORY_SEPARATOR . "config.json", FileConfig::JSON );
        $config->set( $var, $val );
        $config->save();

    }


    public function newPresets() : ARRAY {

        // world / area defaults
        $c = [
        'options' =>[
            'msgdsp'        => 'op',    // msg display off,op,listed,on
            'msgpos'        => 'msg',   // msg position msg,title,tip,pop
            'areadsp'       => 'op',    // area title display off,op,listed,on
            'autolist'      => 'on',    // area creator auto whitelist off,on
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

        ],
        'worlds' =>[]
        ];

        return $c;

    }


    public function formatOldConfigs( $c ) : ARRAY {

        $p = $this->newPresets();

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
        /*
        if( isset( $c['Worlds'] ) && is_array( $c['Worlds'] ) ){
            foreach( $c['Worlds'] as $level ){
                foreach( $level as $ln => $flags ){
                    foreach( $flags as $fn => $set ){
                        $flagname = $this->isFlag( $fn );
                        if( $p['Worlds'][$ln][$flagname] ){
                            $p['Worlds'][$ln][$flagname] = $set;
                        }
                    }
                }
            }
        }*/

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

/** Notes
        //var_dump( $this->plugin->data->getResources() );
        //$d->saveSource( 'test', ['var1'=> 'test1','var2'=> 'test2'], 'yaml');
        //$this->changeConfig( 'test1', 'test ver 1' );
            $array = $config->getNested($key);
            $array[] = "value";
            //$array is ["value"], considering $array is [].
            $config->setNested($key, $array);
        */

