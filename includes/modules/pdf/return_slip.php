<?php
/*
  $Id: return_slip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/order_return.php');
  
  class toC_Return_Slip_PDF {

    var $_pdf = null,
        $_title = null,
        $_order_return = null;
  
    function toC_Return_Slip_PDF() {
      global $osC_Customer;
      
      $this->_order_return = new toC_Order_Return($_GET['orders_returns_id']);
      $customer_info = $this->_order_return->getCustomerInfo();
      
      $logged_in_customers_id = $osC_Customer->getID();
      if ($logged_in_customers_id != $customer_info['id']) {
          die ('You are not allowed to print this return slip');
      }
      
      $this->_pdf = new TOCPDF('P', 'mm', 'A4', true, 'UTF-8');
      $this->_pdf->SetCreator('TomatoCart');
      $this->_pdf->SetAuthor('TomatoCart');
      $this->_pdf->SetTitle('Return Slip');
      $this->_pdf->SetSubject($orders_returns_id . ': ' . $customer_info['name']);
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
      $this->_pdf->MultiCell(70, 4, $osC_Language->get('return_slip_heading_title'), 0, 'L');
      
      //Date purchase & order ID field title
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_FIELD_Y);
      $this->_pdf->SetX(135);
      $this->_pdf->MultiCell(55, 4, $osC_Language->get('operation_heading_return_id') . "\n" . $osC_Language->get('operation_heading_return_date') . "\n" . $osC_Language->get('operation_heading_order_id') . "\n" . $osC_Language->get('operation_heading_order_date'), 0, 'L');

      //Date purchase & order ID field value
      $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_DOC_INFO_VALUE_Y);
      $this->_pdf->SetX(150);
      $this->_pdf->MultiCell(40, 4, $this->_order_return->getOrdersReturnsId()  . "\n" . osC_DateTime::getShort($this->_order_return->getReturnDate()) . "\n" . $this->_order_return->getOrdersId() . "\n" . osC_DateTime::getShort($this->_order_return->getDatePurchased()), 0, 'R');

      //Products
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_TABLE_HEADING_FONT_SIZE);
      $this->_pdf->SetY(TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y + $pos);
      $this->_pdf->Cell(12, 6, '', 'TB', 0, 'R', 0);
      $this->_pdf->Cell(140, 6, $osC_Language->get('heading_products_name'), 'TB', 0, 'L', 0);
      $this->_pdf->Cell(24, 6,  $osC_Language->get('heading_products_quantity'), 'TB', 0, 'L', 0);
      $this->_pdf->Ln();
      
      $i = 0;
      $y_table_position = TOC_PDF_POS_PRODUCTS_TABLE_CONTENT_Y + $pos;
      
      foreach ($this->_order_return->getProducts() as $products) {
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
        $this->_pdf->MultiCell(5, 4, $products['qty'], 0, 'C');
        
        $y_table_position += $rowspan * TOC_PDF_TABLE_CONTENT_HEIGHT;
        $i++;
      }
      $this->_pdf->SetY($y_table_position + 1);
      $this->_pdf->Cell(175, 7, '', 'T', 0, 'C', 0);

      //Comments:
      $this->_pdf->ln();
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->Cell(55, 4, $osC_Language->get('operation_heading_comments'), 0, 'L');
      $this->_pdf->ln();
      $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->MultiCell(175, 4, $this->_order_return->getComment(), 0, 'L');
      
      $this->_pdf->ln(10);
      $this->_pdf->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->MultiCell(175, 4, $osC_Language->get('operation_heading_reminder'), '', 'L');

      $this->_pdf->ln();
      $this->_pdf->SetFont(TOC_PDF_FONT, '', TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE);
      $this->_pdf->MultiCell(175, 4, str_replace('<br />', "\n", $osC_Language->get('return_slip_reminder')), 'LTRB', 'L');

      $this->_pdf->Output("Return Slip", "I");
    }
  }
?>