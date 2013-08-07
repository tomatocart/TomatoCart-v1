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
  echo 'Ext.namespace("Toc.zone_groups");';
 
  include('zone_groups_dialog.php');
  include('zone_entries_dialog.php');
  include('zone_groups_grid.php');
  include('zone_entries_grid.php');
  include('zone_groups_main_panel.php');
?>

Ext.override(TocDesktop.ZoneGroupsWindow, {
	createWindow: function () {
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow('zone_groups-win');
    
		if (!win) {
			pnl = new Toc.zone_groups.MainPanel({owner: this});
      
			win = desktop.createWindow({
				id: 'zone_groups-win',
				title: '<?php echo $osC_Language->get("heading_title"); ?>',
				width: 800,
				height: 400,
				iconCls: 'icon-zone_groups-win',
				layout: 'fit',
				items: pnl
			});
		}
		
		win.show();
	},
  
	createZoneGroupsDialog: function () {
		var desktop = this.app.getDesktop();
		var dlg = desktop.getWindow('zone_groups-dialog-win');
    
		if (!dlg) {
			dlg = desktop.createWindow({}, Toc.zone_groups.ZoneGroupsDialog);
			
			dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
		}
    
		return dlg;
	},
  
	createZoneEntriesDialog: function () {
		var desktop = this.app.getDesktop();
		var dlg = desktop.getWindow('zone_entries-dialog-win');
    
		if (!dlg) {
			dlg = desktop.createWindow({}, Toc.zone_groups.ZoneEntriesDialog);
			
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
		}
    
		return dlg;
	}
});