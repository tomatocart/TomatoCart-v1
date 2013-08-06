<?php
/*
  $Id: modules_order_total_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.modules_order_total.ModulesOrderTotalGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {forceFit: true};
  
  config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'modules_order_total',
        action: 'list_order_total_modules'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'code'
      },[
        'code',
        'title',
        'sort_order',
        'action'
      ]),
      autoLoad: true
  });  
  config.ds.on('load', function(){this.body.unmask();}, this);

  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    { id: 'modules', header: '<?php echo $osC_Language->get('table_heading_order_total_modules'); ?>', dataIndex: 'title'},
    { id: 'author', header: '<?php echo $osC_Language->get('table_heading_sort_order'); ?>', dataIndex: 'sort_order'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'modules';
  
  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  Toc.modules_order_total.ModulesOrderTotalGrid.superclass.constructor.call(this,config);
};


Ext.extend(Toc.modules_order_total.ModulesOrderTotalGrid, Ext.grid.GridPanel, {
  onEdit: function(record) {
    var dlg  = this.owner.createConfigurationDialog({code: record.get("code")});
    dlg.setTitle(record.get('title'));
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
    
  onAction: function(action, code) {
    this.body.mask(TocLanguage.loadingText);
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'modules_order_total',
        action: action,
        module_code: code
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
          this.getStore().reload();
        }
      },
      scope: this
    });
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      
      case 'icon-install-record':
        this.onAction('install', record.get("code"));
        break;
        
      case 'icon-uninstall-record':
        if (action == 'icon-uninstall-record') {
          Ext.MessageBox.confirm(
            record.get("title"), 
            '<?php echo $osC_Language->get('introduction_uninstall_order_total_module'); ?>',
            function(btn) {
              if (btn == 'yes') {
                this.onAction('uninstall', record.get("code"));
              }
            },
            this
          );
        }
        break;
    }
  } 
});