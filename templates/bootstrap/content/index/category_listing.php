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

    if (isset($cPath) && strpos($cPath, '_')) {
        // check to see if there are deeper categories within the current category
        $category_links = array_reverse($cPath_array);
        for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
            $Qcategories = $osC_Database->query('select count(*) as total from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_status = 1 and c.categories_id = cd.categories_id and cd.language_id = :language_id');
            $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
            $Qcategories->bindInt(':parent_id', $category_links[$i]);
            $Qcategories->bindInt(':language_id', $osC_Language->getID());
            $Qcategories->execute();
            
            if ($Qcategories->valueInt('total') < 1) {
              // do nothing, go through the loop
            } else {
              $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id and c.categories_status = 1 order by sort_order, cd.categories_name');
              $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
              $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
              $Qcategories->bindInt(':parent_id', $category_links[$i]);
              $Qcategories->bindInt(':language_id', $osC_Language->getID());
              $Qcategories->execute();
              break; // we've found the deepest category the customer is in
            }
        }
    } else {
        $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id and c.categories_status = 1 order by sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':parent_id', $current_category_id);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->execute();
    }
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon" class="pull-right" style="width: ' . HEADING_IMAGE_WIDTH . 'px; height: ' . HEADING_IMAGE_HEIGHT . 'px"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox clearfix">
	<h6><?php echo $osC_Template->getPageTitle(); ?></h6>
	
    <div class="col3 content">
        <?php
            $number_of_categories = $Qcategories->numberOfRows();
            while ($Qcategories->next()) {
        ?>
    	<div class="center">
    		<?php 
    		    echo osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id'))), osc_image(DIR_WS_IMAGES . 'categories/' . $Qcategories->value('categories_image'), $Qcategories->value('categories_name')) . '<br />' . $Qcategories->value('categories_name'));
    		?>
    	</div>
        <?php 
            }
        ?>
    </div>
</div>