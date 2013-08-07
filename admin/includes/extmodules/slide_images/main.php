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

  echo 'Ext.namespace("Toc.slideImages");';
  
  include('slide_images_dialog.php');
  include('slide_images_grid.php');
?>

Ext.override(TocDesktop.SlideImagesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('slide_images-win');
     
    if (!win) {

      grid = new Toc.slideImages.SlideImagesGrid({owner: this});

      win = desktop.createWindow({
        id: 'slide_images-win',
        title: '<?php echo $osC_Language->get('heading_slide_images_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-slide_images-win',
        layout: 'fit',
        items: grid
      });
    }
           
    win.show();
  },
  
  createSlideImagesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('slide_images_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.slideImages.SlideImagesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  }
});
