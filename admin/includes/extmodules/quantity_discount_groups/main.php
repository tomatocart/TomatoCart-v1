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

  echo 'Ext.namespace("Toc.quantity_discount_groups");';  

  include('quantity_discount_groups_grid.php');
  include('quantity_discount_groups_entries_grid.php');
  include('quantity_discount_groups_dialog.php');
  include('quantity_discount_groups_entries_dialog.php');
  include('quantity_discount_groups_main_panel.php');  
?>

Ext.override(TocDesktop.QuantityDiscountGroupsWindow, {
  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('quantity_discount_groups-win');

    if(!win){                               
      pnl = new Toc.quantity_discount_groups.MainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'quantity_discount_groups-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-quantity_discount_groups-win',
        layout: 'fit',
        items: pnl
      });
    }   
    
    win.show();
  },
  
  createQuantityDiscountGroupsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('quantity_discount_groups-dialog-win');    

    if(!dlg) {
      dlg = desktop.createWindow({}, Toc.quantity_discount_groups.QuantityDiscountGroupsDialog);             
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }    
    
    return dlg;
  },
  
  createQuantityDiscountEntriesDialog : function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('quantity_discount_groups_entries-dialog-win');

    if(!dlg) {
       dlg = desktop.createWindow({},Toc.quantity_discount_groups.QuantityDiscountEntriesDialog);
       
       dlg.on('saveSuccess', function(feedback) {
         this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
       }, this);
    }
    
    return dlg;
  }
});
