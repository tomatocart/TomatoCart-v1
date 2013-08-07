<?php
/*
  $Id: directory_list_tree.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.file_manager.DirectoryTreePanel = function(config) {
  config = config || {};

  config.region = 'west';
  config.border = false;
  config.autoScroll = true;
  config.width = 160;
  config.split = true;
  config.rootVisible = true;
  
  config.root = {
    nodeType: 'async',
    id: '/',
    text: '/',
    expanded: true,
    draggable: false
  };
  config.currentPath = '/';
  
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true, 
    baseParams: {
      module: 'file_manager',
      action: 'list_nodes'
    },
    listeners: {
      load: function() {
        var currentNode = this.getNodeById(this.currentPath);
        currentNode.select();
      },
      scope: this
    }
  });
  
  config.tbar = [{ 
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.onRefresh,
    scope: this
  }];
  
  config.listeners = {
    click: this.onDirectoryNodeClick,
    beforeload: this.onBeforeLoad,
    contextmenu: this.onDirectoryNodeRightClick
  }; 
  
  this.addEvents({'selectchange' : true});
  
  Toc.file_manager.DirectoryTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.file_manager.DirectoryTreePanel, Ext.tree.TreePanel, {
  setCurrentPath: function (path) {
    var path = path.replace('//', '/');
    var currentNode = this.getNodeById(path);
    currentNode.select();
    currentNode.expand();
    this.currentPath = path;
    
    this.fireEvent('selectchange', path);
  },
  
  onDirectoryNodeClick: function (node) {
    this.setCurrentPath(node.id);
  },
  
  reloadCurrentPath: function() {
    var currentNode = this.getNodeById(this.currentPath);
    currentNode.reload();
  },
  
  getCurrentPath: function () {
    return this.currentPath;
  },

  onDirectoryNodeRightClick: function(node, event) {
    event.preventDefault();
    node.select();
    
    this.menuContext = new Ext.menu.Menu({
      items: [
        {
          text: TocLanguage.btnAdd,
          iconCls: 'add',
          handler: function() {
            var dlg = this.owner.createNewDirectoryDialog();
            
            dlg.on('saveSuccess', function() {
              node.reload();
              node.expand();
              if (node.id == this.currentPath) {
                this.fireEvent('selectchange', this.currentPath);
              }
            }, this);            
            
            dlg.show(node.id);
          },
          scope: this          
        },
        {
          text: TocLanguage.tipEdit,
          iconCls: 'edit',
          handler: function() {
            var dlg = this.owner.createRenameDirectoryDialog();
            
            dlg.on('saveSuccess', function() {
              parentNode = node.parentNode;
              this.currentPath = parentNode.id;
              parentNode.reload();
            }, this);      
            
            dlg.show(node.id);
          },
          scope: this
        },
        {
          text: TocLanguage.tipDelete,
          iconCls: 'remove',
          handler:  function() {
            Ext.MessageBox.confirm(
              TocLanguage.msgWarningTitle, 
              TocLanguage.msgDeleteConfirm,
              function(btn) {
                if (btn == 'yes') {
                  Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                      module: 'file_manager',
                      action: 'batch_delete',
                      batch: node.text,
                      directory: node.parentNode.id
                    },
                    callback: function(options, success, response) {
                      var result = Ext.decode(response.responseText);
                      
                      if (result.success == true) {
                        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                        
                        parentNode = node.parentNode;
                        this.currentPath = parentNode.id;
                        parentNode.reload();                        
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
          scope: this
        }
      ]
    });
    
    this.menuContext.showAt(event.getXY());
  },
  
  onBeforeLoad: function(node) {
    var directory = node.getPath('text').substring(1);
    this.getLoader().baseParams['directory'] = directory;
  },
  
  onRefresh: function () {
    root = this.getNodeById('/');
    root.reload();
    
    this.setCurrentPath('/');
  }
  
});