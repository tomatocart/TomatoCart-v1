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

  echo 'Ext.namespace("Toc.articles_categories");';
  
  include('articles_categories_dialog.php');
  include('articles_categories_grid.php');
  include('articles_categories_general_panel.php');
  include('articles_categories_meta_info_panel.php');
?>

Ext.override(TocDesktop.ArticlesCategoriesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('articles_categories-win');
     
    if(!win){
      grd = new Toc.articles_categories.ArticlesCategoriesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'articles_categories-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-articles_categories-win',
        layout: 'fit',
        items: grd
      });
    }

    win.show();
  },
  
  createArticleCategoriesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('articles_categories-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.articles_categories.ArticlesCategoriesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }

});
