<?php
/*
 $Id: gift_certificate.php $
TomatoCart Open Source Shopping Cart Solutions
http://www.tomatocart.com

Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License v2 (1991)
as published by the Free Software Foundation.
*/

class osC_OrderTotal_gift_certificate extends osC_OrderTotal {
    var $output;

    var $_title,
    $_code = 'gift_certificate',
    $_status = false,
    $_tax_class = null,
    $_sort_order;

    function osC_OrderTotal_gift_certificate() {
        global $osC_Language;

        $this->output = array();

        $this->_title = $osC_Language->get('order_total_gift_certificate_title');
        $this->_description = $osC_Language->get('order_total_gift_certificate_description');
        $this->_status = (defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS') && (MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS == 'true') ? true : false);
        $this->_sort_order = (defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER') ? MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER : null);
    }

    function process() {
        global $osC_ShoppingCart, $osC_Currencies;

        if (!$osC_ShoppingCart->hasGiftCertificate()) {
            return;
        }

        if (!class_exists('toC_Gift_Certificates')) {
            require_once('includes/classes/gift_certificates.php');
        }

        $total_amount = 0;
        $gift_certificates = array();
        $order_total = $osC_ShoppingCart->getTotal();
        $gift_certificate_codes = $osC_ShoppingCart->getGiftCertificateCodes();
        $exceed_total_amount = ($order_total == 0) ? true : false;

        foreach($gift_certificate_codes as $gift_certificate_code) {
            if ($exceed_total_amount == false) {
                $redeem_amount = toC_Gift_Certificates::getGiftCertificateAmount($gift_certificate_code);
                $total_amount += $redeem_amount;

                if ($total_amount >= $order_total) {
                    $redeem_amount = $redeem_amount - ($total_amount - $order_total);

                    $total_amount = $order_total;
                    $exceed_total_amount = true;
                }

                $gift_certificates[] = $gift_certificate_code . '[' . $osC_Currencies->format($redeem_amount) . ']';
                $osC_ShoppingCart->setGiftCertificateRedeemAmount($gift_certificate_code, $redeem_amount);
            } else {
                $osC_ShoppingCart->deleteGiftCertificate($gift_certificate_code, false);
            }
        }

        $osC_ShoppingCart->addToTotal((-1) * $total_amount);

        if ($osC_ShoppingCart->isTotalZero()) {
            $osC_ShoppingCart->resetBillingMethod(false);
        }

        if ($order_total != 0) {
            $this->output[] = array('title' => $this->_title . ' (' . implode(', ', $gift_certificates) . ') : ',
                                                    'text' => '-' . $osC_Currencies->format($total_amount),
                                                    'value' => $total_amount * (-1));
        }
    }
}
?>
