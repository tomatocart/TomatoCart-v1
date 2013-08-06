<?php
/*
  $Id: templates_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.templates.TemplatesGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'templates',
        action: 'list_templates'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'code'
      },[
        'code',
        'title',
        'author',
        'url',
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
    {id: 'title', header: '<?php echo $osC_Language->get('table_heading_templates'); ?>', dataIndex: 'title'},
    {header: '<?php echo $osC_Language->get('table_heading_author'); ?>', dataIndex: 'author', width: 120},
    {header: '<?php echo $osC_Language->get('table_heading_url'); ?>', dataIndex: 'url', width: 180},
    config.rowActions
  ]);
  config.autoExpandColumn = 'title';
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  Toc.templates.TemplatesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.templates.TemplatesGrid, Ext.grid.GridPanel, {
  
  onSetDefault: function(record) {
    this.body.mask(TocLanguage.loadingText);
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'templates',
        action: 'set_default',
        template: record.get('code')
      },
      callback: function(options, success, response){
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.onRefresh();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    }); 
  },

  onAction: function(action, code) {
    this.body.mask(TocLanguage.loadingText);
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'templates',
        action: action,
        module_code: code
      },
      callback: function(options, success, response) {
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
  },
  
  onAdd: function() {
    var dlg = this.owner.createTemplatesUploadDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
    
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-default-gray-record':
        this.onSetDefault(record);
        break;
      
      case 'icon-install-record':
        this.onAction('install', record.get("code"));
        break;
        
      case 'icon-uninstall-record':
        if (action == 'icon-uninstall-record') {
          Ext.MessageBox.confirm(
            record.get("title"), 
            '<?php echo $osC_Language->get('introduction_uninstall_template'); ?>',
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