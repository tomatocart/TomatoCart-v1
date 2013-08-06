<?php
  /*$Id: coupons_redeem_history_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
 */
?>

Toc.coupons.RedeemHistoryDialog = function(config) {
  
  config = config || {};
  
  config.id = 'coupons-redeem-history-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title_oupon_redeem_history'); ?>';
  config.layout = 'fit';
  config.width = 600;
  config.height = 400;
  config.iconCls = 'icon-coupons-win';
  config.items = this.buildHistoryGrid();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope: this     
    }
  ];
  
  Toc.coupons.RedeemHistoryDialog.superclass.constructor.call(this, config);
  
};

Ext.extend(Toc.coupons.RedeemHistoryDialog, Ext.Window, {
  show: function(couponsId) {
    this.grdHistory.getStore().baseParams['coupons_id'] = couponsId;
    this.grdHistory.getStore().load();     
    
    Toc.coupons.RedeemHistoryDialog.superclass.show.call(this);   
  },
  buildHistoryGrid: function() {
    var dsHistory = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'coupons',
        action: 'get_redeem_history'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'customers_id'
      },[
          'customers_id',
          'customers_name',
          'orders_id',
          'redeem_amount',
          'redeem_date',
          'redeem_id_address'    
      ])
    });
    
    this.grdHistory = new Ext.grid.GridPanel({
      border: false,
      viewConfig: {emptyText: TocLanguage.gridNoRecords},      
      ds: dsHistory,      
      cm: new Ext.grid.ColumnModel([
          {id: 'coupons_redeem_customers_name', header: '<?php echo $osC_Language->get('table_heading_customers_name'); ?>', dataIndex: 'customers_name' },
          {header: '<?php echo $osC_Language->get('table_heading_orders_id'); ?>', dataIndex: 'orders_id', align: 'center'},
          {header: '<?php echo $osC_Language->get('table_heading_redeem_amount'); ?>', dataIndex: 'redeem_amount', align: 'center'},
          {header: '<?php echo $osC_Language->get('table_heading_redeem_date'); ?>', dataIndex: 'redeem_date', align: 'center'},
          {header: '<?php echo $osC_Language->get('table_heading_redeem_ip'); ?>', dataIndex: 'redeem_id_address', align: 'center'}
      ]),
      autoExpandColumn: 'coupons_redeem_customers_name',
      
      bbar: new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsHistory,
        iconCls: 'icon-grid',
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
      })
    });
    
    return this.grdHistory;
  }
});