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

  $osC_Language->loadIniFile('modules/image/check.php');
  $osC_Language->loadIniFile('modules/image/resize.php');

  echo 'Ext.namespace("Toc.images");';
  
  include('images_check_dialog.php');
  include('images_resize_dialog.php');
  include('images_grid.php');
?>

Ext.override(TocDesktop.ImagesWindow, {
 
  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('images-win');
     
    if(!win){
      grd = new Toc.images.ImagesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'images-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-images-win',
        layout: 'fit',
        items: grd
      });
    }
    win.show();
  },
    
  createImagesCheckDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('images-check-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.images.ImagesCheckDialog);
    }
    
    return dlg;
  },
  
   createImagesResizeDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('images-resize-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.images.ImagesResizeDialog);
    }
    
    return dlg;
  }
});
