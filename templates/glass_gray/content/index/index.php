<?php
/*
  $Id: index.php $
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
  if ($osC_Customer->isLoggedOn()) {
    echo '<p>' . sprintf($osC_Language->get('greeting_customer'), osc_output_string_protected($osC_Customer->getFirstName()), osc_href_link(FILENAME_PRODUCTS, 'new')) . '</p>';
  } else {
    echo '<p>' . sprintf($osC_Language->get('greeting_guest'), osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'), osc_href_link(FILENAME_PRODUCTS, 'new')) . '</p>';
  }
?>

<?php echo $osC_Language->get('index_text'); ?>