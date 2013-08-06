<?php
/*
  $Id: returns_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.ReturnsGrid = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_returns'); ?>';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders',
      action: 'get_orders_returns',
      orders_id: config.ordersId    
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'orders_returns_id'
    }, [
      'orders_returns_id', 
      'quantity',
      'date_added',
      'status',
      'status_id',
      'products',
      'admin_comments',
      'customers_comments',
      'total'
    ]),
    autoLoad: true
  });

  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template(
      '<table width="98%" style="padding-left: 20px">',
       '<tr>',
         '<td width="49%">',
           '<b><?php echo $osC_Language->get('subsection_products'); ?></b>',
           '<p>{products}</p>',
         '</td>',
         '<td>',
           '<b><?php echo $osC_Language->get('subsection_customers_comments'); ?></b>',
           '<p>{customers_comments}</p>',
         '</td>',
       '</tr>',
      '</table>')
  });  
  config.plugins = expander;
  
  config.cm = new Ext.grid.ColumnModel([
    expander,
    {header: '<?php echo $osC_Language->get("table_heading_return_id"); ?>', dataIndex: 'orders_returns_id', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get("table_heading_returned_qty"); ?>',dataIndex: 'quantity', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get("table_heading_date"); ?>',dataIndex: 'date_added', align: 'center', width: 100}, 
    {header: '<?php echo $osC_Language->get("table_heading_status"); ?>',dataIndex: 'status', align: 'center', width: 100},
    {id: 'orders-returns-comments', header: '<?php echo $osC_Language->get("table_heading_admin_comments"); ?>',dataIndex: 'admin_comments', align: 'center'}
  ]);
  config.autoExpandColumn = 'orders-returns-comments';
  
  Toc.orders.ReturnsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.ReturnsGrid, Ext.grid.GridPanel);