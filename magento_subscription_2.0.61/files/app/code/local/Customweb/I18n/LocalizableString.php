<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/String.php';
//require_once 'Customweb/I18n/ILocalizableString.php';
//require_once 'Customweb/I18n/Translation.php';


/**
 * This class
 * 
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_I18n_LocalizableString implements Customweb_I18n_ILocalizableString {
	
	private $string = null;
	private $arguments = array();
	
	public function __construct($string, $args = array()) {
		if ($string instanceof Customweb_I18n_ILocalizableString) {
			$this->string = $string->getUntranslatedString();
			$this->arguments = $string->getArguments();
		}
		else {
			
			
			$this->string = $string;
			$this->arguments = $args;
		}
	}
	
	public function getUntranslatedString() {
		return $this->string;
	}
	
	public function getArguments() {
		return $this->arguments;
	}
	
	public function __toString() {
		return $this->toString();
	}
	
	public function toString() {
		return Customweb_I18n_Translation::getInstance()->translate($this->string, $this->arguments);
	}
	
	
}