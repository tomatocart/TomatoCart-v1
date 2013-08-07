<?php
/*
  $Id: application_bottom.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $messageStack->add('debug', 'Number of queries: ' . $osC_Database->numberOfQueries() . ' [' . $osC_Database->timeOfQueries() . 's]', 'warning');
  $osC_Services->stopServices();
?>
