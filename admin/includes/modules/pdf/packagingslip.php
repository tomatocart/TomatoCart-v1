<?php
/*
  $Id: packagingslip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/order.php');

  class toC_PackagingSlip_PDF {

    var $_pdf = null,
        $_title = null,
        $_order = null;
  
    function toC_PackagingSlip_PDF() {
      $this->_order = new osC_Order($_REQUEST['orders_id']);
      
      $customer_info = $this->_order->getBilling();
      $customer_info['email_address'] = $this->_order->getCustomer('email_address');
      
      $this->_pdf = new TOCPDF('P', 'mm', 'A4', true, 'UTF-8');
      $this->_pdf->SetCreator('TomatoCart');
      $this->_pdf->SetAuthor('TomatoCart');
      $this->_pdf->SetTitle('Packing Slip');
      $this->_pdf->SetSubject($order_id . ': ' . $customer_info['name']);
      $this->_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
      
      $this->_pdf->setCustomerInfo($customer_info);
    }

    function render () {
      global $osC_Language;
      
      //New Page
      $this->_pdf->AddPage(); 
      
      //Title
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TITLE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_HEADING_TITLE_Y);
      $this->_pdf->MultiCell(70, 4, $osC_Language->get('pdf_packaging_slip_heading_title'), 0, 'L');
      
      //Ship To Title
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_SHIP_TO_TITLE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_HEADING_TITLE_Y);
      $this->_pdf->SetX(70);
      $this->_pdf->MultiCell(100, 4, $osC_Language->get('operation_heading_ship_to') . ':', 0, 'L');
      
      //Shipping Address
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_SHIP_TO_ADDRESS_FONT_SIZE);
      $this->_pdf->SetX(70);
      $shipping_address = $this->_order->getDelivery();
      $this->_pdf->MultiCell(100, 4, $shipping_address['name'] . "\n" . $shipping_address['street_address'] . " " . $shipping_address['suburb'] . "\n" . $shipping_address['postcode'] . " " . $shipping_address['city'] . "\n" . $shipping_address['country_title']  . "\n" . $shipping_address['email_address'], 0, 'L');
      
      //Date purchase & order ID field title
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_FIELD_Y);
      $this->_pdf->SetX(135);
      $this->_pdf->MultiCell(55, 4, $osC_Language->get('operation_heading_invoice_number') . ':' . "\n" . $osC_Language->get('operation_heading_invoice_date') . ':' . "\n" . $osC_Language->get('operation_heading_order_id') . ':' , 0, 'L');

      //Date purchase & order ID field value
      $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_VALUE_Y);
      $this->_pdf->SetX(150);
      $this->_pdf->MultiCell(40, 4, $this->_order->getInvoiceNumber() . "\n" . osC_DateTime::getShort($this->_order->getInvoiceDate()) . "\n" . $this->_order->getOrderID()  , 0, 'R');
            
      //Products
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y + 10);
      $this->_pdf->Cell(12, 6, '', 'TB', 0, 'R', 0);
      $this->_pdf->Cell(140, 6, $osC_Language->get('table_heading_products'), 'TB', 0, 'L', 0);
      $this->_pdf->Cell(24, 6,  $osC_Language->get('table_heading_quantity'), 'TB', 0, 'L', 0);
      $this->_pdf->Ln();
      
      $i = 0;
      $y_table_position = TOC_PDF_POS_PRODUCTS_TABLE_CONTENT_Y + 10;
      
      foreach ($this->_order->getProducts() as $products) {
        $rowspan = 1;
        
        //Pos
        $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_CONTENT_FONT_SIZE);
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->MultiCell(12, 4, ($i + 1), 0, 'C');
      
        //Product
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(24);
        
        $product_info = $products['name'];
        if (strlen($products['name']) > 60) {
          $rowspan = 2;
        }
        
        if ( $products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
          $product_info .= "\n" . '   -' . $osC_Language->get('senders_name') . ': ' . $products['senders_name'];
          
          if ( $products['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL ) {
            $product_info .= "\n" . '   -' . $osC_Language->get('senders_email') . ': ' . $products['senders_email'];
            $rowspan++;
          }
          
          $product_info .= "\n" . '   -' . $osC_Language->get('recipients_name') . ': ' . $products['recipients_name'];
          
          if ( $products['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL ) {
            $product_info .= "\n" . '   -' . $osC_Language->get('recipients_email') . ': ' . $products['recipients_email'];
            $rowspan++;
          }
          
          $product_info .= "\n" . '   -' . $osC_Language->get('messages') . ': ' . $products['messages'];
          $rowspan += 3;
        }
        
        if (isset( $products['variants'] ) && ( sizeof( $products['variants'] ) > 0)) {
          foreach ( $products['variants'] as $variant ) {
            $product_info .=  "\n" . $variant['groups_name'] . ": " . $variant['values_name'];
            $rowspan++;
          } 
        } 
        $this->_pdf->MultiCell(120, 4, $product_info, 0, 'L');          
  
        //Quantity
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(164);
        $this->_pdf->MultiCell(10, 4, $products['quantity'], 0, 'C');
        
        $y_table_position += $rowspan * TOC_PDF_TABLE_CONTENT_HEIGHT;
        
        //products list exceed page height, create a new page
        if (($y_table_position - TOC_PDF_POS_CONTENT_Y - 6) > 150) { 
          $this->_pdf->AddPage();
          
          $y_table_position= TOC_PDF_POS_CONTENT_Y + 6;
          $this->_pdf->SetFont(TOC_PDF_FONT, 'B', 10);
          $this->_pdf->SetX(15);
          $this->_pdf->SetY($y_table_position - 6);
          $this->_pdf->Cell(9, 6, 'Pos.', 'TB', 0, 'C', 0);
          $this->_pdf->Cell(128, 6, $osC_Language->get('table_heading_products'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(30, 6,  $osC_Language->get('table_heading_quantity'), 'TB', 0, 'C', 0);
      
          $this->_pdf->Cell(12, 6, '', 'TB', 0, 'R', 0);
          $this->_pdf->Cell(140, 6, $osC_Language->get('table_heading_products'), 'TB', 0, 'L', 0);
          $this->_pdf->Cell(24, 6,  $osC_Language->get('table_heading_quantity'), 'TB', 0, 'L', 0);
      
          $this->_pdf->Ln();
        }      
        $i++;
      }
      $this->_pdf->SetY($y_table_position + 1);
      $this->_pdf->Cell(175, 7, '', 'T', 0, 'C', 0);

      $this->_pdf->Output("Packaging Slip", "I");
    }
  }
?>