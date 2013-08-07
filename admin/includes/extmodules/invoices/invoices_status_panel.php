<?php
/*
  $Id:invoices_status_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.invoices.InvoicesStatusPanel = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_status_history'); ?>';
  config.layout = 'border';
  
  config.items = this.buildForm(config.ordersId);  
  
  Toc.invoices.InvoicesStatusPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.invoices.InvoicesStatusPanel, Ext.Panel, {
  
  buildForm: function(ordersId){
    this.grdInvoicesStatus = new Ext.grid.GridPanel({
      region: 'center',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'invoices',
          action: 'list_orders_status',
          orders_id: ordersId     
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          id: 'date_added'
        },[
          'date_added',
          'status',
          'comments',
          'customer_notified'
        ]),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {header: '<?php echo $osC_Language->get('table_heading_date_added');?>', dataIndex: 'date_added', width: 120, align: 'center'},
        {header: '<?php echo $osC_Language->get('table_heading_status');?>', dataIndex: 'status', width: 120, align: 'center'},
        {id: 'invoices-status-comments', header: '<?php echo $osC_Language->get('table_heading_comments');?>', dataIndex: 'comments'},
        {header: '<?php echo $osC_Language->get('table_heading_customer_notified');?>', dataIndex: 'customer_notified', width: 120, align: 'center'}
      ]),
      autoExpandColumn: 'invoices-status-comments',
      border: false
    });
        
    return [this.grdInvoicesStatus];
  }
});