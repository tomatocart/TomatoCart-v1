<?php
/*
  $Id: modules_geoip_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.modules_geoip.ModulesGeoIPGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {forceFit: true};
  
  config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'modules_geoip',
        action: 'list_geoip_modules'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'code'
      },[
        'code',
        'title',
        'description',
        'author',
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
    actions:[''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    { id: 'title', header: '<?php echo $osC_Language->get('table_heading_geoip_modules'); ?>', dataIndex: 'title'},
    { header: '<?php echo $osC_Language->get('table_heading_author'); ?>', dataIndex: 'author'},
    { header: '<?php echo $osC_Language->get('table_heading_description'); ?>', dataIndex: 'description'},
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
  
  Toc.modules_geoip.ModulesGeoIPGrid.superclass.constructor.call(this, config);
};


Ext.extend(Toc.modules_geoip.ModulesGeoIPGrid, Ext.grid.GridPanel, {
  onAction: function(action, code) {
    this.body.mask(TocLanguage.loadingText);
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'modules_geoip',
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
            '<?php echo $osC_Language->get('introduction_uninstall_geoip_module'); ?>',
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