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

  echo 'Ext.namespace("Toc.reports_customers");';
  
  include('orders_total_grid.php');
  include('best_orders_grid.php');
?>

Ext.override(TocDesktop.ReportsCustomersWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow(this.id);
    var grd = null;
     
    if (!win) {
      if (this.params.report == 'orders-total') {
        grd = new Toc.reports_customers.OrdersTotalGrid({owner: this});
      } else {
        grd = new Toc.reports_customers.BestOrdersGrid({owner: this});
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
