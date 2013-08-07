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

require_once('includes/classes/rss.php');

$Qcategories = toC_RSS::getCategories();
?>
<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('rss_categories'); ?></h6>
    
    <div class="content">
        <ul>
            <?php
                while ($Qcategories->next()) {
            ?>
			<li>
            	<span style="float: right">
            	<?php 
            	    echo osc_link_object(osc_href_link(FILENAME_RSS, 'categories_id=' . $Qcategories->value('categories_id')), osc_image(DIR_WS_IMAGES . 'rss16x16.png'));
            	?>
            	</span>         
                <?php 
                    echo osc_link_object(osc_href_link(FILENAME_RSS, 'categories_id=' . $Qcategories->value('categories_id')), $Qcategories->value('categories_name'));
                ?>
          	</li>
            <?php 
                } 
              
                $Qcategories->freeResult();
            ?>
            <li>
                <span style="float:right;"><?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=new'), osc_image(DIR_WS_IMAGES . 'rss16x16.png')); ?></span>
                <?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=new'), $osC_Language->get('new_products')); ?>
            </li>
            <li>
                <span style="float:right;"><?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=special'), osc_image(DIR_WS_IMAGES . 'rss16x16.png')); ?></span>
                <?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=special'), $osC_Language->get('special_products')); ?>
            </li>
            <li>
                <span style="float:right;"><?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=feature'), osc_image(DIR_WS_IMAGES . 'rss16x16.png')); ?></span>
                <?php echo osc_link_object(osc_href_link(FILENAME_RSS, 'group=feature'), $osC_Language->get('feature_products')); ?>
            </li>
        </ul>
    </div>
</div>
