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
    global $toC_Compare_Products, $osC_Language, $osC_Template; 
?>

<!-- box information start //-->

<div class="boxNew">
    <div class="boxTitle"><?php echo $osC_Box->getTitle(); ?></div>
    
    <div class="boxContents">
		<ul class="clearfix">
			<?php 
                foreach ($toC_Compare_Products->getProducts() as $products_id) {
                    $osC_Product = new osC_Product($products_id);
                    
                    $cid = $products_id;
                    $str_variants = '';
                    //if the product have any variants, it means that the $products_id should be a product string such as 1#1:1;2:2
                    if ($osC_Product->hasVariants()) {
                        $variants = $osC_Product->getVariants();
                        
                        if (preg_match('/^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)+$/', $products_id)) {
                            $products_variant = $variants[$products_id];
                        }else {
                            $products_variant = $osC_Product->getDefaultVariant();
                        }
                        
                        //if the product have any variants, get the group_name:value_name string
                        if (isset($products_variant) && isset($products_variant['groups_name']) && is_array($products_variant['groups_name']) && !empty($products_variant['groups_name'])) {
                            $str_variants .= ' -- ';
                            
                            foreach($products_variant['groups_name'] as $groups_name => $value_name) {
                                $str_variants .= '<strong>' . $groups_name . ': ' . $value_name . '</strong>;';
                            }
                            
                            //clean the last ';'
                            if (($pos = strrpos($str_variants, ';')) !== false) {
                                $str_variants = substr($str_variants, 0, -1);
                            }
                        }
                        
                        //build the product string that could be used
                        if (strpos($products_id, '#') !== false) {
                            $cid = str_replace('#', '_', $products_id);
                        }
                    }
            ?>
          	<li>
          		<span class="pull-right" style="margin: 0 3px 1px 3px; width: 16px"><a href="<?php echo osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), 'cid=' . $cid . '&' . osc_get_all_get_params(array('cid', 'action')) . '&action=compare_products_remove'); ?>"> <i class="icon-trash"></i> </a></span>
          		<?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id), $osC_Product->getTitle() . $str_variants, 'style="width: 160px"'); ?>
          	</li>
            <?php 
                }
			?>
		</ul>
        <p>
            <span style="float: right"><a class="btn btn-mini multibox" rel="ajax:true" href="<?php echo osc_href_link(FILENAME_JSON, 'module=products&action=compare_products&template=' . $osC_Template->getCode()); ?>"><?php echo $osC_Language->get('button_compare_now'); ?></a></span>
            <a class="btn btn-mini" href="<?php echo osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=compare_products_clear'); ?>"><?php echo $osC_Language->get('button_clear'); ?></a>
        </p>
		<script type="text/javascript">
        window.addEvent("domready",function() {
            var box = new MultiBox('multibox', { 
                movieWidth: 820,
                movieHeight: 600
            });
        });
        </script>
    </div>
</div>

<!-- box information end //-->
