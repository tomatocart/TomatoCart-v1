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

  echo 'Ext.namespace("Toc.orders_returns");';
  
  include('orders_returns_edit_dialog.php');
  include('orders_returns_store_credit_dialog.php');
  include('orders_returns_credit_slip_dialog.php');
  include('orders_returns_grid.php');
?>

Ext.override(TocDesktop.OrdersReturnsWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('orders_returns-win');
     
    if (!win) {
      this.grd = new Toc.orders_returns.OrdersReturnsGrid({owner: this});

      win = desktop.createWindow({
        id: 'orders_returns-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 850,
        height: 400,
        iconCls: 'icon-orders_returns-win',
        layout: 'fit',
        items: this.grd
      });
    }
    
    win.show();
  },

  createOrdersReturnsEditDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders_returns_edit-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.orders_returns.OrdersReturnsEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createOrdersReturnsCreditSlipDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders_returns_credit_slip-dialog-win');
    
    if(!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.orders_returns.OrdersReturnsCreditSlipDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createOrdersReturnsStoreCreditDialog: function() {
    var desktop =  this.app.getDesktop();
    var dlg = desktop.getWindow('orders_returns_store_credit-dialog-win');
    
    if(!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.orders_returns.OrdersReturnsStoreCreditDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});