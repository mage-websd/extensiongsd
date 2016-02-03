<?php
	$ddate = Mage::registry('ddate_by_order');
?>

<div class="box-right">	
    <div class="entry-edit">
		<div class="entry-edit-head">
			<h4 class="icon-head head-shipping-method"><?php echo $this->helper('ddate')->__('Customer Order Comment') ?></h4>
		</div>
		<fieldset>
			<textarea cols="84" rows="1" value="<?php echo $ddate['ddate_comment']; ?>" name="mw_customercomment_info" id="mw_customercomment_info" style="width:98%; height:4em;"><?php echo $ddate['ddate_comment']; ?></textarea>
		<div class="clear"></div>
		</fieldset>
    </div>
</div>