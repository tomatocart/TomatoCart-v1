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
  echo 'Ext.namespace("Toc.watermark");';
  include('watermark_dialog.php');
?>

Ext.override(TocDesktop.WatermarkWindow, {
  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('watermark-win');
    
    if (!win) {
      win = desktop.createWindow({owner:this}, Toc.watermark.WatermarkDialog);
      
      win.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    win.show();
  }
});
