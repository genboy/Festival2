<?php declare(strict_types = 1);
/** src/genboy/Festival2/Core.php
 *
 * environment core
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Helper;

use pocketmine\utils\Config;
use pocketmine\Server;

class Core{

	private $plugin;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->plugin->setup->checkStatus(); //var_dump($this->plugin->data['config']);

    }

}

