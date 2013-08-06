<?php
/*
  $Id: orders_choose_shipping_method_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.OrdersChooseShippingMethodDialog = function (config) {
  
  config = config || {};
  
  config.id = 'orders-shipping-method-win';
  config.title = '<?php echo $osC_Language->get('heading_title_choose_shipping_method'); ?>';
  config.layout = 'fit';
  config.width = 400;
  config.height = 300;
  config.modal = true;
  config.iconCls = 'icon-orders-win';
  config.items = this.buildGrid(config.ordersId);
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});  
  
  Toc.orders.OrdersChooseShippingMethodDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.orders.OrdersChooseShippingMethodDialog, Ext.Window, {

  buildGrid: function (ordersId) {
    rowActions = new Ext.ux.grid.RowActions({
      tpl: new Ext.XTemplate(
        '<div class="ux-row-action">'
        +'<tpl for="action">'
        +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
        +'</tpl>'
        +'</div>'
      ),
      actions: [''],
      widthIntercept: Ext.isSafari ? 4: 2
    });
    rowActions.on('action', this.onRowAction, this);
    
    this.grdProuducts = new Ext.grid.GridPanel({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'orders',
          action: 'list_shipping_methods',
          orders_id: ordersId
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'code'
        }, [
          'title',
          'code',
          'price',
          'action'
        ]),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'orders_shipping_methods_title', header: '<?php echo $osC_Language->get('table_heading_shipping_method'); ?>', dataIndex: 'title'},
        {header: '<?php echo $osC_Language->get('table_heading_price'); ?>', dataIndex: 'price', width: 100},
        rowActions
      ]),
      plugins: rowActions,
      autoExpandColumn: 'orders_shipping_methods_title'
    });
    
    return this.grdProuducts;
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-add-record':
        this.onChangeShippingMethod(record);
        break;
    }
  },
  
  onChangeShippingMethod: function(record) {
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'save_shipping_method',
        code: record.get('code'),
        orders_id: this.ordersId
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.fireEvent('saveSuccess', result.feedback);
          this.close();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  }
})
