<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.reports_products");';
  
  include('products_viewed_grid.php');
  include('categories_purchased_grid.php');
  include('products_purchased_grid.php');
  include('low_stock_grid.php');
?>


Ext.override(TocDesktop.ReportsProductsWindow, {

  createWindow: function() {
    desktop = this.app.getDesktop();
    win = desktop.getWindow(this.id);
     
    if(!win){
      if (this.params.report == 'products-purchased') {
        grd = new Toc.reports_products.ProductsPurchasedGrid({owner: this});
      } else if (this.params.report == 'products-viewed') {
        grd = new Toc.reports_products.ProductsViewedGrid({owner: this});
      } else if (this.params.report == 'categories-purchased') {
        grd = new Toc.reports_products.CategoriesPurchasedGrid({owner: this});
      } else {
        grd = new Toc.reports_products.LowStockGrid({owner: this});
      }      
      
      win = desktop.createWindow({
        id: this.id,
        title: this.title,
        width: 800,
        height: 400,
        iconCls: this.iconCls,
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
