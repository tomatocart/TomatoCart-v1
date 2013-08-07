<?php
/*
  $Id: flash_pie.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/flash_chart.php');

  class toC_Flash_Pie extends toC_Flash_Chart {

    function toC_Flash_Pie ($title = '', $alpha = null, $line_color = null, $style = null, $gradient = null, $border_size = null) {
      parent::toC_Flash_Chart($title);

      //initialize pie parameters
      $alpha = ($alpha == null) ? $this->pie_alpha : $alpha;
      $style = ($style == null) ? $this->pie_style : $style;
      $gradient = ($gradient == null) ? $this->pie_gradient : $gradient;
      $line_color = ($line_color == null) ? $this->pie_line_color : $line_color;
      $border_size = ($border_size == null) ? $this->pie_border_size : $border_size;

      $this->_graph->pie($alpha, $line_color, $style, $gradient);
      $this->_graph->set_tool_tip($this->pie_tool_tip);

      $this->_graph->pie_slice_colours($this->pie_slice_colours);
    }

    function setData ($data) {
      if (is_array($data)) {
        if(sizeof($data) == 0){
           $this->_graph->pie_values(0, array('   No Data'));
        } else {
          $labels = array_keys($data);
          $values = array_values($data);

          $this->_graph->pie_values($values, $labels);
        }
      }
    }

    function setPieToolTip($tool_tip) {
      $this->_graph->set_tool_tip($tool_tip);
    }
  }

  class toC_Flash_Pie_Table {
    var $table_head_data = null;
    var $data = null;
    var $display_percentage_column = false;

    function toC_Flash_Pie_Table ($table_head_data, $data, $display_percentage_column = false) {
      $this->table_head_data = $table_head_data;
      $this->data = $data;
      $this->display_percentage_column = $display_percentage_column;
    }

    function setDisplayPercentageColumn ($value) {
      $this->display_percentage_column = $value;
    }

    function render(){
      global $osC_Language;

      $value_total = array_sum($this->data);
      $pie_chart = new toC_Flash_Pie('', null, null, '{display:none}');
      $pie_chart->setData($this->data);

      echo '<table cellpadding="2" cellspacing="0" border="0" class="dataTable" width="100%">';

      if ( !empty($this->table_head_data) && is_array($this->table_head_data) ) {
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . $this->table_head_data[0] . '</th>';
        echo '<th>' . $this->table_head_data[1] . '</th>';

        if ($this->display_percentage_column === true) {
          echo '<th>' . $osC_Language->get('table_head_percentage') . '</th>';
        }

        echo '<th>&nbsp;</th>';
        echo '</tr>';
        echo '</thead>';
      }

      echo '<tbody>';
      if ( is_array($this->data) && sizeof($this->data) > 0 ) {
        $i = 0;
        $chart_displayed  = false;
        foreach($this->data as $label => $value) {
          echo '<tr onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);" >';
          echo '<td><font style="background-color:' . $pie_chart->pie_slice_colours[$i % 12] . '">&nbsp;&nbsp;</font>&nbsp;' . $label . '</td>';
          echo '<td>' . $value . '</td>';

          if($this->display_percentage_column === true){
            echo '<td>' . sprintf('%.2f%%', ($value * 100) / $value_total) . '</td>';
          }

          if ($chart_displayed === false) {
            echo '<td rowspan="' . $value_total . '" style="background-color:' . $pie_chart->getBgColor() . '" align="center" valign="middle" width="' . ($pie_chart->getWidth() + 50) . '">';
            echo $pie_chart->render();
            echo '</td>';
            $chart_displayed = true;
          }
          $i++;
        }

      } else {
        echo '<tr><td colspan="4" align="center">' . $osC_Language->get('no_data_available') . '</td></tr>';
      }

      echo '</tbody>';
      echo '</table>';
    }
  }

  class toC_Flash_Pie_Simple{
    var $data = null;
    var $display_percentage_column = false;

    function toC_Flash_Pie_Simple ($data) {
      $this->data = $data;
    }

    function render(){
      global $osC_Language;

      $pie_chart = new toC_Flash_Pie('', null, null, '{display:none}');
      $pie_chart->setData($this->data);
      $pie_chart->setWidth(120);
      $pie_chart->setHeight(120);

      if (is_array($this->data) && sizeof($this->data) > 0) {
        echo '<span style="float:left;">';
        echo $pie_chart->render();
        echo '</span>';

        echo '<span style="float:left;padding-left:20px;padding-top:10px;line-height:30px">';
        $i = 0;
        foreach($this->data as $label => $value) {
          echo '<font style="background-color:' . $pie_chart->pie_slice_colours[$i % 12] . '">&nbsp;&nbsp;</font>&nbsp;' . $label . '<br/>';
          $i++;
        }
        echo '</span>';
      }
    }
  }
?>
