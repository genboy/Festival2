<?php declare(strict_types = 1);
/** src/genboy/Festival2/Setup.php
 *
 * setup helper
 *
 */
namespace genboy\Festival2;

use pocketmine\utils\Config;
use genboy\Festival2\Festival;

class Setup {

    private $plugin;

    public $id;

    public $text;

    public $types;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->types = [
          0 => 'load',
          1 => 'install',
          2 => 'config',
          3 => 'ready',
        ];

        $this->id = 0;
        $this->text = 'Loading';

    }

    public function changeStatus( $id, $text = false ) : void{

        if( isset( $this->types[$id] ) ){

            $this->id = $id;

            if($text){
                $this->text = $text;
            }else{
                $this->text = $this->types[$id];
            }
        }

    }

    public function checkStatus() : void{

        $this->plugin->setup->changeStatus( 0, 'Loading' );

        $d = $this->plugin->data;

        if( isset( $d['config']['options'] ) && is_array( $d['config']['options'] ) && isset( $d['config']['levels'] ) && is_array( $d['config']['levels'] ) ){

            $this->plugin->setup->changeStatus( 3, 'Ready' );

        }else{

            $this->plugin->setup->changeStatus( 1, 'Install & Setup' );

            $this->checkPresetConfig();

        }

    }

    public function checkPresetConfig() : void{

        // check if json config defaults
        $conf = $this->plugin->data['config'];
        $lvls = $this->plugin->data['levels'];

        // check & import from old yml config
        if( file_exists($this->plugin->getDataFolder() . "config.yml") ){

            $c = yaml_parse_file($this->plugin->getDataFolder() . "config.yml"); // some original old defaults

		}else if( file_exists($this->plugin->getDataFolder() . "resources/" . "config.yml") ){

            $c = yaml_parse_file($this->plugin->getDataFolder() . "resources/" . "config.yml"); // the old defaults

		}

        if( !isset( $conf["options"] ) ){

            if( isset( $c["Options"] ) && is_array( $c["Options"] ) ){

                $cdata = $this->formatOldConfigs( $c ); // overwrite old configs in new format

                $this->plugin->setup->changeStatus( 2, 'yml config setup');

                $this->plugin->getLogger()->info( "Festival YML config loaded" );

            }else{

                $cdata = $this->newPresets(); // preset defaults

                $this->plugin->setup->changeStatus( 2, 'default config setup' );

                $this->plugin->getLogger()->info( "Festival Default Presets loaded" );

            }


        }


        if( !isset( $lvls[0] ) || !is_array( $lvls[0] ) ){

            $preset = $this->newPresets(); // preset defaults
            $lvls = $preset['worlds'];

            if( isset( $c["Worlds"] ) && is_array( $c['Worlds'] ) ){
                $setlvls = $c['Worlds'];
            }else if( isset( $cdata['worlds'] ) && is_array( $cdata['worlds'] ) ){
                $setlvls = $cdata['worlds'];
            }

            foreach( $setlvls as $ln => $set ){
                if( $ln == "DEFAULT" ){
                    $ln = $this->plugin->getServer()->getDefaultLevel()->getName();
                }
                $lvls[$ln] = $set;
            }

            if( isset($cdata['worlds'] ) ){
                unset( $cdata['worlds'] );
            }

        }

        $this->plugin->helper->saveSource( 'config', $cdata, 'json');
        $this->plugin->helper->saveSource( 'levels', $lvls, 'json');

        $this->plugin->data = $this->plugin->helper->getResources(); // reload data

        $this->plugin->setup->changeStatus( 3, 'Configs Ready' );

        $this->plugin->getLogger()->info( "Festival Level defaults loaded" );

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

        $lvlspreset = [];
        $lvlist = $this->plugin->helper->getServerWorlds();
        if( is_array($lvlist) ){
            foreach($lvlist as $lvl){
                $c['worlds'][$lvl] = $c['defaults'];
            }
        }

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

        if( isset( $c['Worlds'] ) && is_array( $c['Worlds'] ) ){
            foreach( $c['Worlds'] as $ln => $level){
                if( $ln == "DEFAULT" ){
                    $ln = $this->plugin->getServer()->getDefaultLevel()->getName();
                }
                foreach( $level as $f => $set ){

                    $flagname = $this->isFlag( $f );

                    $p['worlds'][$ln][$flagname] = $set;

                }
            }
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
