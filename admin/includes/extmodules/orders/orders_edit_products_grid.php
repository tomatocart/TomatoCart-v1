<?php
/*
  $Id: orders_products_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.OrdersEditProductsGrid = function(config) {

  config = config || {};
  
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.autoHeight = true;
  config.width = 792;
  config.border = true;
  config.clicksToEdit = 1;
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders',
      action: 'list_orders_edit_products',
      orders_id: config.ordersId    
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT
    },[
      'orders_products_id',
      'products_id',
      'products_type',
      'orders_id',
      'products',
      'quantity',
      'qty_in_stock',
      'sku',
      'tax',
      'price_net',
      'price_gross',
      'total_net',
      'total_gross',
      'shipping_method',
      'action'
    ]),
    autoLoad: false
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
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
  config.rowActions.on('action', this.onRowAction, this);   
  config.plugins = config.rowActions;
  
  //Here we can not use the system default currency,
  //the order's currency has to be used instead to format the price.
  //
  var formatPrice = function(price) {
    var code = config.cboCurrencies.getValue();
    var store = config.cboCurrencies.getStore();
    var value = price;
    
    store.each(function(record){
      id = record.get('id');

      if (id == code) {
        var symbol_left = record.get('symbol_left');
        var symbol_right = record.get('symbol_right');
        var decimal_places = record.get('decimal_places');
        var decimal_sep = ".";
        var thousand_sep = ",";
    
        var m = /(\d+)(?:(\.\d+)|)/.exec(price + ""),
            x = m[1].length > 3 ? m[1].length % 3 : 0;
    
        value = symbol_left + " "
               + (price < 0? '-' : '') // preserve minus sign
               + (x ? m[1].substr(0, x) + thousand_sep : "")
               + m[1].substr(x).replace(/(\d{3})(?=\d)/g, "$1" + thousand_sep)
               + (decimal_places? decimal_sep + (+m[2] || 0).toFixed(decimal_places).substr(2) : "")
               + ((symbol_right != null) ? (" " + symbol_right) : '');
      }
    });
    
    return value;
  };
  
  var outStockProducts = config.outStockProduct;
  var i = 0;
   
  config.cm = new Ext.grid.ColumnModel([
    {
      id: 'orders_edit_products', 
      header: '<?php echo $osC_Language->get('table_heading_products');?>', 
      dataIndex: 'products', 
      renderer: function(val) {
        if(outStockProducts != null && outStockProducts.length > 0) {
          var products_id = config.ds.getAt(i++).data['products_id'];
          for(var j = 0; j < outStockProducts.length; j++) {
		        if(outStockProducts[j] == products_id) { 
		          return val + '<br/><span style="color:red;"><?php echo $osC_Language->get('table_heading_out_products_stock');?></span>';
		        }       
          }
        }
        
        return val;
      }
    },
    {header: '<?php echo $osC_Language->get('table_heading_product_sku');?>', dataIndex: 'sku', width: 80, align: 'right', editor: new Ext.form.TextField()},
    {header: '<?php echo $osC_Language->get('table_heading_product_qty');?>', dataIndex: 'quantity', width: 60, align: 'center', editor: new Ext.form.NumberField({allowNegative: false, allowDecimals: false})},
    {header: '<?php echo $osC_Language->get('table_heading_tax');?>', dataIndex: 'tax', width: 50, align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_price_net');?>', dataIndex: 'price_net', width: 80, align: 'right', editor: new Ext.form.NumberField(), renderer: formatPrice},
    {header: '<?php echo $osC_Language->get('table_heading_price_gross');?>', dataIndex: 'price_gross', width: 80, align: 'right'},
    {header: '<?php echo $osC_Language->get('table_heading_total_gross');?>', dataIndex: 'total_gross', width: 80, align: 'right'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'orders_edit_products';
  
  config.listeners = {
    beforeedit: this.onBeforeEdit,
    afteredit: this.onAfterEdit,
    scope: this
  };
  
  config.buttons = [{text: '<?php echo $osC_Language->get('button_add_product');?>', iconCls:'add', handler: this.onAddProduct, scope: this}];
  
  Toc.orders.OrdersEditProductsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.OrdersEditProductsGrid, Ext.grid.EditorGridPanel, {
  onBeforeEdit: function(e) {
    if ((e.record.get('products_type') == '<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>') && (e.column == 2)) {
      alert('<?php echo $osC_Language->get('error_gift_certificate_quantity_not_allowed');?>');
      return false;
    }
          
    return true;
  },
  
  verifyQuantity: function (new_qty, old_qty, qty_in_stock) {
    var new_qty = parseInt(new_qty);
    var old_qty = parseInt(old_qty);
    var qty_in_stock = parseInt(qty_in_stock);
    
  <?php if (STOCK_ALLOW_CHECKOUT == '-1') { ?>
    
    if ((new_qty - old_qty) > qty_in_stock) {
      alert('<?php echo $osC_Language->get('error_max_stock_value_reached');?>');
    
      return false;
    } else {
      return true;
    }
    
  <?php } else if (STOCK_CHECK == '1') { ?>
    
    if ((new_qty - old_qty) > qty_in_stock) {
      return confirm('<?php echo $osC_Language->get('warning_max_stock_value_reached');?>');
    } else {
      return true;
    }
    
  <?php } ?>
    
    return true;
  },
  
  onAfterEdit: function(e) {
    var params = {
      module: 'orders',
      product_id: e.record.get('products_id'),
      orders_products_id: e.record.get('orders_products_id'),
      orders_id: this.ordersId};
  
//    var action = null;
    var verified = true;
    
    if (e.column == 1) {
      params.action = 'update_sku';
      params.products_sku = e.value;
    } else if (e.column == 2) {
      params.action = 'update_quantity';
      params.quantity = e.value;
      
      verified = this.verifyQuantity(e.value, e.originalValue, e.record.get('qty_in_stock'));
      
      if (verified == false) {
        e.record.set('quantity', e.originalValue);
        e.record.commit();
      }
    } else if (e.column == 4) {
      params.action = 'update_price';
      params.price = e.value;
    }
    
    if ((params.action != null) && (verified == true)) {
      Ext.Ajax.request({
        waitMsg: TocLanguage.formSubmitWaitMsg,
        url: Toc.CONF.CONN_URL,
        params: params,
        callback: function (options, success, response) {
          var result = Ext.decode(response.responseText);
          this.getStore().load();
          
          if (result.success == false) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
          }
        },
        scope: this
      });
    } 
  },
  
  onAddProduct: function() {
    if (this.owner.owner.owner == undefined) {
      dlg = this.owner.owner.createOrdersChooseProductDialog(this.ordersId);
    } else { 
      dlg = this.owner.owner.owner.createOrdersChooseProductDialog(this.ordersId);
    }
    
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);
      
    dlg.show();
  },
  
  getTbar: function(owner) {
    if ( typeof(owner)!= "undefined" )  {
      var tbar = new Ext.Toolbar({
        items: [
          {
            text: '<?php echo $osC_Language->get('button_add_product');?>',
            iconCls:'add',
            handler: this.onAddProduct,
            scope: this
          },
          {
            text: '<?php echo $osC_Language->get('button_change_delivery_method');?>',
            iconCls:'add',
            handler: this.onEditShippingMethod,
            scope: this
          }
        ]
      });
      
      return tbar;
    }
  },
  
  onDelete: function (record) {
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'orders',
              action: 'delete_product',
              orders_products_id: record.get('orders_products_id'),
              orders_id: this.ordersId,
              products_id: record.get('products_id')
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.getStore().load();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      }, 
      this
    );
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  }
});