<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Mvc/Controller/IAdapter.php';


/**
 * This interface defines the methods required to access configurations 
 * required to access the controllers.
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Payment_Endpoint_IAdapter extends Customweb_Mvc_Controller_IAdapter {
	
	/**
	 * Returns a renderer for form. 
	 * 
	 * @return Customweb_Form_IRenderer
	 */
	public function getFormRenderer();	
	
}