<?php
/*
  $Id: modules_tree_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.languages.ModulesTreePanel = function(config) {

  config = config || {};

  config.title = '<?php echo $osC_Language->get('tree_head_title'); ?>';
  config.autoScroll = true;
  config.region = 'west';
  config.width = 170;
  config.minWidth = 170;
  config.maxWidth = 170;
  config.split = true;
  config.rootVisible = false;
  config.border = false;
  config.autoHeight = true;
  
  config.root = new Ext.tree.AsyncTreeNode({
    text: '<?php echo $osC_Language->get('heading_title'); ?>',
    id: 'root',
    expanded: true
  });
  
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'languages',
      action: 'list_translation_groups',
      languages_id: config.languagesId
    },
    listeners: {
    	load: function() {
    		this.setGroupSelected('general');
    	},
    	scope: this
    }
  });
    
  config.listeners = {
    click: function(node) {
      config.grdTranslations.setTranslationGroup(node.id);
    }
  };
  
  Toc.languages.ModulesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.languages.ModulesTreePanel, Ext.tree.TreePanel, {
	setGroupSelected: function(nodeId) {
		var selectedNode = this.getNodeById(nodeId);
		selectedNode.select();
	}
});
