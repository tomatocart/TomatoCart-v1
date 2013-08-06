<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
*/

  $Qaccount = osC_Account::getEntry();
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
    if ($messageStack->size('account_edit') > 0) {
        echo $messageStack->output('account_edit');
    }
?>

<form name="account_edit" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'edit=save', 'SSL'); ?>" method="post" onsubmit="return check_form(account_edit);" class="form-horizontal">

	<div class="moduleBox">
    	<h6><em class="pull-right"><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('my_account_title'); ?></h6>
    
		<div class="content">
            <?php
              if (ACCOUNT_GENDER > -1) {
                $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                                      array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
                                      
                $gender = $Qaccount->value('customers_gender');
            ?>
            
            <div class="control-group">
                <label class="control-label" for="gender1"><?php echo $osC_Language->get('field_customer_gender') . ((ACCOUNT_GENDER > 0) ? '<em>*</em>' : ''); ?></label>
                <div class="controls">
                	<label class="radio inline" for="gender1"><input type="radio" value="m" id="gender1" name="gender" <?php echo (isset($gender) && $gender == 'm') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_male'); ?></label>
                	<label class="radio inline" for="gender2"><input type="radio" value="f" id="gender2" name="gender" <?php echo (isset($gender) && $gender == 'f') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_female'); ?></label>
                </div>
            </div>
            
            <?php
                }
            ?>
            
            <div class="control-group">
                <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_first_name'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('firstname',$Qaccount->value('customers_firstname')); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_last_name'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('lastname',$Qaccount->value('customers_lastname')); ?>
                </div>
            </div>
    
            <?php
                if (ACCOUNT_DATE_OF_BIRTH == '1') {
            ?>
            
            <div class="control-group">
                <label class="control-label"><?php echo $osC_Language->get('field_customer_date_of_birth'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_date_pull_down_menu('dob', array('year' => $Qaccount->value('customers_dob_year'), 'month' => $Qaccount->value('customers_dob_month'), 'date' => $Qaccount->value('customers_dob_date')), false, null, null, date('Y')-1901, -5); ?>
                </div>
            </div>
            
            <?php
                }
            ?>
            
            <div class="control-group">
                <label class="control-label" for="email_address"><?php echo $osC_Language->get('field_customer_email_address'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('email_address', $Qaccount->value('customers_email_address')); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label"><?php echo $osC_Language->get('field_customer_group'); ?></label>
                <div class="controls">
                	<?php echo $Qaccount->value('customers_groups_name'); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label"><?php echo $osC_Language->get('field_customer_store_credit'); ?></label>
                <div class="controls">
                	<?php echo $osC_Currencies->format($Qaccount->value('customers_credits')); ?>
                </div>
            </div>
    	</div>
    
        <div class="submitFormButtons">
        	<button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
        </div>
    </div>
</form>