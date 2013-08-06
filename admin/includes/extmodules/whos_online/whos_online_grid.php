<?php
/*
  $Id: whos_online_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>

Toc.whos_online.WhosOnlineGrid = function (config) {

  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'whos_online',
      action: 'list_online_customers'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'session_id'
    }, [
      'session_id',
      'status',
      'geoip',
      'online_time',
      'last_url',
      'custormers_name',
      'customers_info',
      'products',
      'total'
    ]),
    autoLoad: true
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  rowActions.on('action', this.onRowAction, this);
  
  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template(
       '<table width="98%" style="padding-left: 20px">',
         '<tr>',
           '<td width="55%">',
             '<b><?php echo $osC_Language->get('subsection_customer_info'); ?></b>',
             '<p>{customers_info}</p>',
           '</td>',
           '<td>',
             '<b><?php echo $osC_Language->get('subsection_products'); ?></b>',
             '<p>{products}</p>',
           '</td>',
         '</tr>',
       '</table>')
  });  
  config.plugins = [rowActions, expander];
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {dataIndex: 'status', width: 30}, 
    {dataIndex: 'geoip', width: 120}, 
    {header: '<?php echo $osC_Language->get("table_heading_online"); ?>', dataIndex: 'online_time', width: 120}, 
    {id: 'whos_online_custormers', header: '<?php echo $osC_Language->get("table_heading_customers"); ?>', dataIndex: 'custormers_name'}, 
    {header: '<?php echo $osC_Language->get("table_heading_last_page_url"); ?>', dataIndex: 'last_url', width: 140}, 
    {header: '<?php echo $osC_Language->get("table_heading_shopping_cart_total"); ?>', dataIndex: 'total', align:'center', width: 80}, 
    rowActions
  ]);
  config.autoExpandColumn = 'whos_online_custormers';
  
  dsCustomersFilter = new Ext.data.Store({
    reader: new Ext.data.ArrayReader({}, [
       {name: 'id'},
       {name: 'text'}
    ]),
    data: [
      ['', '<?php echo $osC_Language->get("text_none"); ?>'],
      ['customers', '<?php echo $osC_Language->get("text_customers"); ?>'],
      ['guests', '<?php echo $osC_Language->get("text_guests"); ?>'],
      ['customers_guests', '<?php echo $osC_Language->get("text_customers_guests"); ?>'],
      ['bots', '<?php echo $osC_Language->get("text_bots"); ?>']
    ]
  });
  
  config.cboCustomersFilter = new Ext.form.ComboBox({
    store: dsCustomersFilter,
    valueField: 'id',
    displayField: 'text', 
    readOnly: true,
    mode: 'local',
    emptyText: '<?php echo $osC_Language->get("text_none"); ?>',
    triggerAction: 'all',
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onSearch,
      scope: this
    }, 
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    }, 
    '->',
    config.cboCustomersFilter
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
  
  Toc.whos_online.WhosOnlineGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.whos_online.WhosOnlineGrid, Ext.grid.GridPanel, {
  onSearch: function() {
    this.getStore().baseParams['customers_filter'] = this.cboCustomersFilter.getValue();
    this.getStore().reload();
  },
  
  onDelete: function (record) {
    var SessionId = record.get('session_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'whos_online',
              action: 'delete_online_customer',
              session_id: SessionId
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({
                  title: TocLanguage.msgSuccessTitle,
                  html: result.feedback
                });
                
                this.getStore().reload();
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
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {
      batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'whos_online',
                action: 'delete_online_customers',
                batch: batch
              },
              callback: function (options, success, response) {
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({
                    title: TocLanguage.msgSuccessTitle,
                    html: result.feedback
                  });
                  
                  this.getStore().reload();
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
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },

  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  }
}
);