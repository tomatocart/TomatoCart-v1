<?php
/*
  $Id: product_variants_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.product_variants.MainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';
  
  config.grdVariantsEntries = new Toc.product_variants.ProductVariantsEntriesGrid({owner: config.owner});
  config.grdVariantsGroups = new Toc.product_variants.ProductVariantsGroupsGrid({owner: config.owner});
 
  config.grdVariantsGroups.on('selectchange', this.onGrdVariantsGroupsSelectChange, this);
  config.grdVariantsGroups.getStore().on('load', this.onGrdVariantsGroupsLoad, this);
  config.grdVariantsEntries.getStore().on('load', this.ongrdVariantsEntriesLoad, this);
 
  config.items = [config.grdVariantsGroups, config.grdVariantsEntries];
   
  Toc.product_variants.MainPanel.superclass.constructor.call(this,config); 
};

Ext.extend(Toc.product_variants.MainPanel,Ext.Panel,{
  
  onGrdVariantsGroupsLoad: function() {
    if (this.grdVariantsGroups.getStore().getCount() > 0) {
      this.grdVariantsGroups.getSelectionModel().selectFirstRow();
      record = this.grdVariantsGroups.getStore().getAt(0);
      
      this.onGrdVariantsGroupsSelectChange(record);
    } else {
      this.grdVariantsEntries.reset();
    }
  },

  onGrdVariantsGroupsSelectChange: function(record) {
    this.grdVariantsEntries.setTitle('<?php echo $osC_Language->get("heading_title");?>:  '+ record.get('products_variants_groups_name'));
    this.grdVariantsEntries.iniGrid(record);
  },

  ongrdVariantsEntriesLoad: function() {
    record = this.grdVariantsGroups.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('total_entries', this.grdVariantsEntries.getStore().getCount());
      this.grdVariantsGroups.getStore().commitChanges();
    }
  }  
});