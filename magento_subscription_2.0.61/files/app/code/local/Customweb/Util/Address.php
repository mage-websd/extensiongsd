<?php

/**
 *  * You are allowed to use this API in your web application.
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
final class Customweb_Util_Address {

	private function __construct(){}

	public static function splitStreet($street, $countryIsoCode, $zipCode){
		// TODO Correctly splitting the street from the street number is quite elaborate
		// we just assume that the street number is the last part of the street, this is
		// certainly not true for many countries.
		// i.e. US addresses "222314, 32 Rd",
		// combined numbers "Samplestreet A 12" , ....
		$result = array();
		if (preg_match("/(.*)\s([^\s]+)$/", trim($street), $match)) {
			$result['street'] = $match[1];
			$result['street-number'] = $match[2];
		}
		else {
			$result['street'] = $street;
			$result['street-number'] = '';
		}
		return $result;
	}

	/**
	 * This method takes two order context addresses and compares them.
	 * The method returns true, when they match.
	 *
	 * @param Customweb_Payment_Authorization_OrderContext_IAddress $address1
	 * @param Customweb_Payment_Authorization_OrderContext_IAddress $address2
	 * @return boolean
	 */
	public static function compareAddresses(Customweb_Payment_Authorization_OrderContext_IAddress $address1, Customweb_Payment_Authorization_OrderContext_IAddress $address2){
		if ($address1->getCity() != $address2->getCity()) {
			return false;
		}
		if ($address1->getCompanyName() != $address2->getCompanyName()) {
			return false;
		}
		if ($address1->getCountryIsoCode() != $address2->getCountryIsoCode()) {
			return false;
		}
		if ($address1->getFirstName() != $address2->getFirstName()) {
			return false;
		}
		if ($address1->getLastName() != $address2->getLastName()) {
			return false;
		}
		if ($address1->getPostCode() != $address2->getPostCode()) {
			return false;
		}
		if ($address1->getStreet() != $address2->getStreet()) {
			return false;
		}
		
		return true;
	}

	/**
	 * This method takes the two order contexts and compare their shipping addresses.
	 * The method returns true, when they equal.
	 *
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext1
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext2
	 * @return boolean
	 */
	public static function compareShippingAddresses(Customweb_Payment_Authorization_IOrderContext $orderContext1, Customweb_Payment_Authorization_IOrderContext $orderContext2){
		return self::compareAddresses($orderContext1->getShippingAddress(), $orderContext2->getShippingAddress());
	}
}