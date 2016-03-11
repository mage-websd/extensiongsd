<?php

class CJM_ColorSelectorPlus_Model_Eav_Resource_Entity_Attribute_Option_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection
{
    public function setIdFilter($optionId)
    {
   		if (is_array($optionId)) {
   			$this->addFieldToFilter('main_table.option_id', array('in' => $optionId));
		} else if ($optionId != '') {
			$this->addFieldToFilter('main_table.option_id', $optionId);
		}
		return $this;
    }
}