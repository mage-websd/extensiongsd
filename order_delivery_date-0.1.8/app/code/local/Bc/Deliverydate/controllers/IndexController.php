<?php
class Bc_Deliverydate_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/deliverydate?id=15 
    	 *  or
    	 * http://site.com/deliverydate/id/15 	
    	 */
    	/* 
		$deliverydate_id = $this->getRequest()->getParam('id');

  		if($deliverydate_id != null && $deliverydate_id != '')	{
			$deliverydate = Mage::getModel('deliverydate/deliverydate')->load($deliverydate_id)->getData();
		} else {
			$deliverydate = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($deliverydate == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$deliverydateTable = $resource->getTableName('deliverydate');
			
			$select = $read->select()
			   ->from($deliverydateTable,array('deliverydate_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$deliverydate = $read->fetchRow($select);
		}
		Mage::register('deliverydate', $deliverydate);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}