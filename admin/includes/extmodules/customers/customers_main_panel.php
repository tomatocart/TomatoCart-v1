<?php
/*
  $Id: customers_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';
  
  config.pnlAccordion = new Toc.customers.AccordionPanel({owner: config.owner});
  config.grdCustomers = new Toc.customers.CustomersGrid({owner: config.owner, pnlAccordion: config.pnlAccordion}); 
  
  config.grdCustomers.on('selectchange', this.onGrdCustomersSelectChange, this);
  config.grdCustomers.getStore().on('load', this.onGrdCustomersLoad, this);
  
  config.items = [config.grdCustomers, config.pnlAccordion];    
    
  Toc.customers.mainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.customers.mainPanel, Ext.Panel, {

  onGrdCustomersLoad: function() {
    if (this.grdCustomers.getStore().getCount() > 0) {
      this.grdCustomers.getSelectionModel().selectFirstRow();
      record = this.grdCustomers.getStore().getAt(0);
      
      this.onGrdCustomersSelectChange(record);
    } else {
      this.pnlAccordion.grdAddressBook.reset();
    }
  },

  onGrdCustomersSelectChange: function(record) {
    this.pnlAccordion.grdAddressBook.iniGrid(record);
    this.pnlAccordion.pnlStoreCredits.iniGrid(record, this.grdCustomers);
    this.pnlAccordion.grdWishlist.iniGrid(record);
  }
});