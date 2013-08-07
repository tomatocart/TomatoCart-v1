<?php
/*
  $Id: print_order.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include("includes/classes/order.php");
  
  class toC_Print_order_PDF {
    var $_pdf = null,
        $_order = null;
    
    function toC_Print_order_PDF() {
      global $osC_Customer;
      
      $this->_order = new osC_Order($_REQUEST['orders_id']);
      
      $customer_info = $this->_order->getBilling();
      $customer_info['email_address'] = $this->_order->getCustomer['email_address'];
      
      $customers_id = $this->_order->getCustomer('id');
      $logged_in_customers_id = $osC_Customer->getID();
      if ($customers_id != $osC_Customer->getID()) {
          die ('You are not allowed to print this order');
      }
      
      $this->_pdf = new TOCPDF('P', 'mm', 'A4', true, 'UTF-8');
      $this->_pdf->SetCreator('TomatoCart');
      $this->_pdf->SetAuthor('TomatoCart');
      $this->_pdf->SetTitle('Order');
      $this->_pdf->SetSubject($_REQUEST['orders_id'] . ': ' . $customer_info['name']);
      $this->_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
      
      $this->_pdf->setCustomerInfo($customer_info);
    }
    
    function render() {
     global $osC_Database, $osC_Language;

     //New Page
     $this->_pdf->AddPage();
     //Title
     $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TITLE_FONT_SIZE);
     $this->_pdf->SetY(TOC_PDF_POS_HEADING_TITLE_Y);
     
     $Qinvoices = $osC_Database->query('select invoice_number from :table_orders where orders_id = :orders_id');
     $Qinvoices->bindTable(':table_orders', TABLE_ORDERS);
     $Qinvoices->bindInt(':orders_id', $_REQUEST['orders_id']);
     $Qinvoices->execute();
     
     if($Qinvoices->value('invoice_number') > 0) {
       //Title
       $this->_pdf->MultiCell(70, 4, $osC_Language->get('pdf_invoice_heading_title'), 0, 'L');
     } else {
        //Title
       $this->_pdf->MultiCell(70, 4, $osC_Language->get('pdf_order_heading_title'), 0, 'L');
     }
     //Date purchase & order ID field title
     $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
     $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_FIELD_Y);
     $this->_pdf->SetX(135);
     
     if($Qinvoices->value('invoice_number') > 0) { 
       $this->_pdf->MultiCell(55, 4, $osC_Language->get('operation_heading_invoice_number') . "\n" . $osC_Language->get('operation_heading_invoice_date') . "\n" . $osC_Language->get('operation_heading_order_id') , 0, 'L');     
     } else {
       $this->_pdf->MultiCell(55, 4, $osC_Language->get('operation_heading_order_date') . "\n" . $osC_Language->get('operation_heading_order_id') , 0, 'L');
     }
     //Date purchase & order ID field value
     $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
     $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_VALUE_Y);
     $this->_pdf->SetX(150);
     
     if($Qinvoices->value('invoice_number') > 0) {
       $this->_pdf->MultiCell(40, 4, $this->_order->getInvoiceNumber() . "\n" . osC_DateTime::getShort($this->_order->getInvoiceDate()) . "\n" . $this->_order->getOrderID(), 0, 'R');     
     } else {
       $this->_pdf->MultiCell(40, 4, osC_DateTime::getShort($this->_order->getDateCreated()) . "\n" . $this->_order->getOrderID(), 0, 'R');
     }
      
      //Products
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y);
      $this->_pdf->Cell(8, 6, '', 'TB', 0, 'R', 0);
      $this->_pdf->Cell(78, 6, $osC_Language->get('heading_products_name'), 'TB', 0, 'C', 0);
      $this->_pdf->Cell(35, 6,  $osC_Language->get('heading_products_quantity'), 'TB', 0, 'C', 0);
      $this->_pdf->Cell(30, 6, $osC_Language->get('heading_products_price'), 'TB', 0, 'R', 0);
      $this->_pdf->Cell(30, 6, $osC_Language->get('heading_products_total'), 'TB', 0, 'R', 0);
      $this->_pdf->Ln();
      
      $i = 0;
      $y_table_position = TOC_PDF_POS_PRODUCTS_TABLE_CONTENT_Y;
      $osC_Currencies = new osC_Currencies();
      
      foreach ($this->_order->getProducts() as $index => $products) {
        $rowspan = 1;
        
        //Pos
        $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_CONTENT_FONT_SIZE);
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->MultiCell(8, 4, ($i + 1), 0, 'C');
      
        //Product
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(30);
        
        $product_info = $products['name'];
        if (strlen($products['name']) > 30) {
          $rowspan = 2;
        }
        
        if ( $products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
          $product_info .= "\n" . '   -' . $osC_Language->get('senders_name') . ': ' . $products['senders_name'];
          
          if ($products['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $product_info .= "\n" . '   -' . $osC_Language->get('senders_email') . ': ' . $products['senders_email'];
            $rowspan++;
          }
          
          $product_info .= "\n" . '   -' . $osC_Language->get('recipients_name') . ': ' . $products['recipients_name'];
          
          if ($products['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $product_info .= "\n" . '   -' . $osC_Language->get('recipients_email') . ': ' . $products['recipients_email'];
            $rowspan++;
          }
          
          $rowspan += 3;
          $product_info .= "\n" . '   -' . $osC_Language->get('messages') . ': ' . $products['messages'];
        }
        
        if (isset( $products['variants'] ) && ( sizeof( $products['variants'] ) > 0)) {
          foreach ( $products['variants'] as $variant ) {
            $product_info .=  "\n" . $variant['groups_name'] . ": " . $variant['values_name'];
            $rowspan++;
          } 
        } 
        $this->_pdf->MultiCell(80, 4, $product_info, 0, 'L');          
  
        //Quantity
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX( 110 );
        $this->_pdf->MultiCell(5, 4, $products['qty'], 0, 'C');
        
        //Price
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(135);
        $price = $osC_Currencies->displayPriceWithTaxRate($products['final_price'], $products['tax'], 1, $this->_order->getCurrency(), $this->_order->getCurrencyValue());
        $price = str_replace('&nbsp;',' ',$price);
        $this->_pdf->MultiCell(20, 4, $price, 0, 'R');
        
        //Total
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(165);
        $total = $osC_Currencies->displayPriceWithTaxRate($products['final_price'], $products['tax'], $products['qty'], $this->_order->getCurrency(), $this->_order->getCurrencyValue());
        $total = str_replace('&nbsp;', ' ', $total);
        $this->_pdf->MultiCell(20, 4, $total, 0, 'R');
        
        $y_table_position += $rowspan * TOC_PDF_TABLE_CONTENT_HEIGHT;
        
        //products list exceed page height, create a new page
        if (($y_table_position - TOC_PDF_POS_CONTENT_Y - 6) > 160) { 
          $this->_pdf->AddPage();
          
          $y_table_position = TOC_PDF_POS_CONTENT_Y + 6;
          $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
          $this->_pdf->SetY(TOC_PDF_POS_CONTENT_Y);
          $this->_pdf->Cell(8, 6, '', 'TB', 0, 'R', 0);
          $this->_pdf->Cell(78, 6, $osC_Language->get('heading_products_name'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(35, 6,  $osC_Language->get('heading_products_quantity'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(30, 6, $osC_Language->get('heading_products_price'), 'TB', 0, 'R', 0);
          $this->_pdf->Cell(30, 6, $osC_Language->get('heading_products_total'), 'TB', 0, 'R', 0);
          $this->_pdf->Ln();
        }      
        $i++;
      }
      $this->_pdf->SetY($y_table_position + 1);
      $this->_pdf->Cell(180, 7, '', 'T', 0, 'C', 0);

      //Order Totals
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_CONTENT_FONT_SIZE);
      foreach ( $this->_order->getTotals() as $totals ) {
      
        $y_table_position += 4;
        $this->_pdf->SetFont(TOC_PDF_FONT, 'B', 8);
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(40);
        $this->_pdf->MultiCell(120, 5, $totals['title'], 0, 'R');
  
        $total_text = str_replace('&nbsp;', ' ', $totals['text']);
        
        $this->_pdf->SetFont(TOC_PDF_FONT, 'B', 8);
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(145);
        $this->_pdf->MultiCell(40, 5, strip_tags($total_text), 0, 'R');
      }
      if($Qinvoices->value('invoice_number') > 0) {
        $this->_pdf->Output("Invoice", "I");
      } else {
        $this->_pdf->Output("Orders", "I");
      }
    }
  }
?>