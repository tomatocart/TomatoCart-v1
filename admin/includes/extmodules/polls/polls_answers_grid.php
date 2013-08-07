<?php
/*
  $Id: polls_answers_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.polls.PollsAnswersGrid = function(config) {
  config = config || {};
  
  this.pollsId = null;
  
  config.title = '<?php echo $osC_Language->get('section_polls_answers'); ?>';
  config.region = 'east';
  config.split = true;
  config.minWidth = 400;
  config.maxWidth = 520;
  config.width = 400;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords}; 
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'polls',
      action: 'list_poll_answers'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'polls_answers_id'
    },
    [
      'polls_answers_id',
      'answers_title',
      'votes_count'
    ])
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
     
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel(
    [
      config.sm,
      {id:'polls_answers_id', header:'<?php echo $osC_Language->get('table_heading_polls_answers_title'); ?>', dataIndex: 'answers_title'},
      {header:'<?php echo $osC_Language->get('table_heading_polls_answers_votes_count'); ?>', dataIndex: 'votes_count'},
      config.rowActions     
    ]
  );
  config.autoExpandColumn = 'polls_answers_id';
      
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

  Toc.polls.PollsAnswersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.polls.PollsAnswersGrid, Ext.grid.GridPanel, {
  iniGrid: function(record) {
    this.pollsId = record.get('polls_id');
    this.pollTilte = record.get('polls_title');
    var store = this.getStore();
    
    store.baseParams['polls_id'] = this.pollsId;
    store.load();  
  },
  
  onAdd: function() {
    if (this.pollsId) {
      dlg = this.owner.createPollsAnswersDialog();
     
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);

      dlg.show(this.pollsId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function(record) {
    var pollsAnswersId = record.get('polls_answers_id');
    var dlg = this.owner.createPollsAnswersDialog();
    dlg.setTitle(this.pollTilte);
   
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(this.pollsId, pollsAnswersId);  
  },
  
  onDelete: function(record) {
    var pollsAnswersId = record.get('polls_answers_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'polls',
              action: 'delete_poll_answer',
              polls_answers_id: pollsAnswersId
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.getStore().reload();
                this.owner.app.showNotification( {title: TocLanguage.msgSuccessTitle, html: result.feedback} );
              }else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      },
      this);       
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
                action: 'delete_poll_answers',
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
    this.setTitle('<?php echo $osC_Language->get('section_polls_answers'); ?>');
    this.pollsId = null;
    this.getStore().removeAll();
  }
});
