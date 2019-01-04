<?php declare(strict_types = 1);
/** src/genboy/Festival2/Utility/Base.php
 *
 * get base server environment properties, plugins, operators
 *
 */
namespace genboy\Festival2\utility;

use genboy\Festival2\Festival;

use pocketmine\Server;

class Base{

	private $plugin;

	public $properties;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->properties = $this->getProperties();

    }

    public function getProperties() : ARRAY {

        $p = [];

        $p['ver']   = $this->plugin->getServer()->getVersion();
        $p['api']   = $this->plugin->getServer()->getApiVersion();

        return $p;

    }


}

