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

  echo 'Ext.namespace("Toc.orders");';
  
  include('refunds_grid.php');
  include('returns_grid.php');
  include('orders_grid.php');
  include('orders_products_grid.php');
  include('orders_edit_products_grid.php');
  include('orders_transaction_grid.php');
  include('orders_status_panel.php');
  include('orders_delete_confirm_dialog.php');
  include('orders_choose_customer_dialog.php');
  include('orders_choose_product_dialog.php');
  include('orders_choose_shipping_method_dialog.php');
  include('orders_dialog.php');
  include('orders_edit_panel.php');
  include('orders_edit_dialog.php');
?>

Ext.override(TocDesktop.OrdersWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('orders-win');
     
    if (!win) {
      grd = new Toc.orders.OrdersGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'orders-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 850,
        height: 400,
        iconCls: 'icon-orders-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },

  createOrdersDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-dialog-win');
    
    if (!dlg) {
    	config.owner = this;
      dlg = desktop.createWindow(config, Toc.orders.OrdersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createOrdersChooseCustomerDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-choose-customer-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.orders.OrdersChooseCustomerDialog);
    }
    
    return dlg;
  },
  
  createNewOrderDialog: function() {
    var dlg = this.createOrdersChooseCustomerDialog();
     
    dlg.on('saveSuccess', function(orders_id, customer_name) {
      var dlgOrderEdit = this.createOrdersEditDialog({ordersId: orders_id});
      dlgOrderEdit.setTitle(orders_id + ': ' + customer_name);
        
      dlgOrderEdit.show();
    }, this);
    
    return dlg;
  },

  createOrdersEditDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-edit-dialog-win');
    
    if (!dlg) {
      config.owner = this;
      dlg = desktop.createWindow(config, Toc.orders.OrdersEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createOrdersChooseProductDialog: function(ordersId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-choose-product-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({ordersId: ordersId}, Toc.orders.OrdersChooseProductDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createOrdersChooseShippingMethodDialog: function (ordersId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-shipping-method-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({ordersId: ordersId}, Toc.orders.OrdersChooseShippingMethodDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createOrdersDeleteConfirmDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-delete-confirm-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.orders.OrdersDeleteComfirmDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});