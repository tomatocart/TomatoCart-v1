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

class toC_Json_Popup_Cart {

    function getCartContents() {
        global $osC_Language, $osC_ShoppingCart, $osC_Currencies, $toC_Json, $osC_Image;

        $content =  '<h6>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('box_shopping_cart_heading')) . '</h6>' .
                    '<div class="content">';


        if ($osC_ShoppingCart->hasContents()) {
            $content .= '<table border="0" width="100%" cellspacing="4" cellpadding="2">';

            foreach ($osC_ShoppingCart->getProducts() as $product) {
                $content .= '  <tr>' .
          						'    <td valign="top" align="center" width="60">' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Image->show($product['image'], $product['name'], '', 'mini')) . '</td>' .
                      '    <td valign="top">' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $product['name']). '<br/>Quantity:' . $product['quantity'] . '<br/><span class="price">' . $osC_Currencies->format($product['price']) . '</span><br/><span>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['id']), 'More Info') . '</span></td>' .
                      '  </tr>';
            }

            $content .= '</table>';
        } else {
            $content .= '<strong class="cartEmpty">' . $osC_Language->get('box_shopping_cart_empty') . '</strong>';
        }

        $content .= '<strong class="subtotal">' . $osC_Language->get('box_shopping_cart_subtotal') . '&nbsp;&nbsp;' . $osC_Currencies->format($osC_ShoppingCart->getSubTotal()) . '</strong>
                  </div>';

        $response = array('success' => true, 'content' => $content);

        echo $toC_Json->encode($response);
    }
}
