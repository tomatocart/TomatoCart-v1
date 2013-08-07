<?php
/*
  $Id: products_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.ProductsMainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.products.CategoriesTreePanel({owner: config.owner, parent: this});
  config.grdProducts = new Toc.products.ProductsGrid({owner: config.owner, mainPanel: this});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdProducts];
  
  Toc.products.ProductsMainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.ProductsMainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdProducts.refreshGrid(categoryId);
  },
  
  getCategoriesTree: function() {
    return this.pnlCategoriesTree;
  }
});
