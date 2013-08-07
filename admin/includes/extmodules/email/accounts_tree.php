<?php
/*
  $Id: accountsTree.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.email.AccountsTree = function(config){
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('heading_title_account_tree'); ?>';
  config.layout = 'fit';
  config.region = 'west';
  config.width = 190;
  config.minWidth = 190;
  config.split = true;
  config.border = false;
  config.autoScroll = true;
  config.rootVisible = false;
  config.enableDrop = true;
  config.ddGroup = 'emailMessageDD'; 
  config.menuContext = this.getContextMenu();
  config.root = {
    nodeType: 'async',
    text: 'root',
    id: 'root',
    expanded: true,
    uiProvider: false,
    draggable: false
  };
  
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true, 
    baseParams: {
	    module: 'email',
	    action: 'load_accounts_tree'
    },
    listeners: {
      beforeload: function() {this.body.mask(TocLanguage.loadingText);},
      load: this.onRootNodeLoad,
      scope: this
    }
  });
  
  config.listeners = {
    contextmenu: this.onContextMenu,
    nodedragover: this.onNodeDragOver,
    beforenodedrop: this.onBeforeNodeDrop,
    click: this.onNodeClick,
    scope: this
  }; 
  
  this.addEvents({'nodechange' : true});
  
  Toc.email.AccountsTree.superclass.constructor.call(this, config);  
}

Ext.extend(Toc.email.AccountsTree, Ext.tree.TreePanel, {
  
  getAccountsIdByNode: function(node) {
    var tmp = node.id.toString().split('_');
    var accounts_id = tmp[0];
    
    return accounts_id;
  },
  
  getFoldersIdByNode: function(node) {
    var tmp = node.id.toString().split('_');
    var folders_id = tmp[1];
    
    return folders_id;
  },
  
  onNodeDragOver: function(dropEvent) {
    var source = this.selectedNode;
    var target = dropEvent.target;
    
    var target_accounts_id = this.getAccountsIdByNode(target);
    var target_folders_id = this.getFoldersIdByNode(target);   
    
    var original_accounts_id = this.getAccountsIdByNode(source);
    var original_folders_id = this.getFoldersIdByNode(source);
    
    if (target.attributes.type == 'account') {
      dropEvent.cancel = true;
      
      return false;
    }
    
    if (target_accounts_id != original_accounts_id) {
      dropEvent.cancel = true;
      
      return false;
    }
    
    if (target_folders_id == original_folders_id) {
      dropEvent.cancel = true;
      
      return false;
    }
    
    return true;
  },
  
  onBeforeNodeDrop: function(dropEvent) {
    var target = dropEvent.target;
    var emails = dropEvent.data.selections;
    
    var target_folders_id = this.getFoldersIdByNode(target);   
    var target_accounts_id = this.getAccountsIdByNode(target);
    
    keys = [];
    for (i = 0; i < emails.length; i++) {
      keys.push(emails[i].get('id'));
    }
 
    if(keys.length > 0) {
      this.body.mask(TocLanguage.loadingText);
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'email',
          action: 'move_messages',
          target_folders_id: target_folders_id,
          accounts_id: target_accounts_id,
          batch: keys.join(',')
        },
        callback: function(options, success, response) {
          this.body.unmask();
          result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            this.fireNodeChangeEvent(); 
            this.updateFolder(result.target_unseen, true, target);    
          }
        },
        scope: this
      }); 
    }
  },
  
  onRootNodeLoad: function () {
    this.body.unmask();
        
    if (this.root.hasChildNodes()){
      if (this.root.item(0).hasChildNodes()) {
        this.getSelectionModel().select(this.root.item(0).item(0));
        this.selectedNode = this.root.item(0).item(0);
      }
    }
    
    this.fireNodeChangeEvent();
  },
  
  getContextMenu: function () {
    var cxtMenu = new Ext.menu.Menu({    
      items: [
        {iconCls: 'add', text: '<?php echo $osC_Language->get('button_add_folder'); ?>', handler: this.addFolder, scope: this},
        '-',
        this.btnDeleteFolder = new Ext.menu.Item({iconCls: 'remove', text: '<?php echo $osC_Language->get('button_delete_folder'); ?>', handler: this.deleteFolder, scope: this }),
        {iconCls: 'remove', text: '<?php echo $osC_Language->get('button_empty_folder'); ?>', handler: this.emptyFolder, scope: this}
      ],
      listeners: {
        hide: function() {this.getSelectionModel().select(this.selectedNode);},
        scope: this
      }
    }); 
    
    return cxtMenu; 
  },
    
  onContextMenu: function (node, event) {
    event.stopEvent();

    if (node.attributes.type == 'folder') {
      var selModel = this.getSelectionModel();
    
      if (!selModel.isSelected(node)) {
        selModel.clearSelections();
        selModel.select(node);
      }
      
      if (node.attributes.parent_id == '0') {
        this.btnDeleteFolder.disable();
      } else {
        this.btnDeleteFolder.enable();
      }
      
      var positions = event.getXY();
      
      this.menuContext.showAt([positions[0], positions[1]]);   
    }
  },
  
  onNodeClick: function (node) {
    if ( (node.attributes.type == 'folder') && (node.id != this.selectedNode.id) ) {
      this.selectedNode = node;
      
      this.fireNodeChangeEvent();
    } else {
      return false;
    }
  },
  
  deleteAccount: function (accountsId) {
    var node = this.root.findChild('id', accountsId);
    var selectedAccountsId = this.getAccountsIdByNode(this.selectedNode);
    
    this.root.removeChild(node);
    if (accountsId == selectedAccountsId) {
      this.onRootNodeLoad();
    }
  },
  
  deleteAccounts: function (accounts) {
    var containsCurrentId = false;
    var selectedAccountsId = this.getAccountsIdByNode(this.selectedNode); 
    
    Ext.each(accounts, function (accountsId) {
      var node = this.root.findChild('id', accountsId);

      this.root.removeChild(node);
      if (accountsId == selectedAccountsId) {
        containsCurrentId = true;
      }    
    }, this);
    
    if (containsCurrentId == true) {
      this.onRootNodeLoad();
    }
  },
  
  fireNodeChangeEvent: function () {
    var accounts_id = null;
    var folders_id = null;
    
    if (this.root.hasChildNodes()) {
      var accounts_id = this.getAccountsIdByNode(this.selectedNode);
      var folders_id = this.getFoldersIdByNode(this.selectedNode);
    }
    
    this.fireEvent('nodechange', accounts_id, folders_id);
  },
  
  addFolder: function() {
    var node = this.getSelectionModel().getSelectedNode();
          
    var accounts_id = this.getAccountsIdByNode(node);
    var folders_id = this.getFoldersIdByNode(node);     

    Ext.MessageBox.prompt(
      '<?php echo $osC_Language->get('field_name'); ?>', 
      '<?php echo $osC_Language->get('introduction_new_folder'); ?>', 
      function(button, text) {
        if (button == 'ok') {
          this.body.mask(TocLanguage.loadingText);
           
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'email',
              action: 'add_folder',
              accounts_id: accounts_id,
              folders_id: folders_id,
              folders_name: text
            },
            callback: function(options, success, response) {
              this.body.unmask();
              
              var result = Ext.decode(response.responseText);
              if (result.success == true) {
                
                node.appendChild({
                  id: accounts_id + '_' + result.folders_id, 
                  text: text, 
                  iconCls: node.attributes.iconCls, 
                  parent_id: folders_id, 
                  leaf: true, 
                  type: 'folder'
                });
                
                node.expand();
                node.lastChild.attributes.name = text;
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

  deleteFolder: function() {
    var node = this.getSelectionModel().getSelectedNode();
          
    var accounts_id = this.getAccountsIdByNode(node);
    var folders_id = this.getFoldersIdByNode(node);   
  
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          this.body.mask(TocLanguage.loadingText); 
          
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'email',
              action: 'delete_folder',
              accounts_id: accounts_id,
              folders_id: folders_id
            },
            callback: function(options, success, response) {
              this.body.unmask();

              var result = Ext.decode(response.responseText);
              if (result.success == true) {
                if ( node.id == this.selectedNode.id ) {
                  this.selectedNode = node.parentNode;
                  
                  this.fireNodeChangeEvent();
                }
                
                node.remove();
                
                this.getSelectionModel().select(this.selectedNode);
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
  }, 

  emptyFolder: function(){
    var node = this.getSelectionModel().getSelectedNode();
          
    var accounts_id = this.getAccountsIdByNode(node);
    var folders_id = this.getFoldersIdByNode(node);   
  
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      '<?php echo $osC_Language->get('msgEmptyConfirm'); ?>',
      function(btn) {
        if (btn == 'yes') {
          this.body.mask(TocLanguage.loadingText); 
          
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'email',
              action: 'empty_folder',
              accounts_id: accounts_id,
              folders_id: folders_id
            },
            callback: function(options, success, response) {
              this.body.unmask();

              var result = Ext.decode(response.responseText);
              if (result.success == true) {
                if ( node.id == this.selectedNode.id ) {
                  this.fireNodeChangeEvent();
                }
                
                this.getSelectionModel().select(this.selectedNode);
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
  },
  
  updateFolder: function(unseen, specilNode, node) {
    if (arguments[1] == true) {
      if (unseen > 0) {
        node.setText('<b>' + node.attributes.name + ' (' + unseen + ')' + '</b>');
      } else {
        node.setText(node.attributes.name );
      }
    } else {
	    if (unseen > 0) {
	      this.selectedNode.setText('<b>' + this.selectedNode.attributes.name + ' (' + unseen + ')' + '</b>');
	    } else {
	      this.selectedNode.setText(this.selectedNode.attributes.name);
	    }
    }
  },
  
  updateAccountNode: function(node) {
    if (this.root.childNodes.length > 0){
      var n = this.root.findChild('id', node.id);
      
      if (n != null) {
        n.setText(node.text); 
      } else {
	      this.root.appendChild(node);
	    }
    } else {
      this.root.reload();
    }
  },
  
  hasAccount: function() {
    return (this.root.childNodes.length > 0);
  },
  
  getAccountsId: function() {
    return this.getAccountsIdByNode(this.selectedNode); 
  },
  
  getFoldersId: function() {
    return this.getFoldersIdByNode(this.selectedNode);   
  }
});