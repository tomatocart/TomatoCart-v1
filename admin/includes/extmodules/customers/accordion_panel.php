<?php
/*
  $Id: accordion_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.AccordionPanel = function(config) {
  config = config || {};

  config.region = 'east';
  config.border = false;
  config.split = true;
  config.minWidth = 240;
  config.maxWidth = 350;
  config.width = 300;
  config.layout = 'accordion';

  config.pnlStoreCredits = new Toc.customers.StoreCreditsGrid({owner: config.owner});
  config.grdAddressBook = new Toc.customers.AddressBookGrid({owner: config.owner});
  config.grdWishlist = new Toc.customers.CustomersWishlistGrid();

  config.items = [
    config.grdAddressBook,
    config.pnlStoreCredits,
    config.grdWishlist
  ];

  Toc.customers.AccordionPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.AccordionPanel, Ext.Panel);