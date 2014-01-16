<?php
/*
  $Id: invoice.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/order.php');

  class toC_Invoice_PDF {

    var $_pdf = null,
        $_title = null,
        $_order = null;
  
    function toC_Invoice_PDF() {
      $this->_order = new osC_Order($_REQUEST['orders_id']);
      
      $customer_info = $this->_order->getBilling();
      $customer_info['email_address'] = $this->_order->getCustomer('email_address');
      
      $this->_pdf = new TOCPDF('P', 'mm', 'A4', true, 'UTF-8');
      $this->_pdf->SetCreator('TomatoCart');
      $this->_pdf->SetAuthor('TomatoCart');
      $this->_pdf->SetTitle('Order');
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
      $this->_pdf->MultiCell(70, 4, $osC_Language->get('pdf_invoice_heading_title'), 0, 'L');
      
      //Date purchase & order ID field title
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_FIELD_Y);
      $this->_pdf->SetX(135);
      $this->_pdf->MultiCell(55, 4, $osC_Language->get('operation_heading_invoice_number') . ':' . "\n" . $osC_Language->get('operation_heading_invoice_date') . ':' . "\n" . $osC_Language->get('operation_heading_order_id') . ':' , 0, 'L');

      //Date purchase & order ID field value
      $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_VALUE_Y);
      $this->_pdf->SetX(150);
      $this->_pdf->MultiCell(40, 4, $this->_order->getInvoiceNumber() . "\n" . osC_DateTime::getShort($this->_order->getInvoiceDate()) . "\n" . $this->_order->getOrderID(), 0, 'R');
      
      //Products
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y);
      $this->_pdf->Cell(8, 6, '', 'TB', 0, 'R', 0);
      $this->_pdf->Cell(78, 6, $osC_Language->get('table_heading_products'), 'TB', 0, 'C', 0);
      $this->_pdf->Cell(40, 6,  $osC_Language->get('table_heading_quantity'), 'TB', 0, 'C', 0);
      $this->_pdf->Cell(30, 6, $osC_Language->get('table_heading_price'), 'TB', 0, 'C', 0);
      $this->_pdf->Cell(30, 6, $osC_Language->get('table_heading_total'), 'TB', 0, 'C', 0);
      $this->_pdf->Ln();
      
      $i = 0;
      $y_table_position = TOC_PDF_POS_PRODUCTS_TABLE_CONTENT_Y;
      $osC_Currencies = new osC_Currencies_Admin();
      
      foreach ($this->_order->getProducts() as $products) {
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
        $this->_pdf->MultiCell(10, 4, $products['quantity'], 0, 'C');
        
        //Price
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(135);
        $price = $osC_Currencies->displayPriceWithTaxRate($products['final_price'], $products['tax'], 1, $this->_order->getCurrency(), $this->_order->getCurrencyValue());
        $price = str_replace('&nbsp;',' ',$price);
        $this->_pdf->MultiCell(30, 4, $price, 0, 'C');
        
        //Total
        $this->_pdf->SetY($y_table_position);
        $this->_pdf->SetX(165);
        $total = $osC_Currencies->displayPriceWithTaxRate($products['final_price'], $products['tax'], $products['quantity'], $this->_order->getCurrency(), $this->_order->getCurrencyValue());
        $total = str_replace('&nbsp;', ' ', $total);
        $this->_pdf->MultiCell(30, 4, $total, 0, 'C');
        
        $y_table_position += $rowspan * TOC_PDF_TABLE_CONTENT_HEIGHT;
        
        //products list exceed page height, create a new page
        if (($y_table_position - TOC_PDF_POS_CONTENT_Y - 6) > 160) { 
          $this->_pdf->AddPage();
          
          $y_table_position = TOC_PDF_POS_CONTENT_Y + 6;
          $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
          $this->_pdf->SetY(TOC_PDF_POS_CONTENT_Y);
          $this->_pdf->Cell(8, 6, '', 'TB', 0, 'R', 0);
          $this->_pdf->Cell(78, 6, $osC_Language->get('table_heading_products'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(40, 6,  $osC_Language->get('table_heading_quantity'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(30, 6, $osC_Language->get('table_heading_price'), 'TB', 0, 'C', 0);
          $this->_pdf->Cell(30, 6, $osC_Language->get('table_heading_total'), 'TB', 0, 'C', 0);
          $this->_pdf->Ln();
        }      
        $i++;
      }
      $this->_pdf->SetY($y_table_position + 1);
      $this->_pdf->Cell(186, 7, '', 'T', 0, 'C', 0);

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

      $this->_pdf->Output("Invoice", "I");
    }
  }
?>