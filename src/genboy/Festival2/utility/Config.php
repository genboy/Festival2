<?php declare(strict_types = 1);
/** src/genboy/Festival2/config.php */
namespace genboy\Festival2\utility;

use pocketmine\utils\Config as FileConfig;

use genboy\Festival2\Festival;

class Config{

	private $plugin;

    public function __construct( Festival $plugin){

        $this->plugin = $plugin;

        $this->loadConfig();

    }
    public function loadConfig() : void{

        // get Festival configurations
        // use data
        $d = $this->plugin->data;
        var_dump( $d->getResources() );
        //$d->saveSource( 'test', ['var1'=> 'test1','var2'=> 'test2'], 'yaml');
        //$this->changeConfig( 'test1', 'test ver 1' );

        $this->plugin->getLogger()->info( "Festival Config loaded" );

    }

    public function changeConfig( $var, $val ){

        $config = new FileConfig($this->plugin->getDataFolder(). "resources" . DIRECTORY_SEPARATOR . "config.json", FileConfig::JSON );
        $config->set( $var, $val );
        $config->save();

        //..
        /*
        //
            $array = $config->getNested($key);
            $array[] = "value";
            //$array is ["value"], considering $array is [].
            $config->setNested($key, $array);
        */

    }
}

