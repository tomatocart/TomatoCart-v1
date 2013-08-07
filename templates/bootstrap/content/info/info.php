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

<h1><?php echo $article['articles_name']; ?></h1>

<div class="moduleBox">
    <div class="content">
        <?php
            if (!osc_empty($article['articles_image'])) {
        ?>
		<p style="float: right; padding: 0px 5px 5px 5px"><?php echo $osC_Image->show($article['articles_image'], $article['articles_image'], '', 'product_info', 'articles'); ?></p>
        <?php 
            }
        ?>
		<p><?php echo $article['articles_description']; ?></p>
    </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  	<a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_DEFAULT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>
