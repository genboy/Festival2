<?php declare(strict_types = 1);
/** src/genboy/Festival2/Api.php
 *
 * global helper
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Level as FeLevel;
use genboy\Festival2\Area as FeArea;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

class Api {

    private $plugin;

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->loadApi();

    }

    public function loadApi(){

        $this->useAreas();

    }

    public function useAreas(){

        $adata = $this->plugin->data->getAreas();

        if( isset($adata) && is_array($adata) ){
            foreach($adata as $datum){
                new FeArea($datum["name"], $datum["desc"], $datum["flags"], new Vector3($datum["pos1"]["0"], $datum["pos1"]["1"], $datum["pos1"]["2"]), new Vector3($datum["pos2"]["0"], $datum["pos2"]["1"], $datum["pos2"]["2"]), $datum["level"], $datum["whitelist"], $datum["commands"], $datum["events"], $this->plugin);
            }
        }


    }

    /** Save areas
	 * @var obj Festival
	 * @var obj FestArea(Area)
	 * @file areas.json
	 */
	public function saveAreas() : void{

		$areas = [];
        foreach($this->plugin->areas as $area){
            $areas[] = ["name" => $area->getName(), "desc" => $area->getDesc(), "flags" => $area->getFlags(), "pos1" => [$area->getFirstPosition()->getFloorX(), $area->getFirstPosition()->getFloorY(), $area->getFirstPosition()->getFloorZ()] , "pos2" => [$area->getSecondPosition()->getFloorX(), $area->getSecondPosition()->getFloorY(), $area->getSecondPosition()->getFloorZ()], "level" => $area->getLevelName(), "whitelist" => $area->getWhitelist(), "commands" => $area->getCommands(), "events" => $area->getEvents()];
            $this->areaList[strtolower( $area->getName() )] = $area; // name associated area list for inArea check
        }
        $this->plugin->helper->saveSource( "areas", json_encode($areas) );
        //file_put_contents($this->plugin->getDataFolder() . "areas.json", json_encode($areas));
    }

}
