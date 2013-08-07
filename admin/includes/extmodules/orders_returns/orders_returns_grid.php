<?php
/*
  $Id: orders_returns_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.orders_returns.OrdersReturnsGrid = function (config) {
  config = config || {};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: { 
      module: 'orders_returns',
      action: 'list_orders_returns'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'orders_returns_id'
    }, [
      'orders_returns_id', 
      'orders_id',
      'orders_returns_customer',
      'quantity',
      'date_added',
      'status',
      'status_id',
      'products',
      'return_quantity',
      'billing_address',
      'customers_comments',
      'admin_comments',
      'total',
      'action'
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
    actions:['','',''],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  
  config.expander = new Ext.grid.RowExpander({
    tpl: new Ext.Template(
      '<table width="98%" style="padding-left: 20px">',
       '<tr>',
         '<td width="25%">',
           '<b><?php echo $osC_Language->get('section_customers_heading'); ?></b>',
           '<p>{billing_address}</p>',
         '</td>',
         '<td width="35%">',
           '<b><?php echo $osC_Language->get('section_products_heading'); ?></b>',
           '<p>{products}</p>',
         '</td>',
         '<td>',
           '<b><?php echo $osC_Language->get('section_comments_heading'); ?></b>',
           '<p>{customers_comments}</p>',
         '</td>',
       '</tr>',
      '</table>'
    ),
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  config.plugins = [config.rowActions, config.expander];
  
  config.cm = new Ext.grid.ColumnModel([
    config.expander,
    {header: '<?php echo $osC_Language->get("table_heading_return_id"); ?>', dataIndex: 'orders_returns_id', align: 'center', width: 70},
    {header: 'OID',dataIndex: 'orders_id', align: 'center', width: 30},
    {header: '<?php echo $osC_Language->get("table_heading_orders_returns_customers"); ?>',dataIndex: 'orders_returns_customer', width: 100},
    {header: '<?php echo $osC_Language->get("table_heading_returned_qty"); ?>',dataIndex: 'quantity', align: 'center', width: 40},
    {header: '<?php echo $osC_Language->get("table_heading_date_added"); ?>',dataIndex: 'date_added', align: 'center', width: 100}, 
    {header: '<?php echo $osC_Language->get("table_heading_status"); ?>',dataIndex: 'status', align: 'center', width: 80},
    {id: 'orders-returns-admin-comments', header: '<?php echo $osC_Language->get("table_heading_admin_comments"); ?>',dataIndex: 'admin_comments'},    
    config.rowActions
  ]);
  config.autoExpandColumn = 'orders-returns-admin-comments';
  
  dsStatus = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders_returns',
      action: 'list_return_status',
      top: 'true'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      fields: ['status_id', 'status_name']
    }),
    autoLoad: true
  });
  
  config.cboOrdersReturnsStatus = new Ext.form.ComboBox({
    emptyText: '<?php echo $osC_Language->get("all_status"); ?>',
    store: dsStatus,
    valueField: 'status_id',
    displayField: 'status_name',
    readOnly: true,
    editable: false,
    triggerAction: 'all',
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.txtOrderId = new Ext.form.TextField({
    emptyText: '<?php echo $osC_Language->get("operation_heading_order_id"); ?>'
  });
  
  config.txtCustomerId = new Ext.form.TextField({
    emptyText: '<?php echo $osC_Language->get("operation_heading_customer_id"); ?>'
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onSearch,
      scope: this
    }, 
    '->', 
    config.txtOrderId,
    ' ', 
    config.txtCustomerId,
    ' ',
    config.cboOrdersReturnsStatus, 
    ' ', 
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    beforePageText: TocLanguage.beforePageText,
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
   
  Toc.orders_returns.OrdersReturnsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders_returns.OrdersReturnsGrid, Ext.grid.GridPanel, {
	
  onEdit: function (record) {
    var dlg = this.owner.createOrdersReturnsEditDialog();
    dlg.setTitle(record.get("orders_returns_id") + " : " + record.get("orders_returns_customer"));

    dlg.on('saveSuccess', function() {
    	this.onRefresh();
    }, this);
    
    dlg.show(record);
  },
  
  onCreditSlip: function (record) {
    var dlg = this.owner.createOrdersReturnsCreditSlipDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record);
  },
  
  onStoreCredit: function (record) {
    var dlg = this.owner.createOrdersReturnsStoreCreditDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record);
  },
  
  onSearch: function () {
    this.getStore().baseParams['orders_id'] = this.txtOrderId.getValue() || null;
    this.getStore().baseParams['customers_id'] = this.txtCustomerId.getValue() || null;
    this.getStore().baseParams['orders_returns_status_id'] = this.cboOrdersReturnsStatus.getValue() || null;
    
    this.getStore().reload();
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-credit-slip-record':
        this.onCreditSlip(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-store-credit-record':
        this.onStoreCredit(record);
        break;
    }
  }
});