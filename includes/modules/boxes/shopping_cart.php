<?php
/*
  $Id: shopping_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_shopping_cart extends osC_Modules {
    var $_title,
        $_code = 'shopping_cart',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_shopping_cart() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_shopping_cart_heading');
    }

    function initialize() {
      global $osC_Language, $osC_Template, $osC_Session, $osC_Currencies;
      
      $this->_title_link = osc_href_link(FILENAME_CHECKOUT, null, 'SSL');
      
      $content = '<div id="ajaxCartContent">' .
                  '<div id="ajaxCartContentShort" class="collapsed">' .
                    '<span class="cartTotal"></span>' .  
                    '<span class="quantity"></span> ' . $osC_Language->get('text_items') .
                  '</div>' .
                  '<div id="ajaxCartContentLong" class="expanded">' .
                    '<ul class="products collapsed" id="ajaxCartContentProducts"></ul>' .
                    '<p id="ajaxCartContentNoProducts" class="collapsed">' . $osC_Language->get('No products') . '</p>' .
                    '<div id="ajaxCartButtons">' .
                      osc_link_object(osc_href_link(FILENAME_CHECKOUT), osc_draw_image_button('button_ajax_cart.png'), 'style="margin-right:30px;"') .
                      osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'payment'), osc_draw_image_button('button_ajax_cart_checkout.png')) .
                      '<div style="visibility:hidden">' . 
                        '<span>clear-bug-div</span>' .
                      '</div>' .
                    '</div>' .
                  '</div>' .
                 '</div>';
                      
      $css = '#ajaxCartContent {overflow: hidden;}' . chr(13) . 
             '.boxTitle #ajaxCartCollapse, .boxTitle #ajaxCartExpand {cursor:pointer;position:relative;top:3px;}' . chr(13) .
             '.hidden {display: none;}' . chr(13) .
             '.expanded {display: block;}' . chr(13) .
             '.collapsed {display: none;}' . chr(13) .
             '.strike {text-decoration:line-through;}' . chr(13) .
             '#ajaxCartContentShort span{ padding: 0 2px;}' . chr(13) .
             '#ajaxCartButtons {margin-top:10px;}' . chr(13) .
             '#ajaxCartButtons a {padding: 1px;text-align: center;text-decoration: none;}' . chr(13) .
             '#ajaxCartOrderTotals span.orderTotalText {float: right}' . chr(13) .
             '#ajaxCartContentLong ul.products {text-align: left;}' .  chr(13) .
             '#ajaxCartContentLong ul li {padding: 6px 0;position: relative;line-height:16px;}' . chr(13) .
             '#ajaxCartContentLong ul.products span.price {display:block;position:absolute;right:15px;top:6px;}' . chr(13) .
             '#ajaxCartContentLong ul.products .removeProduct {cursor: pointer;display: block;width: 11px;height: 13px;position: absolute;right: 0;top: 8px;background: url(includes/languages/' . $osC_Language->getCode() . '/images/buttons/button_ajax_cart_delete.gif) no-repeat left top;}' . chr(13) .
             '#ajaxCartContentLong #ajax_cart_prices {padding: 5px 0;border-top : 1px dashed #777F7D;}' . chr(13) .
             '#ajaxCartOrderTotals {padding:5px 0;border-top: 1px dashed #CCCCCC;}' . chr(13) .
             '#ajaxCartContentLong #ajaxCartOrderTotals li {padding: 2px;font-size: 11px}' . chr(13) .
             '#ajaxCartContentLong p{color: #616060;padding-bottom:5px;margin: 0}' . chr(13) .
             '#ajaxCartContentLong p.variants, #ajaxCartContentLong p.customizations { padding: 2px;margin: 0 0 0 5px; }' . chr(13) .
             '#ajaxCartContentShort span.cartTotal {float:right; font-weight: bold}' . chr(13) .
             '#ajaxCartContentProducts dd span {display:block;padding-left:32px;}' . "\n\n";                                 
      
      $osC_Template->addStyleDeclaration($css);
      $osC_Template->addJavascriptFilename('includes/javascript/ajax_shopping_cart.js');
      
      $js .= '<script type="text/javascript">
                window.addEvent("domready",function() {
                  ajaxCart = new AjaxShoppingCart({
                    sessionId : "' . $osC_Session->getID() . '",
                    currentUrl: "' . osc_get_current_url() . '",
                    error_sender_name_empty: "' . $osC_Language->get('error_sender_name_empty') . '",
                    error_sender_email_empty: "' . $osC_Language->get('error_sender_email_empty') . '",
                    error_recipient_name_empty: "' . $osC_Language->get('error_recipient_name_empty') . '",
                    error_recipient_email_empty: "' . $osC_Language->get('error_recipient_email_empty') . '",
                    error_message_empty: "' . $osC_Language->get('error_message_empty') . '",
                    error_message_open_gift_certificate_amount: "' . $osC_Language->get('error_message_open_gift_certificate_amount') . '"
                  });
                });
              </script>';
      
      $this->_content = $content . "\n" . $js;
    }
  }
?>
