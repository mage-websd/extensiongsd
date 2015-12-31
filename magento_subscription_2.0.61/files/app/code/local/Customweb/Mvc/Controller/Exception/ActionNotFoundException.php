<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/String.php';


class Customweb_Mvc_Controller_Exception_ActionNotFoundException extends Exception {
	
	private $actionName = null;
	
	public function __construct($actionName) {
		$this->actionName = $actionName;
		parent::__construct(Customweb_Core_String::_("No action found for action name '@actionName'.")->format(array('@actionName' => $actionName)));
	}

	public function getActionName(){
		return $this->actionName;
	}
	
	
}