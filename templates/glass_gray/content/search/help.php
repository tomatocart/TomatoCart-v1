<?php
/*
  $Id: help.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<style type="text/css">
<!--
#pageContent {
  width: 500px;
  margin: 0;
  padding: 0;
}

div#pageBlockLeft {
  width: 500px;
  margin: 0;
}

.moduleBox {
  width: 500px;
}
//-->
</style>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('search_help_heading'); ?></h6>

  <div class="content">
    <p><?php echo $osC_Language->get('search_help'); ?></p>

    <p align="right"><?php echo osc_link_object('javascript:window.close();', $osC_Language->get('close_window')); ?></p>
  </div>
</div>
