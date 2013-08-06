<?php
/*
  $Id: countries_zones_gird.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.countries.ZonesGrid = function(config){

  config = config || {};
  
  this.countriesId = null;
  this.countriesName = null;
  
  config.title = '<?php echo $osC_Language->get('section_zones'); ?>';
  config.region = 'east';
  config.split = true;
  config.minWidth = 240;
  config.maxWidth = 320;
  config.width = 280;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'countries',
      action: 'list_zones'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'zone_id'
    }, [
      'zone_id',
      'zone_country_id',
      'zone_code',
      'zone_name'
    ])
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
     {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
     {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'zones', header: '<?php echo $osC_Language->get('table_heading_zones'); ?>', dataIndex: 'zone_name', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_zone_code'); ?>', dataIndex: 'zone_code', width: 60, sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'zones';
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
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
      handler:this.onRefresh,
      scope:this
    }
  ];

  Toc.countries.ZonesGrid.superclass.constructor.call(this, config);
};

  
Ext.extend(Toc.countries.ZonesGrid, Ext.grid.GridPanel,{

  iniGrid: function(record) {
    this.countriesId = record.get('countries_id');
    this.countriesName = record.get('countries_name');
    var store = this.getStore();
    
    store.baseParams['countries_id'] = this.countriesId;
    store.load();
  },
  
  onAdd: function() {
    if (this.countriesId) {
      var dlg = this.owner.createZonesDialog();
      
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
      
      dlg.show(this.countriesId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
    
  onEdit: function(record) {
    var zoneId = record.get('zone_id');
    var dlg = this.owner.createZonesDialog();
    dlg.setTitle(this.countriesName);
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(this.countriesId, zoneId);
  },
  
  onDelete: function(record) {
    var zoneId = record.get('zone_id');
  
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'countries',
              action: 'delete_zone',
              zone_id: zoneId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
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
      }, this);
  },
    
  onBatchDelete: function() {
    var keys = this.selModel.selections.keys;
    
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
                module: 'countries',
                action: 'delete_zones',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
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
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
    
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  },
  
  reset: function() {
    this.setTitle('<?php echo $osC_Language->get('section_zones'); ?>');
    this.countriesId = null;
    this.countriesName = null;
    this.getStore().removeAll();
  }
});
