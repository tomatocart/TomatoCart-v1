<?php
/*
  $Id: account_list_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
 
Toc.email.AccountListDialog = function(config){
  
  config = config || {};

  config.id = 'account_list_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_accounts'); ?>';
  config.layout = 'fit';
  config.iconCls = 'icon-account-win';
  config.width = 600;
  config.height = 300;
  config.modal = true;
  config.items = this.bulidAccountsGrid();
  
  config.buttons = [      
    {       
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents('saveSuccess', 'deleteSuccess', 'batchDeleteSuccess', 'saveFoldersSuccess', 'deleteFoldersSuccess');
    
  Toc.email.AccountListDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.email.AccountListDialog, Ext.Window, {

  bulidAccountsGrid: function(){
    var sm = new Ext.grid.CheckboxSelectionModel();
    
    var rowActions = new Ext.ux.grid.RowActions({
      actions:[
        {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onRowAction, this);    
    
    this.grdAccounts = new Ext.grid.GridPanel({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'email',
          action: 'list_accounts'  
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'accounts_id'
        },[
          'accounts_id',
          'accounts_email',
          'accounts_name', 
          'incoming_mail_type',
          'incoming_mail_host'
          ]),
        autoLoad: true 
      }),
      border: false,    
      loadMask: true,
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {header:'<?php echo $osC_Language->get('table_heading_name'); ?>', dataIndex: 'accounts_name', width: 100},
        {id: 'account_list_email', header:'<?php echo $osC_Language->get('table_heading_email'); ?>', dataIndex: 'accounts_email'},
        {header:'<?php echo $osC_Language->get('table_heading_type'); ?>', dataIndex: 'incoming_mail_type', width: 60, align: 'center'},
        {header:'<?php echo $osC_Language->get('table_heading_host'); ?>', dataIndex: 'incoming_mail_host', width: 150},
        rowActions
      ]),
      autoExpandColumn: 'account_list_email',
      plugins: rowActions,
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      tbar: [
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
          scope:this            
        }
      ],
      listeners: {
        rowdblclick: this.onEdit , 
        scope: this
      }
    });  
       
    return this.grdAccounts;
  },

  onAdd: function() {
    var dlg = this.owner.createAccountDialog(this.owner);
    
    dlg.on('saveSuccess', function(feedback, accountsNode){
      this.onRefresh();
      
      this.fireEvent('saveSuccess', feedback, accountsNode);
    }, this);

    dlg.show();
  },
  
  onBatchDelete: function() {
    var keys = this.grdAccounts.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'email',
                action: 'delete_accounts',
                batch: keys.join(',')
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                  
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                    
                  this.onRefresh();
                
                  this.fireEvent('batchDeleteSuccess', keys);
                }else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        },
        this
      );
    }else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onDelete: function(record) {
    var accountsId = record.get('accounts_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
            module: 'email',
            action: 'delete_accounts',
            batch: accountsId
          },
          callback: function(options, success, response) {
            var result = Ext.decode(response.responseText);
                  
            if (result.success == true) {
              this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                    
              this.onRefresh();
              
              this.fireEvent('deleteSuccess', accountsId);
            }else {
              Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
          },
          scope: this
        });   
      }
    }, this);
  },
  
  onEdit: function(grid, row) {
    var dlg = this.owner.createAccountDialog(this.owner);
    var record = this.grdAccounts.getStore().getAt(row); 
    
    dlg.setTitle(record.get("accounts_email"));

    dlg.on('saveSuccess', function(feedback , accountNode) {
      this.onRefresh();
      
      this.fireEvent('saveSuccess', feedback , accountNode);
    }, this);
    
    dlg.show(record.data.accounts_id);
  },
  
  onRefresh: function() {
    this.grdAccounts.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(grid, row);
        break;
    }
  } 
    
});