<?php
/*
  $Id: products_attributes_entries_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products_attributes.AttributeEntriesGrid = function(config) {

  config = config || {};
  
  this.attributesGroupsId = null;
  
  config.title = '<?php echo $osC_Language->get('heading_title_attribute_entries'); ?>';
  config.region = 'east';
  config.border = false;
  config.split = true;
  config.minWidth = 460;
  config.maxWidth = 500;
  config.width = 460;  
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products_attributes',
      action: 'list_products_attributes_entries'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_attributes_values_id'
    }, [
      'products_attributes_values_id',
      'products_attributes_groups_id',
      'name',
      'module',
      'status',
      'sort_order'
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
     
  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };  
     
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'products_attributes_values_name', header:'<?php echo $osC_Language->get('table_heading_fields_name'); ?>',dataIndex: 'name'},
    {header: '<?php echo $osC_Language->get('table_heading_fields_module'); ?>',dataIndex: 'module', align:'center', width:100},
    {header: '<?php echo $osC_Language->get('table_heading_fields_status'); ?>',dataIndex: 'status', align:'center', renderer: renderPublish, width:80},
    {header: '<?php echo $osC_Language->get('table_heading_fields_sort_order'); ?>',dataIndex: 'sort_order', align:'center', width:80},
    config.rowActions     
  ]);
  config.autoExpandColumn = 'products_attributes_values_name';
    
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

  Toc.products_attributes.AttributeEntriesGrid.superclass.constructor.call(this, config);

};

Ext.extend(Toc.products_attributes.AttributeEntriesGrid, Ext.grid.GridPanel, {

  iniGrid: function(record) {
    this.attributesGroupsId = record.get('products_attributes_groups_id');
    var store = this.getStore();
      
    store.baseParams['products_attributes_groups_id'] = this.attributesGroupsId;
    store.load();
  },
  
  onAdd: function() {
    if (this.attributesGroupsId) {
      var dlg = this.owner.createAttributeEntriesDialog();
     
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
      
      dlg.show(this.attributesGroupsId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createAttributeEntriesDialog();
    var productsAttributesValuesId = record.get('products_attributes_values_id');
    dlg.setTitle(record.get('name'));

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(this.attributesGroupsId, productsAttributesValuesId);                         
  },

  onDelete: function(record) {
    var productsAttributesValuesId = record.get('products_attributes_values_id');
    
    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if(btn == 'yes') {  
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'products_attributes',
              action: 'delete_products_attributes_entry',
              products_attributes_groups_id: this.attributesGroupsId,
              products_attributes_values_id: productsAttributesValuesId
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
    
    if(keys.length > 0) {    
      var batch = keys.join(',');
      
      Ext.Msg.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if(btn == 'yes') {      
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: { 
                module: 'products_attributes',
                action: 'delete_products_attributes_entries',
                products_attributes_groups_id: this.attributesGroupsId,
                batch: batch                                        
              },
              callback: function(options, success, response){
                var result = Ext.decode(response.responseText);
              
                if(result.success == true) {
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
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
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
    this.setTitle('<?php echo $osC_Language->get('heading_title_attribute_entries'); ?>');
    this.attributesGroupsId = null;
    this.getStore().removeAll();
  },
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;
  
    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }

      if (action != 'img-button') {
        var valuesId = this.getStore().getAt(row).get('products_attributes_values_id');
        var module = 'setEntryStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, valuesId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, valuesId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'products_attributes',
        action: action,
        products_attributes_values_id: valuesId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(valuesId).set('status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }
});