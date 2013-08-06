<?php
/*
  $Id: products_attributes_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products_attributes.MainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';
  
  config.grdAttributeGroups = new Toc.products_attributes.AttributeGroupsGrid({owner: config.owner});
  config.grdAttributeEntries = new Toc.products_attributes.AttributeEntriesGrid({owner : config.owner}); 
  
  config.grdAttributeGroups.on('selectchange', this.onGrdAttributeGroupsSelectChange, this);
  config.grdAttributeGroups.getStore().on('load', this.onGrdAttributeGroupsLoad, this);
  config.grdAttributeEntries.getStore().on('load', this.onGrdAttributeEntriesLoad, this);
  
  config.items = [config.grdAttributeGroups, config.grdAttributeEntries];  
    
  Toc.products_attributes.MainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.products_attributes.MainPanel, Ext.Panel, {   

  onGrdAttributeGroupsLoad: function() {
    if (this.grdAttributeGroups.getStore().getCount() > 0) {
      this.grdAttributeGroups.getSelectionModel().selectFirstRow();
      record = this.grdAttributeGroups.getStore().getAt(0);
      
      this.onGrdAttributeGroupsSelectChange(record);
    } else {
      this.grdAttributeEntries.reset();
    }
  },

  onGrdAttributeGroupsSelectChange: function(record) {
    this.grdAttributeEntries.setTitle('<?php echo $osC_Language->get('heading_title'); ?>: '+ record.get('products_attributes_groups_name'));
    this.grdAttributeEntries.iniGrid(record);
  },

  onGrdAttributeEntriesLoad: function() {
    var record = this.grdAttributeGroups.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('total_entries', this.grdAttributeEntries.getStore().getCount());
      this.grdAttributeGroups.getStore().commitChanges();
    }
  }  
});