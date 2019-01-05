<?php declare(strict_types = 1);
/**
 * src/genboy/Festival/Festival.php
 *
 * Main class plugin
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Setup;
use genboy\Festival2\Core;
use genboy\Festival2\EventListener;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Festival extends PluginBase implements Listener {

    // obj for status control
    public  $setup;

    // obj for configurations
    public  $core;

    // obj for data control
    public  $helper;

    // obj for data storage
    public  $data;

    // obj for application control
    public  $api;

    public function onLoad() : void {

	}

	public function onEnable() : void {

        $this->setup = new Setup($this);

        $this->core = new Core($this);

        $this->getLogger()->info( "Festival 2 (in development) enabled & ready" );

    }

    public function onDisable() : void {

        $this->getLogger()->info( "Festival 2 disabled" );

	}

}
