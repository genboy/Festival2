<?php declare(strict_types = 1);
/**
 * src/genboy/Festival/Festival.php
 *
 * Main class plugin
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\utility\Base;
use genboy\Festival2\utility\Config;
use genboy\Festival2\utility\Api;
use genboy\Festival2\utility\Data;
use genboy\Festival2\utility\EventListener;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Festival extends PluginBase implements Listener {

    // obj for server environment
    public  $base;

    // obj for data source acces
    public  $data;

    // obj for configurations
    public  $config;

    // obj for application control
    public  $api;

    // status string for install & update routes
    public $status;

    public function onLoad() : void {

        $this->status = ['loading', 'config'];

	}

	public function onEnable() : void {

        $this->loadBase();
        $this->loadData();
        $this->loadConfig();
        $this->loadApi();
        $this->loadListener();


        if( $this->status[0] == 'ready' ){ //var_dump($this->status);

            $this->getLogger()->info( "Festival 2 (in development) enabled & ready " );

        }else{

            $this->getLogger()->info( "Festival 2 ". $this->status[0] .":". $this->status[1] ." (in development)" );

        }


    }

    public function onDisable() : void {

        $this->getLogger()->info( "Festival 2 disabled" );

	}


    /** load Server Environment
	 */
    public function loadBase(){

        $this->base = new Base($this);

    }

    /** load Data
	 */
    public function loadData(){

        $this->data = new Data($this);

    }

    /** load Configs
	 */
    public function loadConfig(){

        $this->config = new Config($this);

    }


    /** load Api
	 */
    public function loadApi(){

        $this->api = new Api($this);

    }


    /** load Listeners
	 */
    public function loadListener(){

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

    }

}
