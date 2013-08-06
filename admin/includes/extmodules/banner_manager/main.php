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
  echo 'Ext.namespace("Toc.banner_manager");';
  
  include('banner_graphs_panel.php');
  include('banner_table_panel.php');
  include('banner_manager_statistics_dialog.php');
  include('banner_manager_preview_dialog.php');
  include('banner_manager_dialog.php');
  include('banner_manager_grid.php');
?>

Ext.override(TocDesktop.BannerManagerWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('banner_manager-win');
     
    if (!win) {
      grd = new Toc.banner_manager.BannerManagerGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'banner_manager-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-banner_manager-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createBannerManagerPreviewDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('banner_manager_preview-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.banner_manager.BannerManagerPreviewDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createBannerManagerStatisticsDialog: function(bannersId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('banner_manager_statistics-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({banners_id: bannersId}, Toc.banner_manager.BannerManagerStatisticsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createBannerManagerDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('banner_manager-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.banner_manager.BannerManagerDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
