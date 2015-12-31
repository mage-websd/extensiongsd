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
 *
 * @category Customweb
 * @package Customweb_Subscription
 * @version 2.0.61
 */
class Customweb_Subscription_Model_PeriodUnit {
	
	/**
	 *
	 * @var Customweb_Subscription_Model_PeriodUnit[]
	 */
	private static $elements = array();

	private static function elements(){
		if (empty(self::$elements)) {
			self::add('minute', Zend_Date::MINUTE, 'Minute');
			self::add('hour', Zend_Date::HOUR, 'Hour');
			self::add('day', Zend_Date::DAY, 'Day');
			self::add('week', Zend_Date::WEEK, 'Week');
			self::add('month', Zend_Date::MONTH, 'Month');
			self::add('year', Zend_Date::YEAR, 'Year');
		}
		return self::$elements;
	}

	private static function add($name, $dateConstant, $label){
		self::$elements[$name] = new self($name, $dateConstant, $label);
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit[]
	 */
	public static function values(){
		return self::elements();
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 * @throws Exception
	 */
	public static function valueOf($name){
		$elements = self::elements();
		if (isset($elements[$name])) {
			return $elements[$name];
		}
		throw new Exception('No period unit named ' . $name . ' specified.');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function MINUTE(){
		return self::valueOf('minute');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function HOUR(){
		return self::valueOf('hour');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function DAY(){
		return self::valueOf('day');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function WEEK(){
		return self::valueOf('week');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function MONTH(){
		return self::valueOf('month');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_PeriodUnit
	 */
	public static function YEAR(){
		return self::valueOf('year');
	}
	
	/**
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 *
	 * @var string
	 */
	private $dateConstant;
	
	/**
	 *
	 * @var string
	 */
	private $label;

	private function __construct($name, $dateConstant, $label){
		$this->name = $name;
		$this->dateConstant = $dateConstant;
		$this->label = $label;
	}

	/**
	 *
	 * @var string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 *
	 * @var string
	 */
	public function getDateConstant(){
		return $this->dateConstant;
	}

	/**
	 *
	 * @var string
	 */
	public function getLabel(){
		return Mage::helper('customweb_subscription')->__($this->label);
	}

	/**
	 *
	 * @param Customweb_Subscription_Model_PeriodUnit $o
	 * @return number
	 */
	public function compareTo(Customweb_Subscription_Model_PeriodUnit $o){
		$elements = self::values();
		$key1 = array_search($this->getName(), $elements);
		$key2 = array_search($o->getName(), $elements);
		return $key1 - $key2;
	}
}