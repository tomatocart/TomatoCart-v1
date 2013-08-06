<?php
/*
  $Id: guest_book_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.guest_book.GuestBookGrid = function(config) {
  config = config || {};

  config.border = false;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'guest_book',
      action: 'list_guest_book'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'guest_books_id'
    },  [
      'guest_books_id',
      'title',
      'email',
      'url',
      'guest_books_status',
      'languages',
      'content',
      'date_added'
    ]),
    autoLoad: true
  });  

  config.rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
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
    {id: 'guest_books_id', header: '<?php echo $osC_Language->get('table_heading_title'); ?>', dataIndex: 'title'},
    {header: '<?php echo $osC_Language->get('table_heading_languages'); ?>', dataIndex: 'languages', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get('table_heading_date_added'); ?>', dataIndex: 'date_added', align: 'center', width: 100}, 
    {header: '<?php echo $osC_Language->get('table_heading_guest_books_status'); ?>', dataIndex: 'guest_books_status', align: 'center', width: 100, renderer: renderPublish},
    config.rowActions
  ]);
  config.autoExpandColumn = 'guest_books_id';
  
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
    steps: Toc.CONF.GRID_STEPS,
    beforePageText: TocLanguage.beforePageText,
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

  Toc.guest_book.GuestBookGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.guest_book.GuestBookGrid, Ext.grid.GridPanel, {
  onAdd: function() {
    var dlg = this.owner.createGuestBookDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show();
  },
  
  onEdit: function (record) {
    var dlg = this.owner.createGuestBookDialog();
    dlg.setTitle(record.get("title"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('guest_books_id'));
  },     

  onDelete: function(record) {
    var guestBooksId = record.get('guest_books_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'guest_book',
              action: 'delete_guest_book',
              guest_books_id: guestBooksId
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
                module: 'guest_book',
                action: 'delete_guest_books',
                batch: batch
              },
              callback: function(options, success, response){
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
  
  onAction: function(action, guestBooksId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'guest_book',
        action: action,
        guest_books_id: guestBooksId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(guestBooksId).set('guest_books_status', flag);
          store.commitChanges();
                  
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        } else {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
      },
      scope: this
    });
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
        var guestBooksId = this.getStore().getAt(row).get('guest_books_id');
        var flag = 0;
        var module = 'set_status';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, guestBooksId, flag);

            break;
        }
      }
    }
  }  
});
