<?php
/*
  $Id: refunds_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.RefundsGrid = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_refunds'); ?>';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'orders',
      action: 'get_refund_history',
      orders_id: config.ordersId    
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'orders_refunds_id'
    },[
      'orders_refunds_id',
      'orders_refunds_type',
      'total_products',
      'sub_total',
      'total_refund',
      'date_added',
      'comments',
      'products',
      'totals'
    ]),
    autoLoad: true
  });

  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template(
      '<table width="55%">',
        '<tr>',
          '<td>',
            '<p><b><?php echo $osC_Language->get('section_products'); ?></b></p>',
            '<p>{products}</p>',
            '<p align="right">{totals}</p>',
          '</td>',
        '</tr>',
      '</table>')
  });  
  config.plugins = expander;
  
  config.cm = new Ext.grid.ColumnModel([
    expander,
    {header: '<?php echo $osC_Language->get('table_heading_date_added');?>', dataIndex: 'date_added', width: 100, align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_refunds_type');?>', dataIndex: 'orders_refunds_type', width: 100, align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_total_products');?>', dataIndex: 'total_products', width: 100, align: 'center'},
    {id: 'orders-refunds-comments', header: '<?php echo $osC_Language->get('table_heading_comments');?>', dataIndex: 'comments', align: 'left'},
    {header: '<?php echo $osC_Language->get('table_heading_total_refund');?>', dataIndex: 'total_refund', width: 100, align: 'center'}
  ]);
  config.autoExpandColumn = 'orders-refunds-comments';
  
  Toc.orders.RefundsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.RefundsGrid, Ext.grid.GridPanel);