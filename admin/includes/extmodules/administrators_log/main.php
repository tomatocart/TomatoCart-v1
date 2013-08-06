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

  echo 'Ext.namespace("Toc.administrators_log");';
  
  include('administrators_log_info_dialog.php');
  include('administrators_log_grid.php');
?>

Ext.override(TocDesktop.AdministratorsLogWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('administrators_log-win');
     
    if (!win) {
      grd = new Toc.administrators_log.AdministratorsLogGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'administrators_log-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-administrators_log-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createAdministratorsLogInfoDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('administrators_log_info-dialog');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.administrators_log.AdministratorsLogInfoDialog);
      
      return dlg;
    }
  }
});
