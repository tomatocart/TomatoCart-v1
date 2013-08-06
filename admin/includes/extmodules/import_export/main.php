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

  echo 'Ext.namespace("Toc.import_export");';
  
  include('import_export_dialog.php');
?>

Ext.override(TocDesktop.ImportExportWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('import_export-win');
     
    if (!win) {
      dlg = desktop.createWindow(null, Toc.import_export.ImportExportDialog);
    }
      
    dlg.show();
  }
});