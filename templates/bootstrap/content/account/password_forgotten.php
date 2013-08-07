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
  if ($messageStack->size('password_forgotten') > 0) {
    echo $messageStack->output('password_forgotten');
  }
?>

<form name="password_forgotten" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'password_forgotten=process', 'SSL'); ?>" method="post" onsubmit="return check_form(password_forgotten);">

    <div class="moduleBox">
        <h6><?php echo $osC_Language->get('password_forgotten_heading'); ?></h6>
        
        <div class="content">
            <p><?php echo $osC_Language->get('password_forgotten'); ?></p>
            
    		<label for="email_address"><?php echo $osC_Language->get('field_customer_email_address'); ?></label>
    		
            <input type="text" id="email_address" name="email_address">
        </div>
    </div>
    
    <div class="submitFormButtons">
    	<button type="submit" class="btn btn-small pull-right"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
    	
    	<a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_ACCOUNT, null, 'SSL'); ?>"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    </div>

</form>
