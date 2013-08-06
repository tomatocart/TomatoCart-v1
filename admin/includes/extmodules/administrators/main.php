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

  echo 'Ext.namespace("Toc.administrators");';
  
  include('administrators_dialog.php');
  include('administrators_grid.php');
?>

Ext.override(TocDesktop.AdministratorsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('administrators-win');
     
    if (!win) {
      grd = new Toc.administrators.AdministratorsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'administrators-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-administrators-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createAdministratorsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('administrators-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.administrators.AdministratorsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
