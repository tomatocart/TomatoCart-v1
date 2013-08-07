<?php
/*
  $Id: login.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<?php 
  if ($messageStack->size('login') > 0) {
    echo $messageStack->output('login');
  }
?>

<div class="moduleBox">
  <h1><?php echo $osC_Template->getPageTitle(); ?></h1>
  
  <div class="content">
    <div style="width: 49%; float: right;">
      <form name="login" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'login=process', 'SSL'); ?>" method="post">
    
      <b><?php echo $osC_Language->get('login_returning_customer_heading'); ?></b>
    
      <div>
        <p><?php echo $osC_Language->get('login_returning_customer_text'); ?></p>
    
        <ol>
          <li><?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), 'email_address', null, true) . osc_draw_input_field('email_address'); ?></li>
          <li><?php echo osc_draw_label($osC_Language->get('field_customer_password'), 'password', null, true) . osc_draw_password_field('password'); ?></li>
        </ol>
    
        <p><?php echo sprintf($osC_Language->get('login_returning_customer_password_forgotten'), osc_href_link(FILENAME_ACCOUNT, 'password_forgotten', 'SSL')); ?></p>
    
        <p align="right"><?php echo osc_draw_image_submit_button('button_login.gif', $osC_Language->get('button_sign_in')); ?></p>
      </div>
    
      </form>
    </div>
      
    <div style="width: 49%;">
      <div class="outsideHeading">
        <b><?php echo $osC_Language->get('login_new_customer_heading'); ?></b>
      </div>
    
      <div>
        <p><?php echo $osC_Language->get('login_new_customer_text'); ?></p>
    
        <p align="right"><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'create', 'SSL'), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?></p>
      </div>
    </div>
    <div style="clear: both"></div>
  </div>
</div>