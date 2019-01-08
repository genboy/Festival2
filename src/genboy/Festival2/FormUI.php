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

	public function __construct(Festival $plugin){

		$this->plugin = $plugin;

	}

    public function openUI( $user){
        if( $user->hasPermission("festival2.access" ) ){
            $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival2");
            if ($plug === null || $plug->isDisabled() ) {
                $user->sendMessage("Festival Board TEST needs Festival2 plugin (https://github.com/genboy/Festival)");
            }else{
                $this->plugin->form->selectForm($user);
            }
        }else{
            $user->sendMessage("No permission to use this!"); # Sends to the sender
        }
    }

    public function openConfig( $user){
        if( $user->hasPermission("festival2.access" ) ){ // might be other permissions needed
            $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival2");
            if ($plug === null || $plug->isDisabled() ) {
                $user->sendMessage("Festival Board TEST needs Festival2 plugin (https://github.com/genboy/Festival)");
            }else{
                   $this->plugin->form->configForm($user);
            }
        }else{
            $user->sendMessage("No permission to use this!"); # Sends to the sender
        }
    }

    public function selectForm( Player $sender ) : void {

        $form = new SimpleForm(function ( Player $sender, ?int $data ) {

            // catch data and do something
            if( $data === null){
                return;
            }

            switch ($data) {

                case 0:
                $sender->sendMessage("test button 1");
                break;
                case 1:
                $this->areaTPForm($sender);
                break;
                case 2:
                $this->configForm($sender);
                break;

            }
            return false;
        });

        $form->setTitle("Form select Title");
        $form->setContent("Some description Text");

        $form->addButton("Manage area");
        $form->addButton("TP to area");
        $form->addButton("Config test");
        $form->sendToPlayer($sender);

    }



    public function areaTPForm( Player $sender ) : void {

        $form = new CustomForm(function ( Player $sender, ?array $data ) {

            // catch data and do something
            if( $data === null){
                return;
            }
            //var_dump($data); // Sends all data to console
            if( $data[1] != 0 ){
                //$plug = Server::getInstance()->getPluginManager()->getPlugin("Festival2");
                $selectlist = array();
                foreach($this->plugin->areas as $area){
                    $selectlist[]= strtolower( $area->getName() );
                }
                $area = $selectlist[ ( $data[1] - 1 ) ];
                Server::getInstance()->dispatchCommand($sender, "fe tp ".$area );
            }else if( $data[2] == 0 ){
                $this->selectForm($sender);
            }else if( $data[2] == 1 ){
                $sender->sendMessage("Festival Board option2");
            }
        });

        $form->setTitle("Form Test Title");
        $form->addLabel("Some description Text");

        //$plug = Server::getInstance()->getPluginManager()->getPlugin("Festival2");
        $selectlist = array();
        $selectlist[]= "Select destination";
        foreach($this->plugin->areas as $area){
            $selectlist[]= strtolower( $area->getName() );
        }

        $form->addDropdown("TP to area", $selectlist ); // Dropdowm Data $selectlist

        $form->addDropdown("More actions", ["Go back", "Option2"]); // Dropdowm, Options 1, 2 & 3

        $form->sendToPlayer($sender);  // $sender->sendForm($form);

    }




    public function configForm( Player $sender ) : void {

        $form = new CustomForm(function ( Player $sender, ?array $data ) {

            // catch data and do something
            if( $data === null){
                return;
            }
            var_dump($data); // Sends all data to console
            $c = 4;
            $defaults = [];
            foreach( $this->plugin->data->config["defaults"] as $flag => $set){
                $c++;
                $defaults[$flag] = $data[$c];
            }
            $this->plugin->data->config["defaults"] = $defaults;
            $this->plugin->data->saveConfig( $this->plugin->data->config );
        });

        $form->setTitle("Festival Configuration (test)");
        $form->addLabel("Set config options and default flags");


        $form->addStepSlider("Message position", ["msg", "title", "tip", "pop"] );
        $form->addStepSlider("Area titles visible", ["on", "op", "off"] );
        $form->addStepSlider("Area message display", ["on", "op", "off"] );

        $form->addToggle("Auto whitelist", $this->plugin->data->config["options"]['autolist'] );

        foreach( $this->plugin->data->config["defaults"] as $flag => $set){
            $form->addToggle( $flag, $set );
        }
        /*$form->addDropdown("TP to area", $selectlist ); // Dropdowm, Options 1, 2 & 3


        $form->addToggle("Toggle");
        $form->addToggle("Toggle2");
        $form->addToggle("Toggle3");
        $form->addSlider("Slider", 1, 100); // Slider, Min 1, Max 100
        $form->addStepSlider("Step Slider", ["5", "10", "15"]); // Step Slider, 5, 10 & 15
        $form->addDropdown("Dropdown", ["1", "2", "3"]); // Dropdowm, Options 1, 2 & 3
        $form->addInput("Input", "Ghost Text", "Text"); // Input, Text already entered
*/

        $form->sendToPlayer($sender);  // $sender->sendForm($form);

    }

    /*
    public function testCustomForm( Player $sender ) : void {

        $form = new CustomForm(function ( Player $sender, ?array $data ) {

            // catch data and do something
            if( $data === null){
                return;
            }
            //var_dump($data); // Sends all data to console
            if( $data[1] != 0 ){
                //$plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
                $selectlist = array();
                foreach($this->plugin->areas as $area){
                    $selectlist[]= strtolower( $area->getName() );
                }
                $area = $selectlist[ ( $data[1] - 1 ) ];
                Server::getInstance()->dispatchCommand($sender, "fe tp ".$area );
            }
        });

        $form->setTitle("Form Test Title");
        $form->addLabel("Some description Text");

        //$plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
        $selectlist = array();
        $selectlist[]= "Select destination";
        foreach($this->plugin->areas as $area){
            $selectlist[]= strtolower( $area->getName() );
        }
        $form->addDropdown("TP to area", $selectlist ); // Dropdowm, Options 1, 2 & 3


        $form->addToggle("Toggle");
        $form->addToggle("Toggle2");
        $form->addToggle("Toggle3");
        $form->addSlider("Slider", 1, 100); // Slider, Min 1, Max 100
        $form->addStepSlider("Step Slider", ["5", "10", "15"]); // Step Slider, 5, 10 & 15
        $form->addDropdown("Dropdown", ["1", "2", "3"]); // Dropdowm, Options 1, 2 & 3
        $form->addInput("Input", "Ghost Text", "Text"); // Input, Text already entered


        $form->sendToPlayer($sender);  // $sender->sendForm($form);

    }*/


}
