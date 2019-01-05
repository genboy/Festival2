<?php declare(strict_types = 1);
/** src/genboy/Festival/EventListener.php */

namespace genboy\Festival2;

use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use genboy\Festival2\Festival;
use genboy\Festival2\Helper;

class EventListener implements Listener{

    private $plugin;

	public function __construct(Festival $plugin){
		$this->plugin = $plugin;
	}

}
