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

  echo 'Ext.namespace("Toc.customers_groups");';
  
  include('customers_groups_grid.php');
  include('customers_groups_dialog.php');
?>

Ext.override(TocDesktop.CustomersGroupsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('customers_groups-win');
    
    if (!win) {
      var grd = new Toc.customers_groups.CustomersGroupsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'customers_groups-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-customers_groups-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createCustomersGroupsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('customers_groups-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.customers_groups.CustomersGroupsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }

});
