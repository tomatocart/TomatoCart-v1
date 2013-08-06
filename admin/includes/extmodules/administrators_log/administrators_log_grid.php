<?php
/*
  $Id: administrators_log_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.administrators_log.AdministratorsLogGrid = function(config) {
  config = config || {};

  config.border = false;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'administrators_log',
      action: 'list_administrators_log'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'administrators_log_id'
    },  [
      'administrators_log_id',
      'administrators_id',
      'module',
      'module_id',
      'module_action',
      'user_name',
      'date',
      'logo_info_title'
    ]),
    autoLoad: true
  });  

  config.rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-info-record', qtip: TocLanguage.tipInfo},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'administrators_log_modules', header: '<?php echo $osC_Language->get('table_heading_module'); ?>', dataIndex: 'module'},
    {header: '<?php echo $osC_Language->get('table_heading_id'); ?>', dataIndex: 'module_id', align: 'center', width: 30},
    {header: '<?php echo $osC_Language->get('table_heading_type'); ?>', dataIndex: 'module_action', align: 'center', width: 100}, 
    {header: '<?php echo $osC_Language->get('table_heading_user'); ?>', dataIndex: 'user_name', align: 'center', width: 100},
    {header: '<?php echo $osC_Language->get('table_heading_date'); ?>', dataIndex: 'date', align: 'center', width: 140}, 
    config.rowActions
  ]);
  config.autoExpandColumn = 'administrators_log_modules';

  config.dsModules = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'administrators_log',
      action: 'get_modules'       
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
    	fields: [
    	  'id',
    	  'text'
      ]
    }),
    autoLoad: true
  });
  
  config.cboModules = new Ext.form.ComboBox({ 
    store: config.dsModules,
    triggerAction: 'all',
    emptyText: '<?php echo $osC_Language->get('filter_all'); ?>',
    editable: false,
    valueField: 'id',
    displayField: 'text'
  });

  config.dsUsers = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'administrators_log',
      action: 'get_users'       
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      fields: [
        'id',
        'text'
      ]
    }),
    autoLoad: true
  });

  config.cboUsers = new Ext.form.ComboBox({ 
    store: config.dsUsers,  
    triggerAction: 'all',
    emptyText: '<?php echo $osC_Language->get('filter_all'); ?>',
    editable: false,
    valueField: 'id',
    displayField: 'text'
  }); 

  config.tbar = [
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    }, 
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->',
    {text: '<?php echo $osC_Language->get('operation_title_filter_modules'); ?>'},
    config.cboModules,
    ' ',
    {text: '<?php echo $osC_Language->get('operation_title_filter_users'); ?>'},
    config.cboUsers , 
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

  Toc.administrators_log.AdministratorsLogGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.administrators_log.AdministratorsLogGrid, Ext.grid.GridPanel, {

  onInfo: function (record) {
    var config = {
      administrators_log_id: record.get('administrators_log_id'), 
      logo_info_title: record.get('logo_info_title'),
      date: record.get('date')
    };
    var dlg = this.owner.createAdministratorsLogInfoDialog(config);

    dlg.show();
  },     

  onDelete: function(record) {
    var logId = record.get('administrators_log_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'administrators_log',
              action: 'delete_administrators_log',
              administrators_log_id: logId
            }, 
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'administrators_log',
                action: 'delete_administrators_logs',
                batch: batch
              },
              callback: function(options, success, response){
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
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

  onSearch: function () {
    var store = this.getStore();
    
    store.baseParams['fm'] = this.cboModules.getValue() || null; 
    store.baseParams['fu'] = this.cboUsers.getValue() || null;  
    store.reload();
  },

  onRefresh: function() {
    this.getStore().reload();
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break; 
      case 'icon-info-record':
        this.onInfo(record);
        break; 
    }
  } 
});
