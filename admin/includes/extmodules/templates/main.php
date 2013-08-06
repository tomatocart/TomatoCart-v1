<?php
/*
  $Id: main.js.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.templates");';
  
  include('templates_upload_dialog.php');
  include('templates_grid.php');
?>

Ext.override(TocDesktop.TemplatesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('templates-win');
     
    if (!win) {
      var grd = new Toc.templates.TemplatesGrid({owner: this});

      win = desktop.createWindow({
        id: 'templates-win',
        title:'<?php echo $osC_Language->get('heading_title'); ?>',
        width:800,
        height:400,
        iconCls: 'icon-templates-win',
        layout: 'fit',
        items: grd
      });
    }
       
    win.show();
  },
  
  createTemplatesUploadDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('templates-upload-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.templates.TemplatesUplaodDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
