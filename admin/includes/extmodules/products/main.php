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
  include('templates/default/extensions/uploadpanel/all.js');  

  echo 'Ext.namespace("Toc.products");';
  
  include('products_grid.php');
  include('general_panel.php');
  include('meta_panel.php');
  include('data_panel.php');
  include('downloadables_panel.php');
  include('gift_certificates_panel.php');
  include('categories_panel.php');
  include('images_grid.php');
  include('images_panel.php');
  include('variants_data_panel.php');
  include('variants_panel.php');
  include('xsell_products_panel.php');  
  include('attributes_panel.php');
  include('products_dialog.php');
  include('attachments_panel.php');
  include('attachments_grid.php');
  include('attachments_dialog.php');
  include('attachments_list_dialog.php');
  include('products_duplicate_dialog.php');
  include('customizations_panel.php');
  include('customizations_dialog.php');
  include('accessories_panel.php');
  include('variants_groups_dialog.php');
  include('categories_tree_panel.php');
  include('products_main_panel.php');
?>

Ext.override(TocDesktop.ProductsWindow, {

  createWindow : function(){
    switch(this.id) {
      case 'products-dialog-win':
        win = this.createProductDialog();
        break;
      case 'products-win':
        win = this.createProductsWindow();
        break;
      case 'products_attachments-win':
        win = this.createProductsAttachmentsWindow();
        break;
    }
    win.show();
  },

  createProductsWindow: function(productId) {
    var desktop = this.app.getDesktop();
    win = desktop.getWindow('products-win');

    if(!win){
      pnl = new Toc.products.ProductsMainPanel({owner: this});

      win = desktop.createWindow({
        id: 'products-win',
        title:'<?php echo $osC_Language->get('heading_title'); ?>',
        width:920,
        height:400,
        iconCls: 'icon-products-win',
        layout: 'fit',
        items: pnl
      });
    }

    return win;
  },

  createProductDialog: function(productId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({products_id: productId, owner: this}, Toc.products.ProductDialog);
    }
        
    return dlg;
  },
  
  createProductDuplicateDialog: function(productsId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_duplicate-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({productsId: productsId, owner: this}, Toc.products.ProductsDuplicateDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
        
    return dlg;
  },
  
  createProductsAttachmentsWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('products_attachments-win');
     
    if (!win) {
      grd = new Toc.products.AttachmentsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'products_attachments-win',
        title: '<?php echo $osC_Language->get('heading_attachments_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-products_attachments-win',
        layout: 'fit',
        items: grd
      });
    }
    
    return win;
  },
  
  createAttachmentsListDialog: function(productsId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_attachments_list_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({productsId: productsId}, Toc.products.AttachmentsListDialog);
    }
        
    return dlg;
  },
  
  createAttachmentsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_attachments_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.products.AttachmentsDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    return dlg;
  },
  
  createCategoryMoveDialog: function(productId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products-move-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.products.CategoriesMoveDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    
    return dlg;
  },
  
  createCustomizationsDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('customization_fields_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.products.CustomizationsDialog);
    }
    return dlg;
  },
  
  createVariantsGroupDialog: function(group_ids) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('variants_group-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({owner: this, group_ids: group_ids}, Toc.products.VariantsGroupsDialog);
    }
    
    return dlg;
  }
});
