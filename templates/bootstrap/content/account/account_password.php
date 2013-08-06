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
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<form name="account_password" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'password=save', 'SSL'); ?>" method="post" onsubmit="return check_form(account_password);" class="form-horizontal">

    <div class="moduleBox">
    
      	<h6><em class="pull-right"><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('my_password_title'); ?></h6>
    
      	<div class="content">
            <div class="control-group">
                <label class="control-label" for="password_current"><?php echo $osC_Language->get('field_customer_password_current'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_password_field('password_current'); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="password_new"><?php echo $osC_Language->get('field_customer_password_new'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_password_field('password_new'); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="password_confirmation"><?php echo $osC_Language->get('field_customer_password_confirmation'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_password_field('password_confirmation'); ?>
                </div>
            </div>
      </div>
    </div>
    
    <div class="submitFormButtons">
    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, null, 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
        
        <button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
    </div>

</form>
