<?php
/*
  $Id: banner_manager_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.banner_manager.BannerManagerGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'banner_manager',
      action: 'list_banner'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'banners_id'
    }, 
    [
      'banners_id',
      'banners_title',
      'banners_group',
      'statistics',
      'status'
  	]),
    autoLoad: true
  });  
  
  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.rowActions = new Ext.ux.grid.RowActions({
  	header: '<?php echo $osC_Language->get('table_heading_action'); ?>',
  	actions:[
  		{iconCls: 'icon-banner-preview-record', qtip: TocLanguage.tipPreview},
    	{iconCls: 'icon-statistics-record', qtip: TocLanguage.tipStatistics},
    	{iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
    	{iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
 	  ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'banners_title', header: '<?php echo $osC_Language->get('table_heading_banners'); ?>', dataIndex: 'banners_title'},
    {header: '<?php echo $osC_Language->get('table_heading_group'); ?>', dataIndex: 'banners_group', align: 'center', width: 100},
    {header: '<?php echo $osC_Language->get('table_heading_statistics'); ?>', dataIndex: 'statistics', align: 'center', width: 100},
    {header: '<?php echo $osC_Language->get('table_heading_status'); ?>', dataIndex: 'status', renderer: renderPublish, align: 'center', width: 100},
    config.rowActions
  ]);
  config.autoExpandColumn = 'banners_title';
  
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

  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
         handler: function() {
          thisObj.onBatchDelete();
         }
      }
    ],
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
        
  Toc.banner_manager.BannerManagerGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.banner_manager.BannerManagerGrid, Ext.grid.GridPanel, {
  
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
        var bannersId = this.getStore().getAt(row).get('banners_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, bannersId, flag);
            break;
        }
      }
    }
  },
  
  onAction: function(action, bannersId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'banner_manager',
        action: action,
        banners_id: bannersId,
        flag: flag
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(bannersId).set('status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  },
  
  onAdd: function() {
    var dlg = this.owner.createBannerManagerDialog();
     
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);
    
    dlg.show();
  },
  
  onPreview: function(record) {
    var dlg = this.owner.createBannerManagerPreviewDialog();
    
    dlg.show(record.get("banners_id"));
  },
  
  onStatistics: function(record) {
    var dlg = this.owner.createBannerManagerStatisticsDialog(record.get("banners_id"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createBannerManagerDialog('<?php echo $osC_Language->get('banner_manager_edit_dialog'); ?>');
    
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);
    
    dlg.show(record.get("banners_id"));
  },
  
  onDelete: function(record) {
    var bannerId = record.get('banners_id');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
      
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'banner_manager',
              action: 'delete_banner',
              banners_id: bannerId
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
                module: 'banner_manager',
                action: 'delete_banners',
                batch: batch
              },
              callback: function(options, success, response){
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, 
        this
      );
    }else{
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-banner-preview-record':
      	this.onPreview(record);
     	  break;
      case 'icon-statistics-record':
        this.onStatistics(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-delete-record':
      	this.onDelete(record);
     	  break;
    }
  } 
});