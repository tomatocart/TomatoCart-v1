<?php
/*
  $Id: best_orders_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.reports_customers.BestOrdersGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'reports_customers',
      action: 'list_best_orders'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
    }, [
      'orders_id',
      'customers_id',
      'customers_name',
      'value',
      'date_purchased'
    ]),
    autoLoad: true
  }); 
  
  config.cm = new Ext.grid.ColumnModel([
    {id: 'customers_name', header: '<?php echo $osC_Language->get('table_heading_customers'); ?>',dataIndex: 'customers_name'},
    {header: '<?php echo $osC_Language->get('table_heading_date_purchased'); ?>',dataIndex: 'date_purchased', width: 150, align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_total'); ?>',dataIndex: 'value', sortable: true, width: 150, align: 'right', renderer: tocCurrenciesFormatter}
  ]);
  config.autoExpandColumn = 'customers_name';
  
  config.datStartDate = new Ext.form.DateField({
    width: 150,
    emptyText: '<?php echo $osC_Language->get("field_start_date"); ?>',
    format: 'Y-m-d'
  });
  
  config.datEndDate = new Ext.form.DateField({
    width: 150,
    emptyText: '<?php echo $osC_Language->get("field_end_date"); ?>',
    format: 'Y-m-d'
  });
  
  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->',
    config.datStartDate, 
    ' ', 
    config.datEndDate,
    ' ',
    { 
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];

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
  
  Toc.reports_customers.BestOrdersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports_customers.BestOrdersGrid, Ext.grid.GridPanel, {
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onSearch: function() {
    var startDate = this.datStartDate.value || null;
    var endDate = this.datEndDate.value || null;
    var store = this.getStore();

    store.baseParams['start_date'] = startDate;
    store.baseParams['end_date'] = endDate;
    store.reload();
  }
});