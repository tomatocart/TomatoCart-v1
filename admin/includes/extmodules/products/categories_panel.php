<?php
/*
  $Id: categories_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.products.CategoriesPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_categories'); ?>';
  config.layout = 'border';
  config.style = 'padding: 5px';
  config.treeLoaded = false;
  config.items = this.buildForm();
  
  Toc.products.CategoriesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.CategoriesPanel, Ext.Panel, {
  buildForm: function() {
    this.pnlCategoriesTree = new Ext.ux.tree.CheckTreePanel({
      region: 'center',
      name: 'categories', 
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      border: false,
      bodyStyle: 'background-color:white;',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: true, 
        baseParams: {
          module: 'products',
          action: 'get_categories_tree'
        },
        listeners: {
          load: function() {
            this.treeLoaded = true;
          },
          scope: this
        }
      })
    });  
    
    return this.pnlCategoriesTree;    
  },
  
  setCategories: function(categoryId) {
    if (this.treeLoaded == true) {
      this.pnlCategoriesTree.setValue(categoryId);
    } else {
      this.pnlCategoriesTree.loader.on('load', function(){
        this.pnlCategoriesTree.setValue(categoryId);
      }, this);
    }    
  },
  
  getCategories: function() {
    return this.pnlCategoriesTree.getValue();
  }
});