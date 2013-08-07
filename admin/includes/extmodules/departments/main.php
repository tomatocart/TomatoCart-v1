<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.departments");';
  
  include('departments_grid.php');
  include('departments_dialog.php');
?>

Ext.override(TocDesktop.DepartmentsWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('departments-win');
     
    if(!win){
      var grd = new Toc.departments.DepartmentsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'departments-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-departments-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createDepartmentDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('departments-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.departments.DepartMentDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
      
    return dlg;
  }
});
