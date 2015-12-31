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
 * Defines a field which is updated from with in the adapter.
 * 
 * @author Nico Eigenmann / Thomas Hunziker
 *
 */
interface Customweb_Payment_Authorization_OrderContext_Updatable_IField {
	
	const SEMANTIC_BILLING_ADDRESS_EMAIL_ADDRESS = 'BILLING::ADDRESS::EMAIL-ADDRESS';
	const SEMANTIC_BILLING_ADDRESS_GENDER = 'BILLING::ADDRESS::GENDER';
	const SEMANTIC_BILLING_ADDRESS_SALUTATION = 'BILLING::ADDRESS::SALUTATION';
	const SEMANTIC_BILLING_ADDRESS_FIRSTNAME = 'BILLING::ADDRESS::FIRSTNAME';
	const SEMANTIC_BILLING_ADDRESS_LASTNAME = 'BILLING::ADDRESS::LASTNAME';
	const SEMANTIC_BILLING_ADDRESS_STREET = 'BILLING::ADDRESS::STREET';
	const SEMANTIC_BILLING_ADDRESS_CITY = 'BILLING::ADDRESS::CITY';
	const SEMANTIC_BILLING_ADDRESS_POSTCODE = 'BILLING::ADDRESS::POSTCODE';
	const SEMANTIC_BILLING_ADDRESS_STATE = 'BILLING::ADDRESS::STATE';
	const SEMANTIC_BILLING_ADDRESS_COUNTRY_CODE = 'BILLING::ADDRESS::COUNTRY-CODE';
	const SEMANTIC_BILLING_ADDRESS_PHONE_NUMBER = 'BILLING::ADDRESS::PHONE-NUMBER';
	const SEMANTIC_BILLING_ADDRESS_MOBILE_PHONE_NUMBER = 'BILLING::ADDRESS::MOBILE-PHONE-NUMBER';
	const SEMANTIC_BILLING_ADDRESS_DATE_OF_BIRTH = 'BILLING::ADDRESS::DATE-OF-BIRTH';
	const SEMANTIC_BILLING_ADDRESS_COMMERCIAL_REGISTER_NUMBER = 'BILLING::ADDRESS::COMMERCIAL-REGISTER-NUMBER';
	const SEMANTIC_BILLING_ADDRESS_COMPANY_NAME = 'BILLING::ADDRESS::COMPANY-NAME';
	const SEMANTIC_BILLING_ADDRESS_SALES_TAX_NUMBER = 'BILLING::ADDRESS::SALES-TAX-NUMBER';
	const SEMANTIC_BILLING_ADDRESS_SOCIAL_SECURITY_NUMBER = 'BILLING::ADDRESS::SOCIAL-SECURITY-NUMBER';

	const SEMANTIC_SHIPPING_ADDRESS_EMAIL_ADDRESS = 'SHIPPING::ADDRESS::EMAIL-ADDRESS';
	const SEMANTIC_SHIPPING_ADDRESS_GENDER = 'SHIPPING::ADDRESS::GENDER';
	const SEMANTIC_SHIPPING_ADDRESS_SALUTATION = 'SHIPPING::ADDRESS::SALUTATION';
	const SEMANTIC_SHIPPING_ADDRESS_FIRSTNAME = 'SHIPPING::ADDRESS::FIRSTNAME';
	const SEMANTIC_SHIPPING_ADDRESS_LASTNAME = 'SHIPPING::ADDRESS::LASTNAME';
	const SEMANTIC_SHIPPING_ADDRESS_STREET = 'SHIPPING::ADDRESS::STREET';
	const SEMANTIC_SHIPPING_ADDRESS_CITY = 'SHIPPING::ADDRESS::CITY';
	const SEMANTIC_SHIPPING_ADDRESS_POSTCODE = 'SHIPPING::ADDRESS::POSTCODE';
	const SEMANTIC_SHIPPING_ADDRESS_STATE = 'SHIPPING::ADDRESS::STATE';
	const SEMANTIC_SHIPPING_ADDRESS_COUNTRY_CODE = 'SHIPPING::ADDRESS::COUNTRY-CODE';
	const SEMANTIC_SHIPPING_ADDRESS_PHONE_NUMBER = 'SHIPPING::ADDRESS::PHONE-NUMBER';
	const SEMANTIC_SHIPPING_ADDRESS_MOBILE_PHONE_NUMBER = 'SHIPPING::ADDRESS::MOBILE-PHONE-NUMBER';
	const SEMANTIC_SHIPPING_ADDRESS_DATE_OF_BIRTH = 'SHIPPING::ADDRESS::DATE-OF-BIRTH';
	const SEMANTIC_SHIPPING_ADDRESS_COMMERCIAL_REGISTER_NUMBER = 'SHIPPING::ADDRESS::COMMERCIAL-REGISTER-NUMBER';
	const SEMANTIC_SHIPPING_ADDRESS_COMPANY_NAME = 'SHIPPING::ADDRESS::COMPANY-NAME';
	const SEMANTIC_SHIPPING_ADDRESS_SALES_TAX_NUMBER = 'SHIPPING::ADDRESS::SALES-TAX-NUMBER';
	const SEMANTIC_SHIPPING_ADDRESS_SOCIAL_SECURITY_NUMBER = 'SHIPPING::ADDRESS::SOCIAL-SECURITY-NUMBER';
	
	/**
	 * This method returns the new value set on the order context. The value
	 * depends on the semantic of the field. 
	 * 
	 * @return mixed
	 */
	public function getValue();
	
	/**
	 * Returns the sematic of the field. It is one of the constants defined 
	 * on this interface starting with SEMANTIC_*.
	 * 
	 * @return string
	 */
	public function getSemanticKey();


}