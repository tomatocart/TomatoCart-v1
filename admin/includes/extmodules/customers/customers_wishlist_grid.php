<?php
/*
  $Id: customers_wishlist_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.CustomersWishlistGrid = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_wishlist'); ?>'; 
  config.border = false;
  
  config.ds = new Ext.data.Store({ 
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'customers',
      action: 'list_wishlists'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'wishlists_products_id'
    },[
      'wishlists_products_id',
      'products_name',
      'date_added',
      'comments'      
    ])
  });
  
  config.expander = new Ext.grid.RowExpander({
    tpl:new Ext.Template(
      '<b><?php echo $osC_Language->get('field_comments'); ?></b> {comments}'
    )
  });
  config.plugins = [config.expander];
      
  config.cm = new Ext.grid.ColumnModel([
    config.expander,
    {header: '<?php echo $osC_Language->get('table_heading_products_name') ?>', dateIndex: 'products_name'},
    {header: '<?php echo $osC_Language->get('table_heading_date_added') ?>', dateIndex: 'date_added'}
  ]);
  
  config.viewConfig = {
    forceFit: true,
    emptyText: TocLanguage.gridNoRecords
  };   
  
  Toc.customers.CustomersWishlistGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.CustomersWishlistGrid, Ext.grid.GridPanel, {
  iniGrid: function(record) {
    var store = this.getStore();    
    
    store.baseParams['customers_id'] = record.get('customers_id');
    store.load();  
  }
});