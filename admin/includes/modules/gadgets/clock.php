<?php
/*
  $Id: clock.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Gadget_Clock extends toC_Gadget {

  var $_title,
      $_code = 'clock',
      $_type = 'flash',
      $_icon = 'clock.png',
      $_description;
  
  function toC_Gadget_Clock() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('gadget_clock_title');
    $this->_description = $osC_Language->get('gadget_clock_description');
  }
  
  function renderView() {
    global $toC_Json;
    
    $view = '
	    swfobject.embedSWF(
        "external/devAnalogClock/media/devAnalogClock.swf",
        "tool-gadget-clock",
        "160",
        "170",
        "8",
        "external/devAnalogClock/media/expressInstall.swf",
        {clockSkin: "external/devAnalogClock/media/skins/tomatocart_clock.png"},
        {scale: "noscale", wmode: "transparent"}
	    );';
    
    $response = array('success' => true, 'view' => $view);
    
    echo $toC_Json->encode($response);
  }
}  
?>