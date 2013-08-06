<?php
/*
  $Id: zone_entries_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.zone_groups.ZoneEntriesGrid = function (config) {

  config = config || {};
  
  this.geoZoneId = null;
  this.geoZoneName = null;
  
  config.title = '<?php echo $osC_Language->get('action_heading_new_zone_entry'); ?>';
  config.region = 'east';
  config.split = true;
  config.minWidth = 280;
  config.maxWidth = 320;
  config.width = 300;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords,
    forceFit: true
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'zone_groups',
      action: 'list_zone_entries'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'geo_zone_entry_id'
    }, [
      'geo_zone_entry_id',
      'countries_id',
      'countries_name',
      'zone_name',
      'zone_id'
    ]),
    autoLoad: true
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
  config.cm =new Ext.grid.ColumnModel([
    config.sm, 
    {header: '<?php echo $osC_Language->get("table_heading_country");?>', dataIndex: 'countries_name'}, 
    {header: '<?php echo $osC_Language->get("table_heading_zone");?>', dataIndex: 'zone_name'}, 
    config.rowActions
  ]);
  
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
      handler: this.onRefresh,
      scope: this
    }
  ];
    
  Toc.zone_groups.ZoneEntriesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.zone_groups.ZoneEntriesGrid, Ext.grid.GridPanel, {

  iniGrid: function (record) {
    this.geoZoneId = record.get('geo_zone_id');
    this.geoZoneName = record.get('geo_zone_name');
    var store = this.getStore();
    
    store.baseParams['geo_zone_id'] = this.geoZoneId;
    store.load();
  },
  
  onAdd: function () {
    if (this.geoZoneId > 0) {
      var dlg = this.owner.createZoneEntriesDialog();

      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);

      dlg.show(this.geoZoneId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function (record) {
    var geoZoneEntryId = record.get('geo_zone_entry_id');
    var dlg = this.owner.createZoneEntriesDialog();
    dlg.setTitle(this.geoZoneName);
        
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(this.geoZoneId, geoZoneEntryId);
  },
  
  onDelete: function (record) {
    var geoZoneEntryId = record.get('geo_zone_entry_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'zone_groups',
              action: 'delete_zone_entry',
              geo_zone_entry_id: geoZoneEntryId
            },
            callback: function (options, success, response) {
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
  },
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
               url: Toc.CONF.CONN_URL,
              params: {
                module: 'zone_groups',
                action: 'delete_zone_entries',
                geo_zone_id: this.geoZoneId,
                batch: batch
              },
              callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, response.responseText);
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
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  },
  
  reset: function() {
    this.setTitle('<?php echo $osC_Language->get('section_zone_entries'); ?>');
    this.geoZoneId = null;
    this.geoZoneName = null;
    this.getStore().removeAll();
  }
});