<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Core/Url.php';
//require_once 'Customweb/Mvc/Controller/IAdapter.php';


/**
 * @author Thomas Hunziker
 *
 */
abstract class Customweb_Mvc_Controller_AbstractAdapter implements Customweb_Mvc_Controller_IAdapter {
	
	/**
	 * This method returns the base URL on which the dispatcher is invoked. 
	 * The URL is used to construct the controller endpoint URLs. 
	 * 
	 * @return string
	 */
	protected abstract function getBaseUrl();
	
	protected abstract function getControllerQueryKey();
	
	protected abstract function getActionQueryKey();
	
	public function getUrl($controllerName, $actionName, array $parameters = array()) {
		$parameters[$this->getControllerQueryKey()] = $controllerName;
		$parameters[$this->getActionQueryKey()] = $actionName;
		return $this->createUrl($parameters);
	}
	
	/**
	 * Creates a URL based on the parameters given and the order context.
	 * Subclasses may override this method to apply SEO optimizations.
	 * 
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @param array $parameters
	 * @return string
	 */
	protected function createUrl(array $parameters) {
		$url = new Customweb_Core_Url($this->getBaseUrl());
		$url->appendQueryParameters($parameters);
		return $url->toString();
	}
	
	public function extractUrlParameters(Customweb_Core_Http_IRequest $request) {
		return $request->getParsedQuery();
	}
	
	public function extractFormData(Customweb_Core_Http_IRequest $request) {
		return $request->getParsedBody();
	}
	
	public function extractControllerName(Customweb_Core_Http_IRequest $request) {
		$parameters = $request->getParameters();
		if (isset($parameters[$this->getControllerQueryKey()])) {
			return $parameters[$this->getControllerQueryKey()];
		}
		throw new Exception("No controller provided in the request.");
	}
	
	public function extractActionName(Customweb_Core_Http_IRequest $request) {
		$parameters = $request->getParameters();
		if (isset($parameters[$this->getActionQueryKey()])) {
			return $parameters[$this->getActionQueryKey()];
		}
		else {
			return 'index';
		}
	}
	
	
	
}