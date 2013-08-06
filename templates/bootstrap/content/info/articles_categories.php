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

    $Qarticles = toC_Articles::getListing($_GET['articles_categories_id']);
?>

<h1><?php echo $article_categories['articles_categories_name']; ?></h1>

<?php
    if ($Qarticles->numberOfRows() > 0) {
        while ($Qarticles->next()) {
?>
<div class="moduleBox">
    <h6><span style="float: right;"><?php echo osC_DateTime::getShort($Qarticles->value('articles_date_added')); ?></span><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->valueInt('articles_id')), $Qarticles->value('articles_name')); ?></h6>

    <div class="content">
    <?php
        if (!osc_empty($Qarticles->value('articles_image'))) {
            echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->valueInt('articles_id')), $osC_Image->show($Qarticles->value('articles_image'), $Qarticles->value('articles_name'), 'style="float: left;margin: 5px 10px 5px 5px"', '', 'articles'));
        }
    ?>
        <p>
            <?php
                $description = strip_tags($Qarticles->value('articles_description'));
                echo substr($description, 0, 300) . ((strlen($description) >= 100) ? '..' : ''); 
            ?>
        </p>
    </div>
</div>
<?php
    }
?>

<div class="listingPageLinks">
    <span style="float: right;"><?php echo $Qarticles->getBatchPageLinks('page', osc_get_all_get_params(array('x', 'y'))); ?></span>
    
    <?php echo $Qarticles->getBatchTotalPages($osC_Language->get('result_set_number_of_articles')); ?>
</div>
  
<?php
    } else {
?>

<div class="moduleBox">
    <div class="content">
        <?php echo $osC_Language->get('no_article_in_this_category'); ?>
    </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
	<a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_ACCOUNT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>
<?php 
    }
?>