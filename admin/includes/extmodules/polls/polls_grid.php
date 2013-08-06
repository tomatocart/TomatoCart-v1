<?php
/*
  $Id: polls_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.polls.PollsGrid = function(config) {
  config = config || {};
  
  config.region = 'center';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords}; 

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'polls',
      action: 'list_polls'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'polls_id'
    },[
      'polls_id',
      'polls_title',
      'polls_status',
      'polls_info'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
    
  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template('{polls_info}')
  });
  config.plugins = [config.rowActions, expander];  
  
  renderStatus = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
     
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    expander,
    {
      id: 'polls_title',
      header: '<?php echo $osC_Language->get('table_heading_polls_title'); ?>',
      dataIndex: 'polls_title'
    },
    {
      header: '<?php echo $osC_Language->get('table_heading_polls_status'); ?>',
      dataIndex: 'polls_status',
      width: 80,
      align: 'center',
      renderer: renderStatus
    },
    config.rowActions
  ]);
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
  config.autoExpandColumn = 'polls_title';
  config.search = new Ext.form.TextField({name: 'search', width: 130});
  
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
    },
    '->',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
   }];  

  config.bbar = new Ext.PagingToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    iconCls: 'icon-grid',
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg
  });
  
  this.addEvents({'selectchange' : true});  
  
  Toc.polls.PollsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.polls.PollsGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createPollsDialog();
    dlg.setTitle('<?php echo $osC_Language->get('action_heading_new_poll'); ?>');
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createPollsDialog();
    dlg.setTitle(record.get('polls_title'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('polls_id'));
  },  
  
  onDelete: function(record) {
    var pollsId = record.get('polls_id');
                  
    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if (btn == 'yes') {                                                                                                                                                                 
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'polls',
              action: 'delete_poll',
              polls_id: pollsId                                        
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
      }, this);
  },

  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');
    
      Ext.Msg.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {                                                                                                                                                                 
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: { 
                module: 'polls',
                action: 'delete_polls',
                batch: batch
              },
              callback: function(options, success, response) {
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.getStore().reload();
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else {
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
    this.getStore().load();
  },
  
  onSearch: function () {
    var store = this.getStore();

    store.baseParams['search'] = this.search.getValue() || null;
    store.reload();
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
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var col = v.findCellIndex(t);
    var action = false;
    
    if (row !== false) {
      var expander = e.getTarget(".x-grid3-row-body");

      if (col > 0 || (col == false && expander != null)) {
        var record = this.getStore().getAt(row);
        this.fireEvent('selectchange', record);
      }
    
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
        var pollsId = this.getStore().getAt(row).get('polls_id');
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.setStatus(pollsId, flag);
            break;
        }
      } 
    }
  },
  
  setStatus: function(pollsId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'polls',
        action: 'set_status',
        polls_id: pollsId,
        flag: flag
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(pollsId).set('polls_status', flag);
          store.commitChanges();
        }

        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }  
  
});

