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

<?php
    if ($messageStack->size('search') > 0) {
        echo $messageStack->output('search');
    }
?>

<form name="search" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false); ?>" method="get" onsubmit="return check_form(this);">

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('search_criteria_title'); ?></h6>
    
    <div class="content">
        <?php echo osc_draw_input_field('keywords', null); ?>
    </div>
</div>

<div class="submitFormButtons">
    <button type="submit" class="btn btn-small pull-right" id="btnSaveShippingMethod"><i class="icon-search icon-white"></i> <?php echo $osC_Language->get('button_search'); ?></button>
    
        <?php echo osc_link_object('javascript:popupWindow(\'' . osc_href_link(FILENAME_SEARCH, 'help') . '\');', $osC_Language->get('search_help_tips')); ?>
</div>

<div class="moduleBox">
	<h6><?php echo $osC_Language->get('advanced_search_heading'); ?></h6>

	<div class="content">
    	<div class="row-fluid">
    		<div class="span6">
    			<div class="control-group">
                        <label class="control-label" for="cPath"><?php echo $osC_Language->get('field_search_categories'); ?><em>*</em></label>
                        <div class="controls">
                            <?php
                                $osC_CategoryTree->setSpacerString('&nbsp;', 3);
                                
                                $categories_array = array(array('id' => '', 'text' => $osC_Language->get('filter_all_categories')));
                                
                                foreach ($osC_CategoryTree->buildBranchArray(0) as $category) {
                                $categories_array[] = array('id' => $category['id'],
                                                            'text' => $category['title']);
                                }
                                
                                echo osc_draw_pull_down_menu('cPath', $categories_array);
                            ?>
                        </div>
                        <div class="control-group">
                            <label class="control-label checkbox" for="recursive"><?php echo osc_draw_checkbox_field('recursive', array(array('id' => '1', 'text' => $osC_Language->get('field_search_recursive'))), true); ?></label>
                        </div>
                    </div>
    		</div>
    		<div class="span6">
                <div class="control-group">
                    <label class="control-label" for="manufacturers"><?php echo $osC_Language->get('field_search_manufacturers'); ?><em>*</em></label>
                    <div class="controls">
                        <?php
                            $manufacturers_array = array(array('id' => '', 'text' => $osC_Language->get('filter_all_manufacturers')));
                            
                            $Qmanufacturers = $osC_Database->query('select manufacturers_id, manufacturers_name from :table_manufacturers order by manufacturers_name');
                            $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
                            $Qmanufacturers->execute();
                            
                            while ($Qmanufacturers->next()) {
                            $manufacturers_array[] = array('id' => $Qmanufacturers->valueInt('manufacturers_id'),
                                                           'text' => $Qmanufacturers->value('manufacturers_name'));
                            }
                            
                            echo osc_draw_pull_down_menu('manufacturers', $manufacturers_array);
                        ?>
                    </div>
                </div>
    		</div>
    	</div>
    	<div class="row-fluid">
    		<div class="span6">
                <div class="control-group">
                    <label class="control-label" for="pfrom"><?php echo $osC_Language->get('field_search_price_from'); ?><em>*</em></label>
                    <div class="controls">
                    	<?php echo osc_draw_input_field('pfrom'); ?>
                    </div>
                </div>
    		</div>
    		<div class="span6">
                <div class="control-group">
                    <label class="control-label" for="pto"><?php echo $osC_Language->get('field_search_price_to'); ?><em>*</em></label>
                    <div class="controls">
                    	<?php echo osc_draw_input_field('pto'); ?>
                    </div>
                </div>
    		</div>
    	</div>
    	<div class="row-fluid">
    		<div class="span6">
                <div class="control-group">
                    <label class="control-label" for="datefrom"><?php echo $osC_Language->get('field_search_date_from'); ?><em>*</em></label>
                    <div class="controls">
                    	<?php echo osc_draw_date_pull_down_menu('datefrom', null, false, null, null, date('Y') - $osC_Search->getMinYear(), 0); ?>
                    </div>
                </div>
    		</div>
    		<div class="span6">
                <div class="control-group">
                    <label class="control-label" for="dateto"><?php echo $osC_Language->get('field_search_date_to'); ?><em>*</em></label>
                    <div class="controls">
                    	<?php echo osc_draw_date_pull_down_menu('dateto', null, null, null, null, date('Y') - $osC_Search->getMaxYear(), 0); ?>
                    </div>
                </div>
    		</div>
    	</div>
	</div>
</div>
<?php
    echo osc_draw_hidden_session_id_field();
?>
</form>