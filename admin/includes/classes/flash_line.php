<?php
/*
  $Id: flash_line.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/flash_chart.php');

  /**
   * toC_Flash_Line
   */
  class toC_Flash_Line extends toC_Flash_Chart {

    /**
     * Constructor
     */
    function toC_Flash_Line($title = ''){
      parent::toC_Flash_Chart($title);

      //initialize line
      $this->_graph->width = $this->line_chart_width;
      $this->_graph->height = $this->line_chart_height;

      $this->_graph->x_axis_colour($this->x_axis_color, $this->x_axis_grid);
      $this->_graph->y_axis_colour($this->y_axis_color, $this->y_axis_grid);
      $this->_graph->set_x_label_style( $this->x_label_size, $this->x_label_color, $this->x_label_orientation , $this->x_step);
      $this->_graph->set_y_label_style( $this->y_label_size, $this->y_label_color );

      $this->_graph->set_x_axis_steps($this->x_step);
      $this->_graph->y_label_steps($this->y_step);
    }

    function setXAxisColor( $axis_color, $grid_color = ''){
      $this->_graph->x_axis_colour( $axis_color, $grid_color = '');
    }

    function setYAxisColor( $axis_color, $grid_color = ''){
      $this->_graph->y_axis_colour( $axis_color, $grid_color = '');
    }

    function setLabelStep($x_step, $y_step){
      $this->_graph->set_x_axis_steps($x_step);
      $this->_graph->y_label_steps($y_step);
    }

    function setYMax($max_value){
      $this->_graph->set_y_max($max_value);
    }

    function setYMin($min_value){
      $this->_graph->set_y_min($min_value);
    }
  }

  /**
   * toC_Flash_Dot_Line
   */
  class toC_Flash_Dot_Line extends toC_Flash_Line {

    function toC_Flash_Dot_Line($title, $line_text = '', $width = null, $dot_size = null, $color = null, $font_size= null){
      parent::toC_Flash_Line($title);

      //initialize pie parameters
      $width = ($width == null) ? $this->line_width : $width;
      $dot_size = ($dot_size == null) ? $this->line_dot_size : $dot_size;
      $color = ($color == null) ? $this->line_color : $color;
      $font_size = ($font_size == null) ? $this->font_size : $font_size;

      $this->_graph->line_dot($width, $dot_size, $color, $line_text, $font_size);
   }

   function setData($data){
     if (is_array($data)){
       $this->_graph->set_data(array_values($data));
       $this->_graph->set_x_labels( array_keys($data));
     }
   }
 }

  /**
   * toC_Flash_Last_Visits_Chart
   */
  class toC_Flash_Last_Visits_Chart extends toC_Flash_Dot_Line {

    function toC_Flash_Last_Visits_Chart($title = ''){
      parent::toC_Flash_Dot_Line($title);
   }

   function setData($data){

     $x_labels = array();
     foreach ($data as $date => $visits){
       $x_labels[] = osC_DateTime::getShort($date);
     }

     $this->_graph->set_data(array_values($data));
     $this->_graph->set_x_labels($x_labels);

     $max_value = (int)(floor(max(array_values($data)) / 10 + 1) * 10);
     $this->setYMin(0);
     $this->setYMax($max_value);
   }
 }
?>
