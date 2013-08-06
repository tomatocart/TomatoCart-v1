<?php
/*
  $Id: file_manager_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.file_manager.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  
  config.pnlDirectoryTree = new Toc.file_manager.DirectoryTreePanel({owner: config.owner, parent: this});
  config.grdDirectoryList = new Toc.file_manager.DirectoryListGrid({owner: config.owner, mainPanel: this});
  
  config.pnlDirectoryTree.on('selectchange', this.onPnlDirectoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlDirectoryTree, config.grdDirectoryList];
  
  Toc.file_manager.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.file_manager.mainPanel, Ext.Panel, {
  
  onPnlDirectoriesTreeNodeSelectChange: function(directory) {
    this.grdDirectoryList.changeDirectory(directory);
  },
  
  getDirectoryTreePanel: function() {
    return this.pnlDirectoryTree;
  }
});
