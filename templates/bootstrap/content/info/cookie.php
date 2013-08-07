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

<div class="moduleBox" style="width: 40%; float: right; margin: 0 0 10px 10px;">
    <h6><?php echo $osC_Language->get('cookie_usage_box_heading'); ?></h6>
    
    <div class="content">
        <?php echo $osC_Language->get('cookie_usage_box_contents'); ?>
    </div>
</div>

<p><?php echo $osC_Language->get('cookie_usage'); ?></p>

<div class="submitFormButtons">
	<a class="btn btn-small pull-right" href="<?php echo osc_href_link(FILENAME_ACCOUNT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>