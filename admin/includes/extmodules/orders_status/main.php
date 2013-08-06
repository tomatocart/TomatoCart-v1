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

  echo 'Ext.namespace("Toc.orders_status");';
  
  include('orders_status_dialog.php');
  include('orders_status_grid.php');
?>

Ext.override(TocDesktop.OrdersStatusWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('orders_status-win');
     
    if(!win){
      grd = new Toc.orders_status.OrdersStatusGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'orders_status-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-orders_status-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createOrdersStatusDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders_status-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.orders_status.OrdersStatusDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
