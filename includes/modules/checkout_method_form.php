<?php
/*
  $Id: checkout_method_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $address = $osC_ShoppingCart->getBillingAddress();
  $email_address = isset($address['email_address']) ? $address['email_address'] : null;
?>

<div class="moduleBox" style="width: 49%; float: right;">
  <form name="login" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'login=process', 'SSL'); ?>" method="post">
  <h6><?php echo $osC_Language->get('login_returning_customer_heading');?></h6>

  <div class="content">
    <p><?php echo $osC_Language->get('login_returning_customer_text');?></p>
  
    <ul>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), 'email_address') . '<br />' . osc_draw_input_field('email_address', $email_address);?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password'), 'password') . '<br />' . osc_draw_password_field('password');?></li>
    </ul>
  
    <p><?php echo sprintf($osC_Language->get('login_returning_customer_password_forgotten'), osc_href_link(FILENAME_ACCOUNT, 'password_forgotten', 'SSL'));?></p>

    <div class="submitFormButtons" style="text-align: right;">
      <?php echo osc_draw_image_submit_button('button_login.gif', null, 'id="btnLogin"'); ?>
    </div>
  </div>
  </form>    
</div>

<div class="moduleBox" style="width: 49%;">
  <div class="outsideHeading">
    <h6><?php echo $osC_Language->get('login_new_customer_heading');?></h6>
  </div>

  <div class="content">
    <p><?php echo $osC_Language->get('login_new_customer_text');?></p>

    <div class="submitFormButtons" style="text-align: right;">
      <?php echo osc_draw_image_button('button_continue.gif', null, 'id="btnNewCustomer" style="cursor: pointer"'); ?>
    </div>
  </div>            
</div>

<div style="clear: both; margin: 0"></div>    