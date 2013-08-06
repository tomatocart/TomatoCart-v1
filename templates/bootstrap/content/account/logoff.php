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
<div class="moduleBox">
    <h1><?php echo $osC_Template->getPageTitle(); ?></h1>
    
    <div class="content clearfix">
        <div class="pull-left"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/img/account_successs.png', $osC_Template->getPageTitle()); ?></div>
        
        <div style="padding-top: 30px;">
			<p><?php echo $osC_Language->get('sign_out_text'); ?></p>
        </div>
    </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
	<a class="btn btn-small pull-right" href="<?php echo osc_href_link(FILENAME_DEFAULT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>