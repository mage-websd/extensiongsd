<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/IAnnotation.php';



class Customweb_Mvc_Controller_Annotation_Action implements Customweb_IAnnotation {
	
	private $name;
	
	public function getName() {
		return $this->name;
	}
	
	public function setValue($name) {
		$this->name = $name;
		return $this;
	}
	
}