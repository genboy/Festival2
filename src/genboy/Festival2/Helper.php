<?php declare(strict_types = 1);
/** src/genboy/Festival2/Helper.php
 *
 * global helper
 *
 */
namespace genboy\Festival2;

use pocketmine\utils\Config;

use genboy\Festival2\Festival;

class Helper {

    private $plugin;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->loadData();

    }

    public function loadData() : void {

       $this->plugin->data = $this->getResources();

       $this->plugin->getLogger()->info( "Festival Data loaded" );

    }

    public function getResources() : ARRAY {

        $data = [];

        $sourcelist = [ 'config', 'levels', 'areas' ]; // 'events', 'commands'

        if(!is_dir($this->plugin->getDataFolder())){
            @mkdir($this->plugin->getDataFolder());
		}

        if( !is_dir($this->plugin->getDataFolder().'resources') ){
            @mkdir($this->plugin->getDataFolder().'resources');
		}

        foreach($sourcelist as $nm){

            if(!file_exists($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $nm . ".json")){
                file_put_contents($this->plugin->getDataFolder() . "resources" . DIRECTORY_SEPARATOR . $nm . ".json", "[]");
            }
            $data[ $nm ] = $this->getSource( $nm );

        }

        return $data;

    }

    public function getInfo() : ARRAY {

        $s = [];

        $s['ver']   = $this->plugin->getServer()->getVersion();
        $s['api']   = $this->plugin->getServer()->getApiVersion();

        return $s;

    }

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

    public function getSource(  $name, $type = 'json') : ARRAY {

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

        if( isset( $data ) && is_array( $data ) ){
            return $data;
        }
        return [];

    }

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
        return $data;

    }

}
