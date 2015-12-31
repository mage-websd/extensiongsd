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

//require_once 'Customweb/Payment/Update/AbstractProcessor.php';
//require_once 'Customweb/Util/System.php';
//require_once 'Customweb/Payment/Update/AbstractLockProcessor.php';
//require_once 'Customweb/Util/String.php';
//require_once 'Customweb/Payment/Update/IHandler.php';


/**
 * This process transactions which are scheduled for a update. The scheduling is organised by the 
 * transaction itself.
 * 
 * @author Thomas Hunziker / Simon Schurter
 *
 */
class Customweb_Payment_Update_ScheduledProcessor extends Customweb_Payment_Update_AbstractLockProcessor {
	
	private $transactionObject = null;
	private $transactionId = null;
	
	/**
	 * @see Customweb_Payment_Update_AbstractProcessor::process()
	 * @Cron()
	 */
	public function process() {
		if ($this->getUpdateAdapter() === null) {
			return;
		}

		if (!$this->tryLockUpdate()){
			return;
		}
		
		$approximatelyExecutedTime = 4;
		$maxExecutionTime = Customweb_Util_System::getMaxExecutionTime() - $approximatelyExecutedTime;
		$start = $this->getStartTime();
		$maxEndtime = $maxExecutionTime + $start;
		
		try {
			$candidates = $this->getHandler()->getScheduledTransactionIds();
			foreach ($candidates as $transactionId) {
				if ($maxEndtime > time()) {
					$this->executeUpdate($transactionId);
				}
				else {
					break;
				}
				
			}
		}
		catch(Exception $e) {
			$this->getHandler()->log("Failed to load scheduled transactions: " . $e->getMessage(), Customweb_Payment_Update_IHandler::LOG_TYPE_ERROR);
		}
		
		$this->unlockUpdate();
	}
	
	protected function getBackendStorageSpace() {
		return 'scheduled_update_processor';
	}
	
	private function executeUpdate($transactionId) {
		if (!$this->getTransactionHandler()->isTransactionRunning()) {
			$this->getTransactionHandler()->beginTransaction();
		}
	
		$transactionObject = $this->getTransactionHandler()->findTransactionByTransactionId($transactionId);
		if ($transactionObject == null) {
			$this->getHandler()->log(Customweb_Util_String::formatString("No transaction found for transaction id '!id'.", array('!id' => $transactionId)), Customweb_Payment_Update_IHandler::LOG_TYPE_ERROR);
		}
		else {
			if ($transactionObject->getUpdateExecutionDate() !== null && $transactionObject->getUpdateExecutionDate()->getTimestamp() <= time()) {
// 				$lastUpdateDate = $transactionObject->getUpdateExecutionDate();
				try {
					if (method_exists($transactionObject, 'setUpdateExecutionDate')) {
						$transactionObject->setUpdateExecutionDate(null);
					}
					
					$this->getUpdateAdapter()->updateTransaction($transactionObject);
					
					$this->getHandler()->log(
						Customweb_Util_String::formatString(
							"Transaction with id '!id' successful updated.", 
							array('!id' => $transactionId)
						), 
						Customweb_Payment_Update_IHandler::LOG_TYPE_INFO
					);
				}
				catch(Exception $e) {
					$this->getHandler()->log($e->getMessage(), Customweb_Payment_Update_IHandler::LOG_TYPE_ERROR);
					// Reschedule the transaction of the update.
					
// 					The API should handle all Exceptions.
// 					We catch the unexpected ones, therefore it was a fatal error and we stop
// 					the pulling for this transaction, as it will most likely fail again.
// 					if (method_exists($transactionObject, 'setUpdateExecutionDate')) {
// 						$transactionObject->setUpdateExecutionDate($lastUpdateDate);
// 					}
				}
			}
			$this->getTransactionHandler()->persistTransactionObject($transactionObject);
		}
		$this->getTransactionHandler()->commitTransaction();
	}

	
	
}