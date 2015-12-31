<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/String.php';


class Customweb_Mvc_Controller_Exception_ActionArgumentNotResolvableException extends Exception {
	
	private $methodName = null;
	private $type = null;
	
	public function __construct($methodName, $type) {
		$this->methodName = $methodName;
		$this->type = $type;
		parent::__construct(Customweb_Core_String::_("The parameter '@parameter' on method '@method' could not be resolved.")->format(array('@method' => $methodName, '@parameter' => $type)));
	}

	public function getMethodName(){
		return $this->methodName;
	}

	public function getType(){
		return $this->type;
	}
	
	
}