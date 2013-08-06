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

  echo 'Ext.namespace("Toc.products_attributes");';  

  include('products_attributes_groups_grid.php');
  include('products_attributes_groups_dialog.php');
  include('products_attributes_entries_dialog.php');
  include('products_attributes_entries_grid.php');
  include('products_attributes_main_panel.php');
?>

Ext.override(TocDesktop.ProductsAttributesWindow, {
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('products_attributes-win');

    if (!win) {                               
      pnl = new Toc.products_attributes.MainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'products_attributes-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-products_attributes-win',
        layout: 'fit',
        items: pnl
      });
    }   
    
    win.show();
  },
  
  createAttributeGroupsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_attributes-dialog-win');    

    if(!dlg) {
      dlg = desktop.createWindow({}, Toc.products_attributes.AttributeGroupsDialog);        
           
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }    
    
    return dlg;
  },
  
  createAttributeEntriesDialog : function(){
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_attributes_entries-dialog-win');

    if(!dlg) {
       dlg = desktop.createWindow({},Toc.products_attributes.AttributeEntriesDialog);
       
       dlg.on('saveSuccess', function(feedback) {
         this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
       }, this);
    }
    
    return dlg;
  }
});