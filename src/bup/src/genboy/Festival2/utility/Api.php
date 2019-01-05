<?php declare(strict_types = 1);
/** src/genboy/Festival2/config.php */
namespace genboy\Festival2\utility;

use genboy\Festival2\Festival;

class Api{

	private $plugin;

	private $controls;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->loadApi();

    }
    public function loadApi() : void{
        // get Festival functions
        $this->plugin->getLogger()->info( "Festival Api loaded" );
    }
}

