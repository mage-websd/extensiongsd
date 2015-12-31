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

//require_once 'Customweb/Core/Util/Class.php';


/**
 * This Util class allows a convient way to serialize and unserialize of
 * objects.
 *
 * In case of unserializing the class checks if any class must be loaded. In
 * case a class is not loaded the method tries to load it with the library
 * class loader. In case this does not work, the registred callbacks are called.
 *
 * @author Thomas Hunziker / Simon Schurter
 *
 */
final class Customweb_Core_Util_Serialization {

	private function __construct() {}

	/**
	 * Serializes a object into a string representation.
	 *
	 * @param object $object
	 * @return string
	 */
	public static function serialize($object) {
		return base64_encode(serialize($object));
	}

	/**
	 * Unserializes a object from a string representation produced by
	 * Serialization::serialize().
	 *
	 * @param string $data
	 * @return mixed
	 * @throws Customweb_Core_Exception_ClassNotFoundException
	 */
	public static function unserialize($data) {
		$serializedString = base64_decode($data);
		self::preloadClasses($serializedString);
		return unserialize($serializedString);
	}

	/**
	 * @param string $serializedString
	 * @throws Customweb_Core_Exception_ClassNotFoundException
	 */
	private static function preloadClasses($serializedString) {
		$matches = array();
		preg_match_all('/O:[0-9]+:\"(.+?)\":/', $serializedString, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $match) {
				$className = $match;
				if (!Customweb_Core_Util_Class::isClassLoaded($className)) {
					try {
						Customweb_Core_Util_Class::loadLibraryClassByName($className);
					}
					catch(Customweb_Core_Exception_ClassNotFoundException $e) {
						if (!class_exists($className)) {
							throw $e;
						}
					}
				}
			}
		}
	}

}