<?php
/*
  $Id: account_newsletters.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<form name="account_newsletter" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'newsletters=save', 'SSL'); ?>" method="post">

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('newsletter_subscriptions_heading'); ?></h6>

  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="30"><?php echo osc_draw_checkbox_field('newsletter_general', '1', $Qnewsletter->value('customers_newsletter')); ?></td>
        <td><b><?php echo osc_draw_label($osC_Language->get('newsletter_general'), 'newsletter_general'); ?></b></td>
      </tr>
      <tr>
        <td width="30">&nbsp;</td>
        <td><?php echo $osC_Language->get('newsletter_general_description'); ?></td>
      </tr>
    </table>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
</div>

</form>
