<?php
/*
  $Id: invoices_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.invoices.InvoicesGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'invoices',
      action: 'list_invoices'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'orders_id'
    }, [
      'orders_id',
      'customers_name',
      {name: 'order_total', type: 'string'},
      'date_purchased',
      'orders_status_name',
      'invoices_number',
      'invoices_date',
      'shipping_address',
      'shipping_method',
      'billing_address',
      'payment_method',
      'products',
      'totals'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-view-record', qtip: TocLanguage.tipView},
      {iconCls: 'icon-invoice-pdf-record', qtip: '<?php echo $osC_Language->get('tip_print_invoice'); ?>'},
      {iconCls: 'icon-packaging-slip-record', qtip: '<?php echo $osC_Language->get('tip_print_packaging_slip'); ?>'},
      {iconCls: 'icon-credit-slip-record', qtip: '<?php echo $osC_Language->get('tip_create_credit_slip'); ?>'},
      {iconCls: 'icon-store-credit-record', qtip: '<?php echo $osC_Language->get('tip_create_store_credit'); ?>'}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
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
             '<p>{products}</p><p>{totals}</p>',
           '</td>',
         '</tr>',
       '</table>')
  });  
  config.plugins = [config.rowActions, expander];
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {header: '<?php echo $osC_Language->get('table_heading_invoices_number'); ?>', dataIndex: 'invoices_number', align: 'center', sortable: true, width: 100},
    {header: 'OID', dataIndex: 'orders_id', width: 30, align: 'center'},
    {id: 'invoices_customers_name', header: '<?php echo $osC_Language->get('table_heading_customers'); ?>', dataIndex: 'customers_name'},
    {header: '<?php echo $osC_Language->get('table_heading_order_total'); ?>', dataIndex: 'order_total', sortable: true, align: 'right', width: 100},
    {header: '<?php echo $osC_Language->get('table_heading_date_purchased'); ?>', dataIndex: 'date_purchased', align: 'center', width: 110, sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_status'); ?>', dataIndex: 'orders_status_name', align: 'center', sortable: true, width: 90},
    {header: '<?php echo $osC_Language->get('table_heading_invoices_date'); ?>', dataIndex: 'invoices_date', align: 'center', width: 110, sortable: true},                         
    config.rowActions
  ]);
  config.autoExpandColumn = 'invoices_customers_name';
  
  config.tbar = [
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
        
  Toc.invoices.InvoicesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.invoices.InvoicesGrid, Ext.grid.GridPanel, {
     
  onView: function(record) {
    var dlg = this.owner.createInvoicesDialog({ordersId: record.get("orders_id")});
    dlg.setTitle(record.get('invoices_number') + ': ' + record.get('customers_name'));
    
    dlg.show();
  },
  
  onInvoice: function(record){
    this.openWin('<?php echo osc_href_link_admin(FILENAME_PDF); ?>' + '?module=invoices&pdf=invoice&orders_id=' + record.get('orders_id') + '&token=' + token, 900, 500);
  },
  
  onPackagingSlip: function(record) {
    this.openWin('<?php echo osc_href_link_admin(FILENAME_PDF); ?>' + '?module=invoices&pdf=packagingslip&orders_id=' + record.get('orders_id') + '&token=' + token, 900, 500);
  },
  
  onCreateCreditSlip: function(record) {
    var dlg = this.owner.createInvoicesCreditSlipsDialog(record);
    dlg.setTitle('<?php echo $osC_Language->get('create_credit_slip_heading_title'); ?>' + ': ' + record.get('invoices_number'));
    
    dlg.show();
  },

  onCreateStoreCredit: function(record) {
    var dlg = this.owner.createInvoicesStoreCreditsDialog(record);
    dlg.setTitle('<?php echo $osC_Language->get('create_store_credit_heading_title'); ?>' + ': ' + record.get('invoices_number'));
    
    dlg.show();
  },

  onSearch: function() {
    var store = this.getStore();
    
    store.baseParams['orders_id'] = this.txtOrderId.getValue() || null;
    store.baseParams['customers_id'] = this.txtCustomerId.getValue() || null;
    store.load();
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-view-record':
        this.onView(record);
        break;
      case 'icon-invoice-pdf-record':
        this.onInvoice(record);
        break;
      case 'icon-packaging-slip-record':
        this.onPackagingSlip(record);
        break;
      case 'icon-credit-slip-record':
        this.onCreateCreditSlip(record);
        break;
      case 'icon-store-credit-record':
        this.onCreateStoreCredit(record);
        break;
    }
  },
  
  openWin: function(u, w, h) {
    var l = (screen.width - w) / 2;
    var t = (screen.height - h) / 2;
    var s = 'width=' + w + ', height=' + h + ', top=' + t + ', left=' + l;
    s += ', toolbar=no, scrollbars=no, menubar=yes, location=no, resizable=no';
    window.open(u, '', s);
  }
});