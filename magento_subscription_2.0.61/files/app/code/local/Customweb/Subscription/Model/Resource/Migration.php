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

/**
 * Migration model of customweb subscription module.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Resource_Migration {
	
	/**
	 * Current version
	 *
	 * @var string
	 */
	const CURRENT_VERSION = '2.1.0';

	public function migrate(){
		set_time_limit(0);
		
		$subscriptionIds = Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('version', 
				array(
					'neq' => self::CURRENT_VERSION 
				))->getAllIds();
		$pageSize = 500;
		$pages = ceil(count($subscriptionIds) / $pageSize);
		for ($page = 1; $page <= $pages; $page++) {
			$subscriptions = Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('version', 
					array(
						'neq' => self::CURRENT_VERSION 
					))->setPageSize($pageSize)->setCurPage($page);
			foreach ($subscriptions as $subscription) {
				try {
					$subscription->getResource()->beginTransaction();
					
					$version = $subscription->getVersion();
					if (empty($version)) {
						$this->migrateFrom130($subscription);
					}
					elseif ($version == '2.0.0') {
						$this->migrateFrom200($subscription);
					}
					
					$subscription->setVersion(self::CURRENT_VERSION);
					$subscription->save();
					
					$subscription->getResource()->commit();
				}
				catch (Exception $e) {
					$subscription->getResource()->rollBack();
					throw $e;
				}
			}
		}
	}

	private function migrateFrom130(Customweb_Subscription_Model_Subscription $subscription){
		if ($subscription->getStatus() == 'captured') {
			$subscription->setStatus('paid');
		}
		if (!in_array($subscription->getStatus(), array(
			'canceled',
			'expired' 
		)) && $subscription->getCancelRequest()) {
			$cancelDate = Mage::helper('customweb_subscription')->toDateObject($subscription->getCancelDate());
			$count = 0;
			while ($cancelDate->compareDate(Zend_Date::now()) > 0) {
				$subscription->getPlan()->previousDueDate($cancelDate);
				$count++;
			}
			$subscription->setCancelCount($subscription->getCancelPeriod() - $count);
		}
		$this->scheduleNextJobs($subscription);
	}

	private function migrateFrom200(Customweb_Subscription_Model_Subscription $subscription){
		$subscription->deletePendingJobs();
		$this->scheduleNextJobs($subscription);
	}

	private function scheduleNextJobs(Customweb_Subscription_Model_Subscription $subscription){
		if (in_array($subscription->getStatus(), array(
			'pending',
			'authorized',
			'paid' 
		))) {
			$dueDate = $subscription->getPlan()->getNextDueDate();
			$subscription->getPlan()->previousDueDate($dueDate);
			
			Mage::getModel('customweb_subscription/scheduler')->createJob($subscription, $subscription->getCheckDate($dueDate), 'check');
		}
		if ($subscription->getStatus() == 'active') {
			$subscription->scheduleNextJobs();
		}
	}
}