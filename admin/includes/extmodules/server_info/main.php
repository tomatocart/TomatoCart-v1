<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  echo 'Ext.namespace("Toc.server_info");';
  
  include('server_info_dialog.php');
?>
Ext.override(TocDesktop.ServerInfoWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('server_info-win');
     
    if(!win){
      win = desktop.createWindow({},Toc.server_info.ServerInfoDialog);
    }
    
    win.show();
  }
});
