<?php
/*
  $Id: zone_groups_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.zone_groups.MainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';
  
  config.grdZoneEntries = new Toc.zone_groups.ZoneEntriesGrid({owner: config.owner});
  config.grdZoneGroups = new Toc.zone_groups.ZoneGroupsGrid({owner: config.owner});
 
  config.grdZoneGroups.on('selectchange', this.onGrdZoneGroupsSelectChange, this);
  config.grdZoneGroups.getStore().on('load', this.onGrdZoneGroupsLoad, this);
  config.grdZoneEntries.getStore().on('load', this.onGrdZoneEntriesLoad, this);
 
  config.items = [config.grdZoneGroups, config.grdZoneEntries];
   
  Toc.zone_groups.MainPanel.superclass.constructor.call(this,config); 
};

Ext.extend(Toc.zone_groups.MainPanel, Ext.Panel, {
  
  onGrdZoneGroupsLoad: function() {
    if (this.grdZoneGroups.getStore().getCount() > 0) {
      this.grdZoneGroups.getSelectionModel().selectFirstRow();
      record = this.grdZoneGroups.getStore().getAt(0);
      
      this.onGrdZoneGroupsSelectChange(record);
    } else {
      this.grdZoneEntries.reset();
    }
  },

  onGrdZoneGroupsSelectChange: function(record) {
    this.grdZoneEntries.setTitle('<?php echo $osC_Language->get("heading_title");?>:  '+ record.get('geo_zone_name'));
    this.grdZoneEntries.iniGrid(record);
  },

  onGrdZoneEntriesLoad: function() {
    record = this.grdZoneGroups.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('geo_zone_entries', this.grdZoneEntries.getStore().getCount());
      this.grdZoneGroups.getStore().commitChanges();
    }
  }  
});