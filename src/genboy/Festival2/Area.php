<?php declare(strict_types = 1);

/** src/genboy/Festival2/Area.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;

use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Area{

	/** @var bool[] */
	public $flags;
	/** @var string */
	private $name;
	/** @var string */
	public $desc;
	/** @var Vector3 */
	private $pos1;
	/** @var Vector3 */
	private $pos2;
	/** @var Radius */
	private $radius;
	/** @var string */
	private $levelName;
	/** @var string[] */
	private $whitelist;
	/** @var string[] */
	public $commands;
	/** @var string[] */
	public $events;
	/** @var Main */
	private $plugin;

	public function __construct(string $name, string $desc, array $flags, Vector3 $pos1, Vector3 $pos2, int $radius, string $levelName, array $whitelist, array $commands, array $events, Festival $plugin){
		$this->name = $name;
		$this->desc = $desc;
		$this->flags = $flags;
		$this->pos1 = $pos1;
		$this->pos2 = $pos2;
		$this->radius = $radius;
		$this->levelName = $levelName;
		$this->whitelist = $whitelist;
		$this->commands = $commands;
		$this->events = $events;
		$this->plugin = $plugin;
		$this->save();
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * @param string
	 */
	public function setName( $str ) : void {
		$this->name = $str;
	}

	/**
	 * @return string
	 */
	public function getDesc() : string {
		return $this->desc;
	}

    /**
	 * @param string
	 */
	public function setDesc( $str ) : void {
		$this->desc = $str;
	}

	/**
	 * @return Vector3
	 */
	public function getFirstPosition() : Vector3{
		return $this->pos1;
	}

	/**
	 * @return Vector3
	 */
	public function getSecondPosition() : Vector3{
		return $this->pos2;
	}

    /**
	 * @param int
	 */
	public function setRadius( $int ) : int{
		$this->radius = $int;
	}

	/**
	 * @return Vector3
	 */
	public function getRadius() : int{
		return $this->radius;
	}

	/**
	 * @return string[]
	 */
	public function getFlags() : array{
		return $this->flags;
	}

	/**
	 * @param string $flag
	 * @return bool
	 */
	public function getFlag(string $flag) : bool{
		if(isset($this->flags[$flag])){
			return $this->flags[$flag];
		}
		return false;
	}

	/**
	 * @param string $flag
	 * @param bool   $value
	 * @return bool
	 */
	public function setFlag(string $flag, bool $value) : bool{
		if(isset($this->flags[$flag])){
			$this->flags[$flag] = $value;
			return true;
		}

		return false;
	}


	/**
	 * @return string[]
	 * @return array
	 */
	public function getCommands() : array{

		$arr = [];
		if(is_array($this->commands)){
			foreach($this->commands as $id => $cmd){
				if( $cmd != '' && $cmd != ' ' && $cmd != 'null' ){
					$arr[$id] = $cmd;
				}
			}
		}
		return $arr;
	}

	/**
	 * @param string $flag
	 * @return bool
	 */
	public function getCommand(string $id) : bool{
		if(isset($this->commands[$id])){
			return $this->commands[$id];
		}

		return false;
	}


	/**
	 * @return string[]
	 * @return array
	 */
	public function getEvents() : array{

		$arr = [];
		if(is_array($this->events)){
			foreach($this->events as $nm => $ids){
				if( $ids != '' && $ids != ' ' && $ids != 'null' ){
					$arr[$nm] = $ids;
				}
			}
		}

		return $arr;
	}

	/**
	 * @return string[]
	 */
	public function setEvent( string $type, string $cmdid) : array{
		return true;
	}


	/**
	 * @param Vector3 $pos
	 * @param string  $levelName
	 * @return bool
	 */
	public function contains(Vector3 $pos, string $levelName) : bool{

        if( isset( $this->radius ) &&  $this->radius > 1 && isset( $this->pos1 ) ){
            // in sphere area
            return ( $pos->getX() >= ( $this->pos1->getX() - $this->radius ) && $pos->getX() <= ( $this->pos1->getX() + $this->radius ) && $pos->getY() >= ( $this->pos1->getY() - $this->radius ) && $pos->getY() <= ( $this->pos1->getY() + $this->radius ) && $pos->getZ() >= ( $this->pos1->getZ() - $this->radius ) && $pos->getZ() <= ( $this->pos1->getZ() + $this->radius ) );

        }else if( isset( $this->pos1 ) && isset( $this->pos2 ) ){
            // in cube area
            return ((min($this->pos1->getX(), $this->pos2->getX()) <= $pos->getX()) && (max($this->pos1->getX(), $this->pos2->getX()) >= $pos->getX()) && (min($this->pos1->getY(), $this->pos2->getY()) <= $pos->getY()) && (max($this->pos1->getY(), $this->pos2->getY()) >= $pos->getY()) && (min($this->pos1->getZ(), $this->pos2->getZ()) <= $pos->getZ()) && (max($this->pos1->getZ(), $this->pos2->getZ()) >= $pos->getZ()) && ($this->levelName === $levelName));
        }
	}

	/**
	 * @param Vector3 $pos
	 * @param string  $levelName
	 * @return bool
	 */
	public function centerContains(Vector3 $pos, string $levelName) : bool{

        if( isset( $this->radius ) &&  $this->radius > 1 && isset( $this->pos1 ) ){
            // in sphere area
            return ( $pos->getX() >= ( $this->pos1->getX() - 2 ) && $pos->getX() <= ( $this->pos1->getX() + 2 ) && $pos->getY() >= ( $this->pos1->getY() - 2 ) && $pos->getY() <= ( $this->pos1->getY() + 2 ) && $pos->getZ() >= ( $this->pos1->getZ() - 2 ) && $pos->getZ() <= ( $this->pos1->getZ() + 2 ) );

        }else if( isset( $this->pos1 ) && isset( $this->pos2 ) ){
            $cx = $this->pos2->getX() + ( ( $this->pos1->getX() - $this->pos2->getX() ) / 2 );
            $cz = $this->pos2->getZ() + ( ( $this->pos1->getZ() - $this->pos2->getZ() ) / 2 );
            $cy1 = min( $this->pos2->getY(), $this->pos1->getY());
            $cy2 = max( $this->pos2->getY(), $this->pos1->getY());
            $px = $pos->getX();
            $py = $pos->getY();
            $pz = $pos->getZ();
            return( $px >= ($cx - 1) && $px <= ($cx + 1) && $pz >= ($cz - 1) && $pz <= ($cz + 1) && $py >= $cy1 && $py <= $cy2 && ($this->levelName === $levelName) );
        }

	}

	/**
	 * @param string $flag
	 * @return bool
	 */
	public function toggleFlag(string $flag) : bool{
		if(isset($this->flags[$flag])){
			$this->flags[$flag] = !$this->flags[$flag];
			$this->plugin->helper->saveAreas();

			return $this->flags[$flag];
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getLevelName() : string{
		return $this->levelName;
	}

	/**
	 * @return null|Level
	 */
	public function getLevel() : ?Level{
		return $this->plugin->getServer()->getLevelByName($this->levelName);
	}

	/**
	 * @param string $playerName
	 * @return bool
	 */
	public function isWhitelisted(string $playerName) : bool{
		if(in_array($playerName, $this->whitelist)){
			return true;
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param bool   $value
	 * @return bool
	 */
	public function setWhitelisted(string $name, bool $value = true) : bool{
		if($value){
			if(!in_array($name, $this->whitelist)){
				$this->whitelist[] = $name;
				$this->plugin->helper->saveAreas();

				return true;
			}
		}else{
			if(in_array($name, $this->whitelist)){
				$key = array_search($name, $this->whitelist);
				array_splice($this->whitelist, $key, 1);
				$this->plugin->helper->saveAreas();

				return true;
			}
		}

		return false;
	}

	/**
	 * @return string[]
	 */
	public function getWhitelist() : array{
		return $this->whitelist;
	}

	public function delete() : void{
		unset($this->plugin->areas[$this->getName()]);
		$this->plugin->helper->saveAreas();
	}

	public function save() : void{
		$this->plugin->areas[$this->name] = $this;
	}

}
