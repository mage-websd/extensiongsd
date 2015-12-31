<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/String.php';


class Customweb_Mvc_Controller_Exception_ActionArgumentScalarException extends Exception {
	
	private $methodName = null;
	
	public function __construct($methodName) {
		$this->methodName = $methodName;
		parent::__construct(Customweb_Core_String::_("The method '@method' has a sclar parameter value. This is not supported on action methods.")->format(array('@method' => $methodName)));
	}

	public function getMethodName(){
		return $this->methodName;
	}
	
	
}