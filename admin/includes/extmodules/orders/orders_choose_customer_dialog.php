<?php
/*
  $Id: orders_choose_customer_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersChooseCustomerDialog = function(config) {
  
  config = config || {};
  
  config.id = 'orders-choose-customer-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title_choose_customer'); ?>';
  config.width = 600;
  config.height = 400;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-orders-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: this.close,
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.orders.OrdersChooseCustomerDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders.OrdersChooseCustomerDialog, Ext.Window, {

  buildForm: function() {
    var rowActions = new Ext.ux.grid.RowActions({
      actions:[
        {iconCls: 'icon-add-record', qtip: TocLanguage.btnAdd}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onRowAction, this);
    
    this.dsCustomers = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders',
        action: 'list_customers'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'customers_id'
      }, [
        'customers_id', 
        'customers_firstname',
        'customers_lastname',
        'customers_email_address',
        'customers_gender',
        'customers_credits'
      ]),
      autoLoad: true
    });
    
    this.grdCustomers = new Ext.grid.GridPanel({
      border: false,
      store: this.dsCustomers,
      cm: new Ext.grid.ColumnModel([
        {dataIndex: 'customers_gender', align: 'center', width: 30, align: 'center'},
        {header: '<?php echo $osC_Language->get("table_heading_first_name"); ?>', dataIndex: 'customers_firstname'},
        {header: '<?php echo $osC_Language->get("table_heading_last_name"); ?>', dataIndex: 'customers_lastname'},
        {id: 'orders_customers', header: '<?php echo $osC_Language->get("table_heading_email"); ?>', dataIndex: 'customers_email_address'},
        {header: '<?php echo $osC_Language->get("table_heading_credit"); ?>', dataIndex: 'customers_credits', align: 'center'},
        rowActions
      ]),
      autoExpandColumn: 'orders_customers',
      plugins: rowActions,
      tbar: [
        '->',
        this.search = new Ext.form.TextField({width: 150}),
        ' ',
        {
          name: 'search',
          handler: this.onSearch,
          iconCls: 'search',
          scope: this
        } 
      ],
      bbar: new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: this.dsCustomers,
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
      })
    });
    
    return this.grdCustomers;
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-add-record':
        this.onSaveOrders(record);
    }
  },

  onRefresh: function() {
    this.dsCustomers.reload();
  },
  
  onSearch: function() {
    this.dsCustomers.baseParams['filter'] = this.search.getValue() || null;
    this.dsCustomers.load();
  },
  
  onSaveOrders: function(record) {
    this.el.mask(TocLanguage.loadingText, 'x-mask-loading');
    
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'create_order',
        customers_id: record.get('customers_id'),
        customers_firstname: record.get('customers_firstname'),
        customers_lastname: record.get('customers_lastname'),
        customers_email_address: record.get('customers_email_address'),
        customers_gender: record.get('customers_gender')
      },
      callback: function (options, success, response) {
        this.el.unmask();
        
        var result = Ext.decode(response.responseText);
        if (result.success == true) {
          this.fireEvent('saveSuccess', result.orders_id, record.get('customers_firstname') + ', ' + record.get('customers_lastname'));
          this.close();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, response.responseText);
        }
      },
      scope: this
    });
  }
});