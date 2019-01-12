<?php declare(strict_types = 1);

/** src/genboy/Festival2/Area.php */

namespace genboy\Festival2;

use genboy\Festival2\Festival;


class Level{

	/** @var bool[] */
	public $flags;
	/** @var string */
	private $name;
	/** @var string */
	private $desc;
	/** @var string */
	private $levelName;
	/** @var Main */
	private $plugin;

	public function __construct(string $name, string $desc, array $flags, Festival $plugin){
		$this->name = $name;
		$this->desc = $desc;
		$this->flags = $flags;
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
	 * @return string
	 */
	public function getDesc() : string {
		return $this->desc;
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
			$this->plugin->helper->saveAreas();

			return true;
		}

		return false;
	}

	/**
	 * @param string $flag
	 * @return bool
	 */
	public function toggleFlag(string $flag) : bool{
		if(isset($this->flags[$flag])){
			$this->flags[$flag] = !$this->flags[$flag];
			$this->plugin->saveAreas();

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

	public function delete() : void{
		unset($this->plugin->levels[$this->getName()]);
		$this->plugin->data->saveLevels();
	}

	public function save() : void{
		$this->plugin->levels[strtolower($this->name)] = $this;
	}

}
