<?php declare(strict_types = 1);

/** src/genboy/Festival2/Area.php */

namespace genboy\Festival2;

use genboy\Festival2\Language;
use genboy\Festival2\Level;
use genboy\Festival2\Flag;

class Config{

	/** @var Main */
	public $options;

	public $defaults;

	private $plugin;

    public function __construct(Main $plugin){

        $this->plugin = $plugin;
        $this->loadConfig();

    }

    public function loadConfig() : void{

        if(!file_exists($this->plugin->getDataFolder() . "resources/" . "config.json")){

            // new install
            $this->makeJSONConfig();

        }else{

            // saved configs
            $this->getJSONConfig();

        }

    }

    public function getJSONConfig() : void{

            $c = json_decode(file_get_contents($this->plugin->getDataFolder() . "resources/" . "config.json"), true);

		    if( isset( $c["options"] ) && is_array( $c["options"] ) ){
                $this->options = $c["options"];
                // check for updates etc.
            }

            if( isset( $c["defaults"] ) && is_array( $c["defaults"] ) ){
                $this->defaults = $c["defaults"];
                // pair new worlds & defaults
            }

            $this->saveConfig();

    }

    public function saveConfig() : void{

        //$c = [ $this->options, $this->defaults ];
        $c = [];
        $c = [ "options" => $this->options, "defaults" => $this->defaults ];
		file_put_contents( $this->plugin->getDataFolder() . 'resources/' . "config.json", json_encode( $c ) );

	}

    /** makeJSONConfig
     * @var options[]
     * @fn checkYMLConfig()
	 */
    public function makeJSONConfig() : void {

            // check old config file
            $this->checkYMLConfig();

            // check important separate values
            if( !isset( $this->options["Language"] ) ){
                $this->options["Language"] = 'en';
            }  // ..

            if( !isset( $this->defaults[1] ) ){
                $this->defaults = [];
            }
            // save new config
            $this->saveConfig();

    }


    /** checkYMLConfig
     * @var options[]
     * @file resources/config.yml
	 */
    public function checkYMLConfig() : void{

        // import from old yml config
        if( file_exists($this->plugin->getDataFolder() . "config.yml") ){
            $c = yaml_parse_file($this->plugin->getDataFolder() . "config.yml"); // some original old defaults
		}else if( file_exists($this->plugin->getDataFolder() . "resources/" . "config.yml") ){
            $c = yaml_parse_file($this->plugin->getDataFolder() . "resources/" . "config.yml"); // the old defaults
		}
        if( isset( $c["Options"] ) && is_array( $c["Options"] ) ){
            // assign old configs
            $this->options = $c["Options"]; // assign old configs
        }
        if( isset( $c["Default"] ) && is_array( $c["Default"] ) ){
            // assign old level configs
            $this->defaults = $c["Default"]; // level flag defaults
        }

    }


}
