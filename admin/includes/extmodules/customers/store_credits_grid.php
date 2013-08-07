<?php
/*
  $Id: store_credits_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.StoreCreditsGrid = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_store_credits'); ?>';
  config.border = false;

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'customers',
      action: 'list_store_credits'     
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'customers_credits_history_id'
    },[
      'customers_credits_history_id',
      'date_added',
      'action_type',
      'amount',
      'comments'
    ])
  });  
  
  config.cm = new Ext.grid.ColumnModel(
    [
      {header:'<?php echo $osC_Language->get('table_heading_date'); ?>', dataIndex: 'date_added'},
      {header:'<?php echo $osC_Language->get('table_heading_action'); ?>', dataIndex: 'action_type'},
      {header:'<?php echo $osC_Language->get('table_heading_blance'); ?>', dataIndex: 'amount'}
    ]
  );
  
  config.viewConfig = {
    forceFit:true,
    emptyText: TocLanguage.gridNoRecords,
    enableRowBody: true,
    getRowClass : function(record, rowIndex, config, store){
      config.body = record.get('comments');
      return 'x-grid3-row-expanded';
    }
  };
      
  config.tbar = [
    {
      text: '<?php echo $osC_Language->get('button_update_balance'); ?>',
      iconCls: 'add',
      handler: this.onUpdateBalance,
      scope: this
    }
  ];    
  
  Toc.customers.StoreCreditsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.StoreCreditsGrid, Ext.grid.GridPanel, {
  iniGrid: function(record, grdCustomers) {
    this.customersId = record.get('customers_id');
    this.grdCustomers = grdCustomers;
    
    this.store.baseParams['customers_id'] = record.get('customers_id');
    this.store.reload();
  },
  
  onUpdateBalance: function() {
 	  var dlg = this.owner.createUpdateBalanceDialog();
 	  
 	  dlg.on('saveSuccess', function(customers_credits) {
      this.store.reload();
    
      record = this.grdCustomers.getSelectionModel().getSelected() || null;
      if (record) {
        record.set('customers_credits', customers_credits);
        this.grdCustomers.getStore().commitChanges();
      }      
    }, this);
    
    dlg.show(this.customersId);
  }
});
