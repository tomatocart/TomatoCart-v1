<?php
/*
  $Id: flash_chart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include_once 'external/open-flash-chart/open-flash-chart.php';

  class toC_Flash_Chart {

    /*The open flash file path*/
    var $_swf_path = 'external/open-flash-chart/';

    /*The swfobject object path*/
    var $_js_path  = 'external/swfobject/';

    /*The graph instance*/
    var $_graph;

    /*Bar alpha*/
    var $_chart_title_style = '{font-size: 20px; color: #555555; }';

    var $_bg_color ='#ffffff';

    /*************************** begin: bar chart ***************************/
    var $bar_alpha = 80;
    var $bar_color = '#0077cc';
    var $outline_color = '#0077cc';
    /*************************** begin: bar chart ***************************/

    /*************************** begin: line chart ***************************/
    var $line_chart_width = '100%';
    var $line_chart_height = '150';
    var $line_width = 3;
    var $line_dot_size = 5;
    var $line_color = '#0077cc';
    var $font_size = 10;

    var $x_axis_color = '#aaaaaa';
    var $x_axis_grid = '#eeeeee';
    var $x_label_color = '#000000';
    var $x_label_size = '10';
    var $x_label_orientation = 0;
    var $x_step = 7;


    var $y_axis_color = '#aaaaaa';
    var $y_axis_grid = '#eeeeee';
    var $y_label_color = '#000000';
    var $y_label_size = '10';
    var $y_step = 2;
    /*************************** end: line chart *****************************/

    /*************************** begin: pie data ***************************/
    var $pie_alpha = 80;
    var $pie_line_color = '#FFFFFF';
    var $pie_style = '{font-size:10px; font-weight:bold; color:#333}';
    var $pie_gradient = false;
    var $pie_border_size = false;
    var $pie_tool_tip = '#x_label# <br> #val#';
    var $pie_width = '150';
    var $pie_height = '150';
    var $pie_slice_colours = array('#058DC7', '#50B432', '#ED561B' , '#EDEF00', '#FF0000', '#8200C3', '#24CBE5', '#64E572', '#FF9655', '#FFF263' , '#FF6250', '#BE3CFF');
    /*************************** end: pie data *****************************/

    /**
     * Constructor
     */
    function toC_Flash_Chart($title = ''){

      //create graph object and initalize the pass
      $this->_graph = new graph();
      $this->_graph->set_swf_path( $this->_swf_path );
      $this->_graph->set_js_path( $this->_js_path );

      $this->_graph->title( $title, $this->_chart_title_style );
        $this->_graph->set_bg_colour($this->_bg_color);
    }

    /**
     * Set chart title
     */
    function setChartTitle( $chart_title ){
      $this->_graph->title( $chart_title, $this->_chart_title_style );
    }

    /**
     * Set bar key
     */
    function setBarKey(  $key, $size ){
      $this->_bar->key( $key, $size );
    }

    /**
     * Set chart title
     */
    function setData( $data ){

    }

    /**
     * Output the chart
     */
    function render(){
      //$this->_graph->set_output_type('js');
      echo $this->_graph->render();
    }

    function getBgColor(){
      return $this->_graph->bg_colour;
    }

    function setWidth($width){
      return $this->_graph->width = $width;
    }

    function setHeight($height){
      return $this->_graph->height = $height;
    }

    function getWidth(){
      return $this->_graph->width;
    }

    function getHeight(){
      return $this->_graph->height;
    }
    
    function setToolTip($tool_tip) {
      $this->_graph->set_tool_tip($tool_tip);
    }
 }
?>
