<?php declare(strict_types = 1);
/** src/genboy/Festival/Language.php */
namespace genboy\Festival2;

class Language{
	public static $instance;
	public function __construct($owner, $langjson){
		$this->owner = $owner;
		$this->trans = $langjson;
		self::$instance = $this;
	}
	static function translate($key){
		$txt = self::$instance->trans[$key];
		if (strpos($txt, "%n") != false) {
			$text = str_replace("%n", "\n", $txt);
		} else {
			$text = $txt;
		}
		return $text;
	}
}
?>
