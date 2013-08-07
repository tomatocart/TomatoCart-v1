<?php
/*
  $Id: credits_memo_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.credits_memo.CreditsMemoGrid = function(config) {
  config = config || {};
  
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'credits_memo',
      action: 'list_credits_memo'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'orders_refunds_id'
    }, [
      'orders_refunds_id',
      'credit_slips_id',
      'orders_id',
      'customers_name',
      'total_products',
      'total_refund',
      'sub_total',
      'date_added',
      'shipping_address',
      'shipping_method',
      'billing_address',
      'payment_method',
      'comments',
      'products',
      'totals'
    ]),
    autoLoad: true
  });  

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
             '<p>{products}</p><p align="right">{totals}</p>',
           '</td>',
         '</tr>',
       '</table>')
  });  
  config.plugins = expander;
  
  config.cm = new Ext.grid.ColumnModel([
    expander,
    {header: '<?php echo $osC_Language->get("table_heading_credit_slip_number"); ?>', dataIndex: 'credit_slips_id', align: 'center', width: 90},
    {header: 'OID', dataIndex: 'orders_id', align: 'center',  width:30},
    {header: '<?php echo $osC_Language->get("table_heading_customer"); ?>', dataIndex: 'customers_name', width: 100},
    {header: '<?php echo $osC_Language->get("table_heading_total_products"); ?>', dataIndex: 'total_products', align: 'center', width: 90},
    {header: '<?php echo $osC_Language->get("table_heading_total"); ?>', dataIndex: 'total_refund', align: 'center', width: 90},
    {header: '<?php echo $osC_Language->get("table_heading_date_added"); ?>', dataIndex: 'date_added', align: 'center', width: 90},
    {id:'credit_slips_comments', header: '<?php echo $osC_Language->get("table_heading_comments"); ?>', dataIndex: 'comments'}
  ]);
  config.autoExpandColumn = 'credit_slips_comments';
    
  config.txtOrderId = new Ext.form.TextField({
    emptyText: '<?php echo $osC_Language->get("operation_heading_order_id"); ?>'
  });
  
  config.txtCustomerId = new Ext.form.TextField({
    emptyText: '<?php echo $osC_Language->get("operation_heading_customer_id"); ?>'
  });
  
  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onSearch,
      scope: this
    },
    '->',
    config.txtOrderId,
    ' ', 
    config.txtCustomerId,
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
        
  Toc.credits_memo.CreditsMemoGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.credits_memo.CreditsMemoGrid, Ext.grid.GridPanel, {
  
  onSearch: function() {
    this.getStore().baseParams['orders_id'] = this.txtOrderId.getValue() || null;
    this.getStore().baseParams['customers_id'] = this.txtCustomerId.getValue() || null;
    
    this.getStore().load();
  }
  
});