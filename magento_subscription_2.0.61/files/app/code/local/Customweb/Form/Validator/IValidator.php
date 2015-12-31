<?php 
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2015 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

/**
 * This interface defines the methods required to use a class
 * as a valitor. A validator can check if a given user input
 * is valid or not. The validation is done with JavaScript
 * in the browser.
 * 
 * @author hunziker
 *
 */
interface Customweb_Form_Validator_IValidator {
	
	/**
	 * This method must return the JavaScript code required to 
	 * execute the validation. The code must return a anonymous
	 * JS function which accepts two arguments: 
	 * 
	 * <ol>
	 *   <li>event: The submit event object</li>
	 *   <li>element: The HTML element (control field) on which the validation 
	 *   should be exectued on.</li>
	 * </ol>
	 * 
	 * To stop further processing the function stopEvent(event) exists. This
	 * method blocks the form to be sent to the server. This method should be
	 * executed to present the user a the error message.
	 * 
	 */
	public function getCallbackJs();
	
	/**
	 * The control object on which the validation is executed.
	 * 
	 * @return Customweb_Form_Control_IControl
	 */
	public function getControl();
	
}