<?php declare(strict_types = 1);
/**
 * src/genboy/Festival2/FormUI.php
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Area as FeArea;
use genboy\Festival2\CustomUI\CustomForm;
use genboy\Festival2\CustomUI\SimpleForm;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;


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
    public function openUI($user){

        if( $user->hasPermission("festival2.access" ) ){
            $user->sendMessage("Forms in development!"); # Sends to the sender
            $this->plugin->form->selectForm($user);
        }else{
            $user->sendMessage("No permission to use this!"); # Sends to the sender
        }
        return true;

    }

     /** selectForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function selectForm( Player $sender, $msg = false ) : void {
        $form = new SimpleForm(function ( Player $sender, ?int $data ) {
            if( $data === null){
                return;
            }
            switch ($data) {
                case 0:
                    $this->areaSelectForm( $sender );
                break;
                case 1:
                    $this->levelForm( $sender );
                break;
                case 2:
                default:
                    $this->configForm( $sender );
                break;
            }
            return false;
        });

        $form->setTitle("Festival Manager");
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent("Select an option");
        }

        $form->addButton("Area's", 0, "textures/items/sign");
        $form->addButton("Levels", 0, "textures/items/name_tag");
        $form->addButton("Configuration", 0, "textures/blocks/command_block");

        $form->sendToPlayer($sender);

    }


     /** areaSelectForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaSelectForm( Player $sender, $msg = false ) : void {
        $form = new SimpleForm(function ( Player $sender, ?int $data ) {
            if( $data === null){
                return;
            }
            switch ($data) {
                case 0:
                    $this->areaEditForm( $sender );
                break;
                case 1:
                    $this->areaNewForm( $sender ); // new
                break;
                case 2:
                    $this->selectForm( $sender ); // del
                break;
                default:
                    $this->selectForm( $sender );
                break;
            }
            return false;
        });

        $form->setTitle("Festival Area Manager");
        if($msg){
            $form->setContent($msg);
        }else{
            $form->setContent("Select an option");
        }

        // select area
        $form->addButton("Edit an area", 0, "textures/items/sign");

        // new area
        $form->addButton("Create new area", 0, "textures/blocks/stonebrick_carved");
        // delete area
        $form->addButton("Delete an area", 0, "textures/blocks/command_block");

        $form->addButton("Go back");

        $form->sendToPlayer($sender);

    }

    /** configForm
     * @class formUI
	 * @param Player $sender
     */
    public function configForm( Player $sender ) : void {

        $form = new CustomForm(function ( Player $sender, ?array $data ) {
            if( $data === null){ // catch data and do something
                return;
            }
            //var_dump($data);

            $this->plugin->config["options"]["itemid"] = $data["itemid"];

            $msgpos_opt = ["msg", "title", "tip", "pop"];
            $this->plugin->config["options"]["msgpos"] = $msgpos_opt[ $data["msgpos"] ];

            $msgdsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["msgdsp"] = $msgdsp_opt[ $data["msgdsp"] ];

            $areadsp_opt = ["on", "op", "off"];
            $this->plugin->config["options"]["areadsp"] = $areadsp_opt[ $data["areadsp"] ];

            $newautolist = "off";
            if(  $data["autolist"] == true){
                $newautolist = "on";
            }

            $c = 5; // after 5 options all input are flags
            foreach( $this->plugin->config["defaults"] as $flag => $set){
                $c++;
                $defaults[$flag] = $data[$c];
            }

            $this->plugin->config["defaults"] = $defaults;
            $this->plugin->helper->saveConfig( $this->plugin->config );

            $msg = "Configs saved!";
            $this->selectForm($sender, $msg);

        });

        $optionset = $this->plugin->config["options"];

        $form->setTitle("Festival Configuration");
        $form->addLabel("Config Options & default flags");

        $msgpos_tlt = "Area messages position";
        $msgpos_opt = ["msg", "title", "tip", "pop"];
        $msgpos_slc = array_search( $optionset["msgpos"], $msgpos_opt);
        $form->addStepSlider( $msgpos_tlt, $msgpos_opt, $msgpos_slc, "msgpos" );

        $msgdsp_tlt = "Area messages visible";
        $msgdsp_opt = ["on", "op", "off"];
        $msgdsp_slc = array_search( $optionset["msgdsp"], $msgdsp_opt);
        $form->addStepSlider( $msgdsp_tlt, $msgdsp_opt, $msgdsp_slc, "msgdsp" );

        $areadsp_tlt = "Area titles visible";
        $areadsp_opt = ["on", "op", "off"];
        $areadsp_slc = array_search( $optionset["areadsp"], $areadsp_opt);
        $form->addStepSlider( $areadsp_tlt, $areadsp_opt, $areadsp_slc, "areadsp" );

        $autolist = false;
        if( $optionset["autolist"] == "on"){
            $autolist = true;
        }
        $form->addToggle("Auto whitelist", $autolist, "autolist" );

        $nr = $optionset['itemid'];
        $form->addInput( "Action item", "block itemid", "$nr", "itemid" );


        foreach( $this->plugin->config["defaults"] as $flag => $set){
            $form->addToggle( $flag, $set );
        }
        $form->sendToPlayer($sender);

    }


    /** areaEditForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaEditForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false && isset( $input["selectedArea"] ) ){

            // manage area flags etc.
            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $areaname;

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                    $areaname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                }
                if( isset( $this->plugin->areas[ $areaname ] ) ){

                    $area = $this->plugin->areas[ $areaname ];

                    if( isset( $data["newareadesc"] ) && !empty( $data["newareadesc"] ) ){
                        $area->setDesc( $data["newareadesc"] );
                    }
                    $c = 1;
                    $flagset = $area->getFlags();
                    foreach( $flagset as $nm => $set){
                        if( isset( $data[$c] ) ){
                            $area->setFlag( $nm, $data[$c] );
                        }
                        $c++;
                    }
                    $area->save();
                    $this->plugin->helper->saveAreas();
                    $this->selectForm( $sender, "Area ". $areaname . " saved! Select an option"  );
                }else{
                    $this->areaForm( $sender, "Area ". $areaname . " not found! Try again, select an option" );
                }
                return false;
            });

            $areasnames = $this->plugin->helper->getAreaNameList();
            $areaname = $areasnames[$input["selectedArea"]];
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area " . TextFormat::DARK_PURPLE . $areaname );
            $form->addInput("Description", "Area description", $this->plugin->areas[$areaname]->getDesc(), "newareadesc" );
            $flgs = $this->plugin->areas[$areaname]->getFlags();
            foreach( $flgs as $flag => $set){
                $form->addToggle( $flag, $set );
            }
            $form->sendToPlayer($sender);

        }else{

            // select area
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->areaEditForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage area's");
            if($msg){
                $form->addLabel( $msg);
            }

            $areasnames = $this->plugin->helper->getAreaNameList( $sender, true );
            $options = $areasnames[0];
            $slct = $areasnames[1];
            $form->addDropdown( "Select to edit an area", $options, $slct, "selectedArea");
            $form->sendToPlayer($sender);
       }
    }

    /** areaNewForm
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function areaNewForm( Player $sender , $input = false, $msg = false) : void {

        if( $input != false ){

            if( isset($input["type"]) && ( !isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["newname"] ) ||  $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["newname"] == "" ) ){

                $form = new CustomForm(function ( Player $sender, ?ARRAY $data ) {
                    if( $data === null){

                    }else{

                        if( isset( $data["name"] ) && !isset( $this->plugin->areas[ $data["name"] ] ) ){

                            //check and save area
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["name"] = $data["name"];
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["desc"] = $data["desc"];

                            $newarea = $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"];
                            var_dump($this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]);
                            unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"] );

                            $newarea["level"] = strtolower( $sender->getLevel()->getName() );

                            if( isset( $this->plugin->levels[ strtolower( $newarea["level"]) ] ) ){
                                $level = $this->plugin->levels[ strtolower( $newarea["level"]) ];
                                $newarea["flags"] = $level->getFlags();
                            }else{
                                $newarea["flags"] = $this->plugin->defaults;
                            }

                            new FeArea( $newarea["name"], $newarea["desc"], $newarea["flags"], $newarea["pos1"], $newarea["pos2"], intval( $newarea["radius"] ), $newarea["level"], [], [], [], $this->plugin);
                            $this->plugin->helper->saveAreas();
                            $sender->sendMessage("New area named ".$newarea["name"]." created!");

                        }else{

                            $this->areaNewForm( $sender , $data, $msg = "New area name not correct or allready used. Please try another name:");

                        }
                    }
                });

                $form->setTitle("Festival Area Maker");
                if($msg){
                    $form->addLabel($msg);
                }else{
                    $form->addLabel("Create area");
                }
                $form->addInput( "Area name", "area name", "", "name" );

                $form->addInput( "Area decription", "area description", "", "desc" );

                $form->sendToPlayer($sender);


            }
        }else{

            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"] = [];
            // simple form select cube or sphere
            $form = new SimpleForm(function ( Player $sender, ?int $data ) {
                if( $data === null){

                }else{
                    switch ($data) {
                        case 0:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "cube";
                            $o = TextFormat::GREEN . "Tab position 1 for new cube area (right mouse block place)"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                            //$this->areaNewForm( $sender, ["type"=>"cube"], "Selecting positions" );
                            //Server::getInstance()->dispatchCommand($sender, "fc pos1");
                            $sender->sendMessage($o);
                        break;
                        case 1:
                            $this->plugin->players[ strtolower( $sender->getName() ) ]["makearea"]["type"] = "sphere";
                            $o = TextFormat::GREEN . "Tab the center position for the new sphere area (right mouse block place)"; //$o = TextFormat::GREEN . "Please place or break the first position.";
                            //$this->areaNewForm( $sender, ["type"=>"sphere"], "Select centre and radius" ); // new
                            //Server::getInstance()->dispatchCommand($sender, "fc pos1");
                            $sender->sendMessage($o);
                        break;
                        case 2:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                        default:
                            $this->areaSelectForm( $sender ); // goback
                        break;
                    }
                }
            });

            $form->setTitle("Festival Area Maker");
            if($msg){
                $form->setContent($msg);
            }else{
                $form->setContent("Select new area type");
            }

            // cube area
            $form->addButton("Cube area (select 2 positions)");

            // sphere area
            $form->addButton("Sphere Area (select center and radius)");

            $form->addButton("Go back");

            $form->sendToPlayer($sender);

        }
    }

    /** levelForm  (prototype function setup)
     * @class formUI
	 * @param Player $sender
	 * @param string $msg
     */
    public function levelForm( Player $sender , $inputs = false, $msg = false) : void {

        if( $inputs != false && isset( $inputs["selectedLevel"] ) ){
            // manage level flags
            $levels = $this->plugin->helper->getServerWorlds();
            $levelname = $levels[ $inputs["selectedLevel"] ];
            $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] = $levelname;

            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $levels = $this->plugin->helper->getServerWorlds();
                if( isset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] ) ){
                    $levelname = $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"];
                    unset( $this->plugin->players[ strtolower( $sender->getName() ) ]["edit"] );
                }
                if( isset( $this->plugin->levels[ $levelname ] ) ){
                    $lvl = $this->plugin->levels[ $levelname ];
                    $flagset = $lvl->getFlags();
                    $c = 0;
                    foreach( $flagset as $nm => $set){
                        if( isset( $data[$c] ) ){
                            $lvl->setFlag( $nm, $data[$c] );
                        }
                        $c++;
                    }
                    $lvl->save();
                    $this->plugin->helper->saveLevels();
                    $this->selectForm( $sender, "Level ". $levelname . " flagset saved! Select an option"  );
                }else{
                    // add new level configs?
                    $worlds = $this->plugin->helper->getServerWorlds();
                    if( in_array( $levelname, $worlds ) ){
                        var_dump($data);
                        $this->levelForm( $sender, false, "Level ". $levelname . " not found! Try again, select an option" );

                    }else{
                        $this->levelForm( $sender, false, "Level ". $levelname . " not found! Try again, select an option" );
                    }
                }
                return false;
            });

            $levels =$this->plugin->helper->getServerWorlds();
            $levelname = $levels[$inputs["selectedLevel"]];
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage level flags " . TextFormat::DARK_PURPLE . $levelname );
            $flgs = $this->plugin->levels[$levelname]->getFlags();
            foreach( $flgs as $flag => $set){
                $form->addToggle( $flag, $set );
            }
            $form->sendToPlayer($sender);

        }else{
            // select level
            $form = new CustomForm(function ( Player $sender, ?array $data ) {
                if( $data === null){
                    return;
                }
                $this->levelForm( $sender, $data );
                return false;
            });
            $form->setTitle( TextFormat::DARK_PURPLE . "Manage levels");
            if( $msg ){
                $form->addLabel( $msg );
            }

            $levels = $this->plugin->helper->getServerWorlds();
            $current = strtolower( $sender->getLevel()->getName() );
            $slct = array_search( $current, $levels);
            $form->addDropdown( "Level select", $levels, $slct, "selectedLevel");
            $form->sendToPlayer($sender);
       }
    }


}
