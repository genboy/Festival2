<?php declare(strict_types = 1);
/**
 * src/genboy/Festival2/FormUI.php
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;

use genboy\Festival2\CustomUI\CustomForm;
use genboy\Festival2\CustomUI\SimpleForm;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class FormUI{

    private $plugin;

    /** __construct
	 * @param Festival
     */
	public function __construct(Festival $plugin){
		$this->plugin = $plugin;
	}

    /** openUI
     * @class formUI
     * @func formUI->selectForm
	 * @param Player $user
     */
    public function openUI( $user){
        if( $user->hasPermission("festival2.access" ) ){
            $user->sendMessage("Forms in development!"); # Sends to the sender
            //$this->plugin->form->selectForm($user);
        }else{
            $user->sendMessage("No permission to use this!"); # Sends to the sender
        }
    }


}
