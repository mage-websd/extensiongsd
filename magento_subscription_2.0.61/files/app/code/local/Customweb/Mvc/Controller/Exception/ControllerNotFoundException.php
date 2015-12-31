<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/String.php';


class Customweb_Mvc_Controller_Exception_ControllerNotFoundException extends Exception {
	
	private $controllerName = null;
	
	public function __construct($controllerName) {
		$this->controllerName = $controllerName;
		parent::__construct(Customweb_Core_String::_("No controller found for controller name '@controllerName'.")->format(array('@controllerName' => $controllerName)));
	}
	
	public function getControllerName() {
		return $this->controllerName;
	}
}