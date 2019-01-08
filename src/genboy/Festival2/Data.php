<?php declare(strict_types = 1);
/** src/genboy/Festival2/Data.php
 *
 * global helper
 *
 */
namespace genboy\Festival2;

use genboy\Festival2\Festival;
use genboy\Festival2\Setup;

class Data {

    private $plugin;

    public  $setup;

    public  $config = [];

    public  $levels = [];

    public  $areas = [];

    public function __construct(Festival $plugin){

        $this->plugin = $plugin;

        $this->setup = new Setup($plugin,$this);

    }

    public function loadConfig(): void {
        // load config data
        $this->config = [];
        $data = $this->plugin->helper->getSource( "config" );
        if( isset( $data['options'] ) && is_array( $data['options'] ) ){
            $this->config = $data;
        }
    }

    public function getConfig(){
        return $this->config;
    }

    public function setConfig( $data ){
        $this->config = $data;
    }

    public function saveConfig( $data ){
        $this->config = $data;
        $this->plugin->helper->saveSource( 'config', $data );
    }

    public function loadLevels(): void {
        // load levels data
        $this->levels = [];
        $data = $this->plugin->helper->getSource( "levels" );
        if( isset( $data[0] ) && is_array( $data[0] ) ){
            $this->levels = $data;
        }
    }

    public function getLevels(){
        return $this->levels;
    }

    public function setLevels( $data ){
        $this->levels = $data;
    }

    public function saveLevels( $data ){
        $this->levels = $data;
        $this->plugin->helper->saveSource( 'levels', $data );
    }

    public function loadAreas(): void {
        // load area data
        $data = $this->plugin->helper->getSource( "areas" );
        if( isset( $data[0] ) && is_array( $data[0] ) ){
            $this->areas = $data;
        }
    }

    public function getAreas(){
        return $this->areas;
    }

    public function setAreas( $data ){
        $this->areas = $data;
    }

    public function saveAreas( $data ){
        $this->areas = $data;
        $this->plugin->helper->saveSource( 'areas', $data );
    }

}
