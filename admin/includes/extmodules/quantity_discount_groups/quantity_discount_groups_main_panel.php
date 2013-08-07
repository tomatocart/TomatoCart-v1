<?php
/*
  $Id: quantity_discount_groups_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.quantity_discount_groups.MainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';  
  
  config.grdQuantityDiscountGroups = new Toc.quantity_discount_groups.QuantityDiscountGroupsGrid({owner: config.owner});
  config.grdQuantityDiscountEntries = new Toc.quantity_discount_groups.QuantityDiscountEntriesGrid({owner: config.owner}); 
  
  config.grdQuantityDiscountGroups.on('selectchange', this.onGrdQuantityDiscountGroupsSelectChange, this);
  config.grdQuantityDiscountGroups.getStore().on('load', this.onGrdQuantityDiscountGroupsLoad, this);                                 
  config.grdQuantityDiscountEntries.getStore().on('load', this.onGrdQuantityDiscountEntriesLoad, this);
  
  config.items = [config.grdQuantityDiscountGroups, config.grdQuantityDiscountEntries];  
    
  Toc.quantity_discount_groups.MainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.quantity_discount_groups.MainPanel, Ext.Panel, {   

  onGrdQuantityDiscountGroupsLoad: function() {
    if (this.grdQuantityDiscountGroups.getStore().getCount() > 0) {
      this.grdQuantityDiscountGroups.getSelectionModel().selectFirstRow();
      record = this.grdQuantityDiscountGroups.getStore().getAt(0);
      
      this.onGrdQuantityDiscountGroupsSelectChange(record);
    } else {
      this.grdQuantityDiscountEntries.reset();
    }
  },

  onGrdQuantityDiscountGroupsSelectChange: function(record) {
    this.grdQuantityDiscountEntries.setTitle('<?php echo $osC_Language->get('heading_title'); ?>: '+ record.get('quantity_discount_groups_name'));
    this.grdQuantityDiscountEntries.iniGrid(record);
  },

  onGrdQuantityDiscountEntriesLoad: function() {
    record = this.grdQuantityDiscountGroups.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('total_entries', this.grdQuantityDiscountEntries.getStore().getCount());
      this.grdQuantityDiscountGroups.getStore().commitChanges();
    }
  }  
});