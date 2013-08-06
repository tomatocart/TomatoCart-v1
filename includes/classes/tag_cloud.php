<?php
/*
  $Id: tag_cloud.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Tag_Cloud {
  
    var $_tags = array(),
        $_min_font_size,
        $_max_font_size,
        $_row_tags,
        $_tag_class;
        
    function toC_Tag_Cloud($tags, $tag_class = 'tag_cloud', $max_font_size = 25, $min_font_size = 9) {
      $this->_tags = $tags;
      $this->_tag_class = $tag_class;
      $this->_max_font_size = $max_font_size;
      $this->_min_font_size = $min_font_size;
    }
    
    function addTag($tag) {
      $this->_tags[] = $tag;
    }
    
    function addTags($tags) {
      if (is_array($tags)) {
        foreach($tags as $tag) {
          $this->_tags[] = $tag;
        }
      }
    }
    
    function generateTagCloud() {
      if (!empty($this->_tags)) {
        
        foreach($this->_tags as $tag) {
          if (!isset($min_count)) {
            $min_count = $tag['count'];
          }elseif ($min_count > $tag['count']) {
            $min_count = $tag['count'];          
          }
          
          if (!isset($max_count)) {
            $max_count = $tag['count'];
          }elseif ($max_count < $tag['count']) {
            $max_count = $tag['count'];
          }
        }
        
        $diff = $max_count - $min_count;
        $diff = ($diff == 0) ? 1 : $diff;
        
        $html = '<div align="center" class="' . $this->_tag_class . '">';
        foreach($this->_tags as $key => $tag) {
          $size = $this->_min_font_size + ($tag['count'] - $min_count) * ($this->_max_font_size - $this->_min_font_size) / $diff;
          $html .=  '<a style="font-size: ' . floor($size) . 'px' . '" href="' . $tag['url'] . '">' . $tag['tag'] . '</a>&nbsp; ';
        }
        $html .= '</div>';
      }
      
      return $html;
    }
  }
?>