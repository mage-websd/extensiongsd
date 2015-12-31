<?php 
/**
 ::[Header]::
 */



/**
 * This interface defines the methods required to access configurations 
 * required to access the controllers.
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Mvc_Controller_IDispatcher {
	
	/**
	 * This method calls based on the given request the action on the controller.
	 *
	 * @param Customweb_Core_Http_IRequest $request
	 * @return Customweb_Core_Http_IResponse
	 */
	public function dispatch(Customweb_Core_Http_IRequest $request);
	
	/**
	 * This method invokes given controller and action with the request object given.
	 *
	 * @param Customweb_Core_Http_IRequest $request
	 * @param string $controllerName
	 * @param string $actionName
	 * @return Customweb_Core_Http_IResponse
	 */
	public function invokeControllerAction(Customweb_Core_Http_IRequest $request, $controllerName, $actionName);
	
}
