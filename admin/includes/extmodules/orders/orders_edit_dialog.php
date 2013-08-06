<?php
/*
  $Id: orders_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersEditDialog = function(config) {
  
  config = config || {};
  
  config.id = 'orders-edit-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 850;
  config.height = 600;
  config.layout = 'fit';
  config.autoScroll = true;
  config.modal = true;
  config.iconCls = 'icon-orders-win';
  config.items = this.buildForm(config.ordersId, config.outStockProduct);
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: this.close,
      scope: this
    }
  ];
    
  Toc.orders.OrdersEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders.OrdersEditDialog, Ext.Window, {
  buildForm: function(ordersId, outStockProduct) {
    pnlOrdersStatus = new Toc.orders.OrdersStatusPanel({ordersId: ordersId, owner: this}); 
    this.frmOrderEdit = new Toc.orders.OrdersEditPanel({ordersId: ordersId, outStockProduct: outStockProduct, owner: this});
    
    this.tabOrders = new Ext.TabPanel({
      activeTab: 0,
      defaults:{autoScroll: true},
      items: [this.frmOrderEdit, pnlOrdersStatus]
    });
    
    return this.tabOrders;    
  }

});