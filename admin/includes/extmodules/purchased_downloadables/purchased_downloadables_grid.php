<?php
/*
  $Id: purchased_downloadables_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.purchased_downloadables.PurchasedDownloadablesGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'purchased_downloadables',
      action: 'list_purchased_downloadables'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'orders_products_download_id'
    }, [
      'orders_products_download_id',
      'products_name',
      'file_name',
      'customer',
      'date_purchased',
      'total_downloads',
      'status',
      'history'
    ]),
    autoLoad: true
  }); 
  
  var expander = new Ext.grid.RowExpander({
    tpl: new Ext.Template(
        '<p><b><?php echo $osC_Language->get('section_redeem_history'); ?></b></p>{history}')
  });  
  config.plugins = expander;
  
  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {id:'purchased_downloadables_code', header: '<?php echo $osC_Language->get('table_heading_purchased_downloadables_products_name'); ?>', dataIndex: 'products_name', width: 200, sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_purchased_downloadables_file_name'); ?>', dataIndex: 'file_name', align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_purchased_downloadables_customer'); ?>', dataIndex: 'customer', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_purchased_downloadables_date_purchased'); ?>', dataIndex: 'date_purchased', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_purchased_downloadables_total_downloads'); ?>', dataIndex: 'total_downloads', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_purchased_downloadables_status'); ?>', dataIndex: 'status', align: 'center', renderer: renderPublish, sortable: true}
  ]);
  config.autoExpandColumn = 'purchased_downloadables_code';
  
  config.txtSearch = new Ext.form.TextField({
    name: 'search',
    width: 150,
    hideLabel: true
  });

  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->',
    config.txtSearch, 
    ' ', 
    { 
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    beforePageText : TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });
  
  Toc.purchased_downloadables.PurchasedDownloadablesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.purchased_downloadables.PurchasedDownloadablesGrid, Ext.grid.GridPanel, {

  onRefresh: function() {
    this.getStore().reload();
  },
  
  onSearch: function() {
    var store = this.getStore();
    
    store.baseParams['search'] = this.txtSearch.getValue() || null;
    store.reload();
  },
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;
  
    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }

      if (action != 'img-button') {
        var downloadId = this.getStore().getAt(row).get('orders_products_download_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, downloadId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, downloadId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'purchased_downloadables',
        action: action,
        orders_products_download_id: downloadId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(downloadId).set('status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }
});