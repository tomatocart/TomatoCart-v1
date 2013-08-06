<?php
/*
  $Id: email_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.email.EmailMainPanel = function(config) {
  config = config || {};
  
  config.border = false;
  config.layout = 'border';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.items = [
    this.getAccountTreePanel(),
    {
      region:'center',
      layout: 'border', 
      border: false,                  
      items: [this.getMessagesGrid(), this.getMessagePanel(config.owner)]
    },
    this.getContactDetailPanel()
  ];
  
  config.tbar = [
    {text: '<?php echo $osC_Language->get('button_check_email'); ?>', iconCls: 'check-email', handler: this.onCheckEmail, scope: this},
    '-',
    {text: '<?php echo $osC_Language->get('button_compose'); ?>', iconCls: 'compose-email', handler: this.onCompose, scope: this},
    '-',
    {text: TocLanguage.btnDelete, iconCls: 'remove', handler: this.onBatchDelete, scope: this},
    '-',
    {text: '<?php echo $osC_Language->get('button_accounts'); ?>', iconCls: 'accounts', handler: this.onAccounts, scope: this},
    '-',
    {text: '<?php echo $osC_Language->get('button_contact_info'); ?>', iconCls: 'contactInfo', enableToggle: true, handler: this.onContactInfoClick, scope: this},
    '->',
    this.txtSearch = new Ext.form.TextField({emptyText: '<?php echo $osC_Language->get('introduction_search'); ?>', width: 150}),
    ' ', 
    {text: '', iconCls: 'search', handler: this.onSearch, scope: this}
  ];
  
  this.addEvents('saveSuccess', 'deleteSuccess', 'saveFoldersSuccess', 'deleteFoldersSuccess');
  
  Toc.email.EmailMainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.email.EmailMainPanel, Ext.Panel, {
  
  getAccountTreePanel: function() {
    this.pnlAccounts = new Toc.email.AccountsTree();
    
    this.pnlAccounts.on('nodechange', function(accountsId, foldersId) {
      this.txtSearch.setValue();
      
      if (accountsId == null) {
        this.dsMessages.removeAll();
      } else {
        this.loadMessages(accountsId, foldersId, false, null);
      }
    }, this);
    
    return this.pnlAccounts;
  },
  
  getMessagePanel: function(owner) {
    this.pnlMessage = new Toc.email.MessagePanel({owner: owner});
    this.pnlMessage.on('load', this.onPnlMessageLoad, this);

    return this.pnlMessage;
  },
  
  getMessagesGrid: function() {
    this.dsMessages = new Ext.data.Store({
      proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
        timeout: 600000,
        url: Toc.CONF.CONN_URL,
        method: 'POST'})
      ),
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'email',
        action: 'list_messages'  
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'id'
      }, 
      [
        'id',
        'icon',
        'attachments',
        'new',
        'subject',
        'from_address',
        'sender',
        'size',
        'date',
        'fetch_time',
        'priority',
        'messages_flag'
      ]),
      listeners: {
        load: this.onDsMessagesLoad,
        scope: this
      }
    });
    
    var rowActions = new Ext.ux.grid.RowActions({
      actions:[
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', function(grid, record){ this.onDelete(record.get('id'));}, this);

    var renderMessage = function(value, p, record) {
      return ( (record.data['new'] == '1') ? ('<b>' + value + '</b>') : value ); 
    };

    this.grdMessages = new Ext.grid.GridPanel({
      store: this.dsMessages,
      height: 250,
      region: 'north',
      border: false,
      layout: 'fit',
      loadMask: true,
      split: true,
      animCollapse: false,
      enableDragDrop: true,
      ddGroup: 'emailMessageDD',
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      plugins: rowActions,
      cm: new Ext.grid.ColumnModel([
        {header: "&nbsp;", width: 30, dataIndex: 'icon'},
        {header: '<?php echo $osC_Language->get('table_heading_from'); ?>', dataIndex: 'from_address', width: 150, renderer: renderMessage},
        {id: 'email_subject', header: '<?php echo $osC_Language->get('table_heading_subject'); ?>', dataIndex: 'subject', renderer: renderMessage},
        {header: '<?php echo $osC_Language->get('table_heading_date'); ?>', dataIndex: 'date', width: 65, align: 'center', renderer: renderMessage},
        rowActions
      ]),
      autoExpandColumn: 'email_subject',
      bbar: new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: this.dsMessages,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText : TocLanguage.beforePageText,
        firstText: TocLanguage.firstText,
        lastText: TocLanguage.lastText,
        nextText: TocLanguage.nextText,
        prevText: TocLanguage.prevText,
        afterPageText: TocLanguage.afterPageText,
        refreshText: TocLanguage.refreshText,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg,
        prevStepText: TocLanguage.prevStepText,
        nextStepText: TocLanguage.nextStepText
      }),
      listeners: {
        cellclick: this.onGrdMessagesCellClick,
        rowdblclick: this.openMessage,
        rowcontextmenu: this.onGrdMessageContextMenu,
        scope: this
      }
    });

    this.grdMessageContextMenu = this.getGrdMessageContextMenu();    

    return this.grdMessages;
  },
  
  getGrdMessageContextMenu: function() {
    var menu = new Ext.menu.Menu({
      items: [
        {iconCls: 'add', text: '<?php echo $osC_Language->get('button_open'); ?>', handler: this.openMessage, scope: this},
        '-',
        this.btnMarkAsRead = new Ext.menu.Item({
          iconCls: 'email_read', 
          text: '<?php echo $osC_Language->get('button_mark_as_read'); ?>', 
          handler: function(){this.markMessage('read');},
          scope:this      
        }),
        this.btnMarkAsUnread = new Ext.menu.Item({
          iconCls: 'email_unread',
          text: '<?php echo $osC_Language->get('button_mark_as_unread'); ?>',
          handler: function(){this.markMessage('unread');},
          scope: this
        })
      ]
    });
    
    return menu;
  },

  onGrdMessageContextMenu: function(grd, row, event) {
    event.stopEvent();
    
    grd.getSelectionModel().clearSelections();
    grd.getSelectionModel().selectRow(row);

    var record = grd.getStore().getAt(row);
    if (record.get('new') == 0) {
      this.btnMarkAsRead.disable();
      this.btnMarkAsUnread.enable();
    } else {
      this.btnMarkAsRead.enable();
      this.btnMarkAsUnread.disable();
    }
    
    var positions = event.getXY();
    
    this.grdMessageContextMenu.showAt([positions[0], positions[1]]);
  },
  
  getContactDetailPanel: function() {
    this.grdCustomerInfo = new Ext.grid.GridPanel({
      region: 'north', 
      height: 150,
      border: false,
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'email',
          action: 'get_customer_info'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'name'
        },
        [
          'name',
          'value'  
        ])
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'email_customer_info_field', header: '&nbsp;', dataIndex: 'name'},
        {header:'&nbsp;', dataIndex: 'value', width: 120}
      ]),
      autoExpandColumn: 'email_customer_info_field'
    });
    
    var orderExpander = new Ext.grid.RowExpander({
      tpl: new Ext.XTemplate(
        '<p><b><?php echo $osC_Language->get('introduction_order_detail'); ?></b></p>',
        '{products}',
        '<hr />',
        '{totals}'
      )
    });
      
    this.grdOrders = new  Ext.grid.GridPanel({
      region: 'center', 
      border: false,
      plugins: orderExpander, 
      title: '<?php echo $osC_Language->get('action_heading_orders'); ?>',
      cm: new Ext.grid.ColumnModel([
        orderExpander,
        {id: 'emails_orders_date_purchased', header: '<?php echo $osC_Language->get('table_heading_date_purchased'); ?>', dataIndex: 'date_purchased'},
        { header: '<?php echo $osC_Language->get('table_heading_order_total'); ?>', dataIndex: 'order_total', width: 70},
        { header: '<?php echo $osC_Language->get('table_heading_status'); ?>', dataIndex: 'orders_status_name', width: 55}
      ]), 
      autoExpandColumn: 'emails_orders_date_purchased',
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'email',
          action: 'list_orders'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'orders_id'
        }, [
          'orders_id',
          'order_total',
          'date_purchased',
          'orders_status_name',
          'products',
          'totals'        
        ])
        
      })
    });
      
    this.pnlContactDetail = new Ext.Panel({
      title: '<?php echo $osC_Language->get('action_heading_customer_infomation'); ?>',
      region:'east',
      hidden: true,
      width: 260,
      split: true,
      layout: 'border',
      border: false,
      items: [this.grdCustomerInfo, this.grdOrders],
      listeners: {
        beforeshow : function(){
          var record = this.grdMessages.getSelectionModel().getSelected();
          
          if (record != null) {
            var sender = record.get('sender');
            
            this.grdCustomerInfo.getStore().baseParams['from'] = sender;
            this.grdCustomerInfo.getStore().reload();
            
            this.grdOrders.getStore().baseParams['email'] = sender;
            this.grdOrders.getStore().reload();
          }
        },
        scope: this
      }
    });
    
    return this.pnlContactDetail;
  },
  
  onDsMessagesLoad: function() {
    this.dsMessages.baseParams['check_email'] = false;
    
    var unseen = this.grdMessages.store.reader.jsonData.unseen;
    this.pnlAccounts.updateFolder(unseen);
  },
  
  onPnlMessageLoad: function(unseen) {
    var record = this.grdMessages.getSelectionModel().getSelected();
    record.set('new', '0');
    record.commit();
    
    this.pnlAccounts.updateFolder(unseen);
  },
  
  onGrdMessagesCellClick: function(grid, row, col) {
    var record = grid.getStore().getAt(row);
    
    if (col != 0 && col != 4) {
      this.pnlMessage.loadMessage(record.get('id'), this.pnlAccounts.getAccountsId(), this.pnlAccounts.getFoldersId(), record.get('fetch_time'));
      
      if (this.pnlContactDetail.isVisible()) {
        this.grdCustomerInfo.getStore().baseParams['from'] = record.get('sender');
        this.grdCustomerInfo.getStore().reload();
        
        this.grdOrders.getStore().baseParams['email'] = record.get('sender');
        this.grdOrders.getStore().reload();
      }
    }
  },
  
  openMessage: function() {
    var record = this.grdMessages.getSelectionModel().getSelected();
    var id = record.get('id');
    var flag = record.get('messages_flag');
    
    if (flag == <?php echo EMAIL_MESSAGE_DRAFT;?>) {
      var dlg = this.owner.createEmailComposerDialog();
      
      dlg.on('saveSuccess', function(){
        this.pnlMessage.reload();
      }, this);
      
      dlg.show(id);
    } else {
      var record = this.grdMessages.getSelectionModel().getSelected();
      
      var dlg = this.owner.createMessageDetailDialog();
      dlg.show();
      
      dlg.pnlMessage.loadMessage(record.get('id'), this.pnlAccounts.getAccountsId(), this.pnlAccounts.getFoldersId(), record.get('fetch_time'));
    }
  },
  
  onAccounts: function() {
    var dlg = this.owner.createAccountListDialog();
    
    dlg.on('saveSuccess', function(feedback, accountNode){
      this.pnlAccounts.updateAccountNode(accountNode);
    }, this);
    
    dlg.on('deleteSuccess', function(accountsId) {
      this.pnlAccounts.deleteAccount(accountsId);
    }, this);
    
    dlg.on('batchDeleteSuccess', function(accounts) {
      this.pnlAccounts.deleteAccounts(accounts);
    }, this);
        
    dlg.show();
  },
  
  onContactInfoClick: function() {
    if (this.pnlContactDetail.isVisible()) {
      this.pnlContactDetail.setVisible(false);
    } else {
      this.pnlContactDetail.setVisible(true);
    }
    
    this.doLayout();
  },
  
  onCompose: function() {
    if (this.pnlAccounts.hasAccount() > 0) {
      var dlg = this.owner.createEmailComposerDialog();
       
      dlg.on('saveSuccess', function(){
        this.onRefresh();
      }, this);
      
      dlg.show();  
    } else {
      alert("<?php echo $osC_Language->get('introduction_set_account');?>");
    }
  },
  
  onDelete: function(id) {
    var node = this.pnlAccounts.getSelectionModel().getSelectedNode();  
    accountId = this.pnlAccounts.getAccountsIdByNode(node);
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'email',
              action: 'delete_message',
              id: id,
              accounts_id: accountId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
                
              if (result.success == true) {
                this.grdMessages.body.unmask();
                
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                
                this.dsMessages.reload();
                  
                if (this.pnlMessage.messagesId == id) {
                  this.pnlMessage.reset();
                }
              }else{
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, this);
  },
  
  onBatchDelete: function() {
    var keys = this.grdMessages.getSelectionModel().selections.keys;
        
    if (keys.length > 0) {
      var accountId = this.pnlAccounts.getAccountsId();
          
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            this.grdMessages.body.mask('Loading...');
            
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'email',
                action: 'delete_messages',
                accounts_id: accountId,
                batch: keys.join(',')
              },
              callback: function(options, success, response) {
                this.grdMessages.body.unmask();
                
                var result = Ext.decode(response.responseText);
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  
                  for(var i = 0; i < keys.length; i++) {
                    if(this.pnlMessage.messagesId = keys[i]) {
                      this.pnlMessage.reset();
                    }
                  }
                  
                  this.dsMessages.reload();
                }else{
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
    this.pnlAccounts.getRootNode().reload();
    this.dsMessages.removeAll();
    this.pnlMessage.body.update('');
  },
  
  onCheckEmail: function() {
    this.txtSearch.setValue();
    
    if (this.pnlAccounts.getRootNode().childNodes.length > 0) {
      this.loadMessages(this.pnlAccounts.getAccountsId(), this.pnlAccounts.getFoldersId(), true, null);
    } else {
      alert("<?php echo $osC_Language->get('introduction_set_account');?>");
    }
    
  },
  
  onSearch: function() {
    this.pnlMessage.reset();
    
    this.loadMessages(this.pnlAccounts.getAccountsId(), this.pnlAccounts.getFoldersId(), false, this.txtSearch.getValue());
  },
  
  loadMessages: function(accountsId, foldersId, checkEmail, search) {
    this.pnlMessage.reset();
    this.grdMessages.getSelectionModel().clearSelections();

    this.dsMessages.baseParams['accounts_id'] = accountsId;
    this.dsMessages.baseParams['folders_id'] = foldersId;
    this.dsMessages.baseParams['check_email'] = checkEmail;
    this.dsMessages.baseParams['search'] = search;
    
    this.dsMessages.load();
  },
  
  markMessage: function(flag){
    var record = this.grdMessages.getSelectionModel().getSelected();

    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
        params: {
          module: 'email',
          action: 'update_message_status',
          accounts_id: this.pnlAccounts.getAccountsId(),
          folders_id: this.pnlAccounts.getFoldersId(),
          is_read: ( (flag == 'read') ? 1: 0 ),
          id: record.get('id')
        },
        callback: function(options, success, response){
          var result = Ext.decode(response.responseText);

          if(result.success == true){
            if (flag == 'unread') {
              record.set('new', '1');
              record.commit();
            } else {
              record.set('new', '0');
              record.commit();
            }
            
            this.pnlAccounts.updateFolder(result.unseen);
          }
        },
        scope: this
    }); 
  }
  
});