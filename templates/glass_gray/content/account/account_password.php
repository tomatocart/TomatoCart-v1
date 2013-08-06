<?php
/*
  $Id: account_password.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<form name="account_password" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'password=save', 'SSL'); ?>" method="post" onsubmit="return check_form(account_password);">

<div class="moduleBox">

  <h6><em><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('my_password_title'); ?></h6>

  <div class="content">
    <ol>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password_current'), 'password_current', null, true) . osc_draw_password_field('password_current'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password_new'), 'password_new', null, true) . osc_draw_password_field('password_new'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password_confirmation'), 'password_confirmation', null, true) . osc_draw_password_field('password_confirmation'); ?></li>
    </ol>
  </div>
</div>

<div class="submitFormButtons">
  <span style="float: right;"><?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?></span>

  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

</form>
