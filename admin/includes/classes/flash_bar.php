<?php
/*
  $Id: flash_bar.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
 require_once('includes/classes/flash_line.php');
 require_once('includes/classes/currencies.php');

  class toC_Flash_Bar extends toC_Flash_Line {

/* Class constructor */

    function toC_Flash_Bar($title = ''){
      parent::toC_Flash_Line($title);

      $this->_bar = new bar_outline($this->bar_alpha, $this->bar_color, $this->outline_color);
    }

    function setData($data){
      $this->_bar->add_data_tip(array_values($order_total));
      $this->_graph->set_x_labels( array_keys($data) );
      $this->_graph->set_y_max(max($this->_bar->data));

      $this->_graph->data_sets[] = $this->_bar;
    }
  }

  class toC_Flash_Bar_Order_Total extends toC_Flash_Bar{

/* Class constructor */

    function toC_Flash_Bar_Order_Total($title = ''){
      parent::toC_Flash_Bar($title);
    }

    function setData($data){
      global $osC_Currencies;

      $x_labels = array();
      foreach ($data as $date => $order_total){
        $this->_bar->add($order_total);
        $x_labels[] = osC_DateTime::getShort($date);
      }
      $this->_graph->set_x_labels($x_labels);


      if(!is_object($osC_Currencies))
        $osC_Currencies = new osC_Currencies();

      $this->_graph->set_tool_tip(' #x_label# <br>' . $osC_Currencies->getSymbolLeft() . ' #val#');

      $max_total = (int) (floor(max($this->_bar->data) / 100 + 1) * 100);
      $this->_graph->set_y_min(0);
      $this->_graph->set_y_max($max_total);

      $this->_graph->data_sets[] = $this->_bar;
    }
  }
  
  class toC_Flash_Bar_Visitors_Continent extends toC_Flash_Bar{

/* Class constructor */

    function toC_Flash_Bar_Visitors_Country($title = ''){
      parent::toC_Flash_Bar($title);
    }

    function setData($data){
      global $osC_Currencies;

      $x_labels = array();
      foreach ($data as $date => $order_total){
        $this->_bar->add($order_total);
        $x_labels[] = $date;
      }
      $this->_graph->set_x_labels($x_labels);


      if(!is_object($osC_Currencies))
        $osC_Currencies = new osC_Currencies();

      $this->_graph->set_tool_tip('');

      $max_total = (int) (floor(max($this->_bar->data) / 100 + 1) * 100);
      $this->_graph->set_y_min(0);
      $this->_graph->set_y_max($max_total);

      $this->_graph->data_sets[] = $this->_bar;
    }
  }  

?>
