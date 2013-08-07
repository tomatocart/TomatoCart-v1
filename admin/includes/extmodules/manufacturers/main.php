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

  echo 'Ext.namespace("Toc.manufacturers");';
  
  include('manufacturers_dialog.php');
  include('manufacturers_grid.php');
  include('manufacturers_general_panel.php');
  include('manufacturers_meta_info_panel.php');
?>
Ext.override(TocDesktop.ManufacturersWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('manufacturers-win');
     
    if (!win) {
      grd = new Toc.manufacturers.ManufacturersGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'manufacturers-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-manufacturers-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createManufacturersDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('manufacturers_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.manufacturers.ManufacturersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
