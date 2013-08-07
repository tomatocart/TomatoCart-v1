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
    if ($messageStack->size('login') > 0) {
        echo $messageStack->output('login');
    }
?>

<div class="moduleBox">
    <div class="content btop">
    	<div class="row-fluid">
            <div class="span6 clearfix">
                <div class="outsideHeading">
                	<b><?php echo $osC_Language->get('login_new_customer_heading'); ?></b>
                </div>
                
                <p><?php echo $osC_Language->get('login_new_customer_text'); ?></p>
                
                <p align="right">
                    <a class="btn btn-small pull-right" href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'create', 'SSL'); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
            	</p>
            </div>
            
            <div class="span6">
                <form name="login" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'login=process', 'SSL'); ?>" method="post">
                
                    <b><?php echo $osC_Language->get('login_returning_customer_heading'); ?></b>
                    
                    <p><?php echo $osC_Language->get('login_returning_customer_text'); ?></p>
                    
                    <div class="control-group">
                        <?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), 'email_address', null, true); ?>
                        <div class="controls">
                        	<?php echo osc_draw_input_field('email_address'); ?>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <?php echo osc_draw_label($osC_Language->get('field_customer_password'), 'password', null, true); ?>
                        <div class="controls">
                        	<?php echo osc_draw_password_field('password'); ?>
                        </div>
                    </div>
                    
                    <p><?php echo sprintf($osC_Language->get('login_returning_customer_password_forgotten'), osc_href_link(FILENAME_ACCOUNT, 'password_forgotten', 'SSL')); ?></p>
                    
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-small btn-success pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_sign_in'); ?></button>
                        </div>
                    </div>
                
                </form>
            </div>
        </div>
    </div>
</div>