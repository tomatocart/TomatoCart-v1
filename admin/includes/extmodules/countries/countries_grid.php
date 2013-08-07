<?php
/*
  $Id: countries_gird.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.countries.CountriesGrid = function(config) { 
  config = config || {};
  
  config.region = 'center';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'countries',
      action: 'list_countries'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'countries_id'
    }, [
      'countries_id',
      'countries_name',
      'countries_iso_code',
      'total_zones'
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
  
  config.cm = new Ext.grid.ColumnModel([
    {id: 'countires', header: '<?php echo $osC_Language->get('table_heading_countries'); ?>', dataIndex: 'countries_name', width: 200},
    {header: '<?php echo $osC_Language->get('table_heading_code'); ?>', dataIndex: 'countries_iso_code', width: 100, align: 'left'},
    {header: '<?php echo $osC_Language->get('table_heading_total_zones'); ?>', dataIndex: 'total_zones', width: 100, align: 'center'},
    config.rowActions
  ]);
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
  config.autoExpandColumn = 'countires';
  
  config.listeners = {
   'rowclick' : this.onGrdRowClick
  };
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
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
  
  config.bbar = new Ext.PagingToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    iconCls: 'icon-grid',
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg
  });
  
  this.addEvents({'selectchange' : true});

  Toc.countries.CountriesGrid.superclass.constructor.call(this, config);
};
  
Ext.extend(Toc.countries.CountriesGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createCountriesDialog();
     
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createCountriesDialog();
    dlg.setTitle(record.get('countries_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('countries_id'));
  },
  
  onDelete: function(record) {
    var coutriesId = record.get('countries_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          btn.disabled = true;
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'countries',
              action: 'delete_country',
              countries_id: coutriesId
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
  
  onGrdRowClick: function(grid, rowIndex, e) {
    var record = grid.getStore().getAt(rowIndex);
    this.fireEvent('selectchange', record);
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
  }
});