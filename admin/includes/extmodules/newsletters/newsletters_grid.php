<?php
/*
  $Id: newsletters_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.NewslettersGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'newsletters',
      action: 'list_newsletters'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'newsletters_id'
    }, [
      'newsletters_id',
      'title',
      'size',
      'module',
      'sent',
      'action'
    ]),
    autoLoad: true
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['','',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'newsletters_title', header: '<?php echo $osC_Language->get('table_heading_newsletters'); ?>', dataIndex: 'title'},
    {header: '<?php echo $osC_Language->get('table_heading_size'); ?>', width: 60, align: 'center', dataIndex: 'size'},
    {header: '<?php echo $osC_Language->get('table_heading_module'); ?>', dataIndex: 'module', width: 140, align: 'center'},
    {header: '<?php  echo $osC_Language->get('table_heading_sent'); ?>', dataIndex: 'sent', width: 60, align: 'center'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'newsletters_title';
  
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
      iconCls:'refresh',
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
  
  Toc.newsletters.NewslettersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.newsletters.NewslettersGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createNewslettersDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createNewslettersDialog();
    dlg.setTitle(record.get('title'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('newsletters_id'));
  },
  
  onDelete: function(record) {
    var newslettersId = record.get('newsletters_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'newsletters',
              action: 'delete_newsletter',
              newsletters_id: newslettersId
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
                module: 'newsletters',
                action: 'delete_newsletters',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, this);

    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onSendEmails: function(record) {
    var module = record.get('module');
    var newslettersId = record.get('newsletters_id');
    
    switch(module) {
      case 'email':
        var dlg = this.owner.createSendEmailsDialog();
        break;
      
      case 'newsletter': 
        var dlg = this.owner.createSendNewslettersDialog();
        break;
      
      case 'product_notification':
        var dlg = this.owner.createSendProductNotificationsDialog();
        dlg.show(newslettersId);
    }
    
    dlg.on('sendSuccess', function() {
      this.onRefresh();
    }, this);
            
    dlg.show(newslettersId);    
  },
  
  onLog: function(record) {
    var dlg = this.owner.createLogDialog();
    dlg.show(record.get('newsletters_id'));
  },  
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;

      case 'icon-send-email-record':
        this.onSendEmails(record);
        break;

      case 'icon-delete-record':
        this.onDelete(record);
        break;

      case 'icon-log-record':
        this.onLog(record);
        break;  
    }
  } 
});