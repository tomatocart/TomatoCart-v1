<?php
/*
  $Id: orders_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.clicksToEdit = 1;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders',
      action: 'list_orders'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'orders_id'
    }, [
      {name: 'orders_id'},
      {name: 'invoice'},
      {name: 'customers_name'},
      {name: 'order_total', type: 'string'},
      {name: 'date_purchased'},
      {name: 'tracking_no'},
      {name: 'orders_status_name'},
      {name: 'customers_comment'},
      {name: 'admin_comment'},
      {name: 'shipping_address'},
      {name: 'shipping_method'},
      {name: 'billing_address'},
      {name: 'payment_method'},
      {name: 'products'},
      {name: 'totals'},
      {name: 'action'}
    ]),
    autoLoad: true
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions: ['','','','','',''],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  
  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template(
       '<table width="98%" style="padding-left: 20px">',
         '<tr>',
           '<td width="25%">',
             '<b><?php echo $osC_Language->get('subsection_shipping_address'); ?></b>',
             '<p>{shipping_address}</p>',
             '<b><?php echo $osC_Language->get('subsection_delivery_method'); ?></b>',
             '<p>{shipping_method}</p>',
           '</td>',
           '<td width="25%">',
             '<b><?php echo $osC_Language->get('subsection_billing_address'); ?></b>',
             '<p>{billing_address}</p>',
             '<b><?php echo $osC_Language->get('subsection_payment_method'); ?></b>',
             '<p>{payment_method}</p>',
           '</td>',
           '<td>',
             '<b><?php echo $osC_Language->get('subsection_products'); ?></b>',
             '<p>{products}</p>',
             '<p>{totals}</p>',
           '</td>',
         '</tr>',
       '</table>')
  });  
  config.plugins = [config.rowActions, expander];
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {header: '', dataIndex: 'invoice', width: 30, align: 'center'},
    {header: 'ID', dataIndex: 'orders_id', width: 30, align: 'center'},
    {id: 'customers_name', header: '<?php echo $osC_Language->get('table_heading_customers'); ?>', dataIndex: 'customers_name'},
    {header: '<?php echo $osC_Language->get('table_heading_order_total'); ?>', dataIndex: 'order_total', width: 120, align: 'right'},
    {header: '<?php echo $osC_Language->get('table_heading_date_purchased'); ?>', dataIndex: 'date_purchased', align: 'center', width: 120, sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_tracking_no'); ?>', dataIndex: 'tracking_no', align: 'center', width: 120, sortable: true, editor: new Ext.form.TextField()},
    {header: '<?php echo $osC_Language->get('table_heading_status'); ?>', dataIndex: 'orders_status_name', align: 'center', width: 120, sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'customers_name';
  
  dsStatus = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders', 
      action: 'get_status',
      top: '1'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      fields: ['status_id', 'status_name']
    }),
    listeners: {
      load: function() {
        this.cboStatus.setValue('');
      },
      scope: this
    },
    autoLoad: true                                                                                
  });
  
  config.cboStatus = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('operation_heading_filter_status'); ?>', 
    store: dsStatus, 
    valueField: 'status_id', 
    displayField: 'status_name', 
    hiddenName: 'status', 
    readOnly: true,
    width: 120, 
    triggerAction: 'all',
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.listeners = {
    afteredit: this.onAfterEdit,
    scope: this
  };
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '->',
    this.txtOrderId = new Ext.form.TextField({
      width: 120,
      emptyText: '<?php echo $osC_Language->get('operation_heading_order_id'); ?>'
    }),
    ' ',
    this.txtCustomerId = new Ext.form.TextField({
      width: 120,
      emptyText: '<?php echo $osC_Language->get('operation_heading_customer_id'); ?>'
    }),
    ' ',
    config.cboStatus,
    ' ',  
    {
      name: 'search',
      handler: this.onSearch,
      iconCls: 'search',
      scope: this
    } 
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls: 'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
    beforePageText : TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });
        
  Toc.orders.OrdersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.OrdersGrid, Ext.grid.EditorGridPanel, {

  onAdd: function() {
    var dlg = this.owner.createOrdersChooseCustomerDialog();
    
    dlg.on('saveSuccess', function(orders_id, customer_name) {
      this.onRefresh();
      
      var dlgOrderEdit = this.owner.createOrdersEditDialog({ordersId: orders_id});
      dlgOrderEdit.setTitle(orders_id + ': ' + customer_name);
        
      dlgOrderEdit.show();
      
      dlgOrderEdit.on('saveSuccess', function(orders_id, customer_name) {
        this.onRefresh();
      }, this);
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createOrdersEditDialog({ordersId: record.get("orders_id")});
    dlg.setTitle(record.get('orders_id') + ': ' + record.get('customers_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onView: function(record) {
    var dlg = this.owner.createOrdersDialog({ordersId: record.get("orders_id")});
    dlg.setTitle(record.get('orders_id') + ': ' + record.get('customers_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onInvoice: function(record) {
    var ordersId = record.get('orders_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      '<?php echo $osC_Language->get('create_invoice_confirmation');?>',
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'orders',
              action: 'create_invoice',
              orders_id: ordersId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.getStore().reload();
                
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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
  
  onDelete: function(record) {
    var dlg = this.owner.createOrdersDeleteConfirmDialog();
    
    dlg.on('deleteSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show('delete_order', record.get('orders_id'), record.get('orders_id') + ': ' + record.get('customers_name'));
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) { 
      var orders = [];
      Ext.each(this.getSelectionModel().getSelections(), function(record){
        orders.push('#' + record.get('orders_id') + ': ' + record.get('customers_name'));
      });
      
      var dlg = this.owner.createOrdersDeleteConfirmDialog();
      
      dlg.on('deleteSuccess', function() {
        this.onRefresh();
      }, this);
      
      dlg.show('delete_orders', keys.join(','), orders);
    } else {
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onSearch: function() {
    var store = this.getStore();
    
    store.baseParams['orders_id'] = this.txtOrderId.getValue() || null;
    store.baseParams['customers_id'] = this.txtCustomerId.getValue() || null;
    store.baseParams['status'] = this.cboStatus.getValue() || null;
    store.load();
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onPrintOrder: function(record) {
    this.openWin('<?php echo osc_href_link_admin(FILENAME_PDF); ?>' + '?module=orders&pdf=order&orders_id=' + record.get('orders_id') + '&token=' + token, 900, 500);
  },
    
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-invoice-record':
        this.onInvoice(record);
        break;
      case 'icon-order-pdf-record':
        this.onPrintOrder(record);
        break;
      case 'icon-view-record':
        this.onView(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  },
  
  openWin: function(u, w, h) {
    var l = (screen.width - w) / 2;
    var t = (screen.height - h) / 2;
    var s = 'width=' + w + ', height=' + h + ', top=' + t + ', left=' + l;
    s += ', toolbar=no, scrollbars=no, menubar=yes, location=no, resizable=no';
    window.open(u, '', s);
  },
  
  onAfterEdit: function(e) {
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'update_tracking_no',
        tracking_no: e.record.get('tracking_no'),
        orders_id: e.record.get('orders_id')
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          e.record.commit();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);          
        }
      },
      scope: this
    });
  }
});