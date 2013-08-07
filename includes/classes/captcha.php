<?php
/*
  $Id: captcha.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Captcha {
  protected $_code;
  protected $_width  = 30;
  protected $_height = 150;

  function toC_Captcha() { 
    $this->_code = osc_create_random_string(6); 
  }
  
  function setWidth ($width) {
    $this->_width = $width;
  }
  
  function setHeight($height) {
    $this->_height = $height;
  }

  function getCode(){
    return $this->_code;
  }

  function genCaptcha() {
    $image = imagecreatetruecolor($this->_height, $this->_width);

    $_width = imagesx($image); 
    $_height = imagesy($image);

    $black = imagecolorallocate($image, 0, 0, 0); 
    $white = imagecolorallocate($image, 255, 255, 255); 
    $red = imagecolorallocatealpha($image, 255, 0, 0, 75); 
    $green = imagecolorallocatealpha($image, 0, 255, 0, 75); 
    $blue = imagecolorallocatealpha($image, 0, 0, 255, 75); 
     
    imagefilledrectangle($image, 0, 0, $_width, $_height, $white); 
     
    imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $red); 
    imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $green); 
    imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $blue); 

    imagefilledrectangle($image, 0, 0, $_width, 0, $black); 
    imagefilledrectangle($image, $_width - 1, 0, $_width - 1, $_height - 1, $black); 
    imagefilledrectangle($image, 0, 0, 0, $_height - 1, $black); 
    imagefilledrectangle($image, 0, $_height - 1, $_width, $_height - 1, $black); 
     
    imagestring($image, 10, intval(($_width - (strlen($this->_code) * 9)) / 2),  intval(($_height - 15) / 2), $this->_code, $black);

    header('Content-type: image/jpeg');
    
    imagejpeg($image);
    imagedestroy($image);    
  }
}
?>