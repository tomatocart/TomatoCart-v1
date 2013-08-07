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

<style type="text/css">
<!--
#pageWrapper {
  margin-left: 20px;
  padding: 0;
  float: left;
}

#pageContent {
  width: 100%;
  margin: 0;
  padding: 0;
}

div#pageBlockLeft {
  width: 0;
  margin: 0;
}
//-->
</style>

  <h1><?php echo $osC_Language->get('compare_products_heading'); ?></h1>

  <div>
		<?php
		  echo $toC_Compare_Products->outputCompareProductsTable();
		?>

    <p align="right"><?php echo osc_link_object('javascript:window.close();', $osC_Language->get('close_window')); ?></p>
  </div>
