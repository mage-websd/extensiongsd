<?php 
/**
 ::[Header]::
 */

//require_once 'Customweb/Payment/Endpoint/Annotation/ExtractionMethod.php';
//require_once 'Customweb/IAnnotation.php';



/**
 * A method annotated with this annotation allows the extraction different ids from the 
 * request to load the transaction. 
 * 
 * The method has to return a array with a id identifer and the id it self.
 * 
 * e.g. array(
 * 	'id' => '123',
 *  'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::TRANSACTION_ID_KEY,
 * )
 * 
 * This annotation can only be applied once per controller.
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_Payment_Endpoint_Annotation_ExtractionMethod implements Customweb_IAnnotation{
	
	const TRANSACTION_ID_KEY = 'transactionId';
	
	const EXTERNAL_TRANSACTION_ID_KEY = 'externalTransactionId';
	
	const PAYMENT_ID_KEY = 'paymentId';
	
}