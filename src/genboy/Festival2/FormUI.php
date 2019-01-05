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
                $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
                $selectlist = array();
                foreach($plug->areas as $area){
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

        $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
        $selectlist = array();
        $selectlist[]= "Select destination";
        foreach($plug->areas as $area){
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
            //var_dump($data); // Sends all data to console
            if( $data[1] != 0 ){
                $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
                $selectlist = array();
                foreach($plug->areas as $area){
                    $selectlist[]= strtolower( $area->getName() );
                }
                $area = $selectlist[ ( $data[1] - 1 ) ];
                Server::getInstance()->dispatchCommand($sender, "fe tp ".$area );
            }
        });

        $form->setTitle("Form Test Title");
        $form->addLabel("Some description Text");

        $plug = Server::getInstance()->getPluginManager()->getPlugin("Festival");
        $selectlist = array();
        $selectlist[]= "Select destination";
        foreach($plug->areas as $area){
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

    }

}
