<?php
/*
  $Id: attachments_list_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.AttachmentsListDialog = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('heading_attachments_title'); ?>';
  
  config.width = 500;
  config.modal = true;
  config.layout = 'fit';
  config.height = 300;
  config.border =  false;
  config.iconCls = 'icon-products_attachments-win';
  config.id = "products_attachments_list_dialog-win";
  
  config.items = this.buildGrid();
  
  config.buttons = [{
    text: TocLanguage.btnAdd,
    handler: this.onAdd,
    scope: this
  },{
    text: TocLanguage.btnClose,
    handler: function() {
      this.close();
    },
    scope: this
  }];
  
  this.addEvents('saveSuccess', true);

  Toc.products.AttachmentsListDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.AttachmentsListDialog, Ext.Window, {
  buildGrid: function() {
    var dsAttachments = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'list_product_attachments'     
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'attachments_id'
      },  [
        'attachments_id',
        'attachments_name',
        'attachments_filename',
        'attachments_description',
        'action'
      ]),
      autoLoad: true
    });
        
    this.txtSearch = new Ext.form.TextField({
      emptyText: '<?php echo $osC_Language->get('empty_attachements_name'); ?>'
    });
    
    var sm = new Ext.grid.CheckboxSelectionModel();
    this.grdProductsAttachments = new Ext.grid.GridPanel({
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      border: false,
      ds: dsAttachments,
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {id: 'attachments_name', header: '<?php echo $osC_Language->get('table_heading_attachments_name'); ?>', dataIndex: 'attachments_name'},
        {header: '<?php echo $osC_Language->get('table_heading_attachments_file'); ?>', dataIndex: 'attachments_filename', width: 250},
        {header: '<?php echo $osC_Language->get('table_heading_attachments_description'); ?>', dataIndex: 'attachments_description'}
      ]),
      autoExpandColumn: 'attachments_name',
      
      tbar: [{
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.onRefresh,
        scope: this
      }, '->', this.txtSearch, ' ',
      {
        iconCls : 'search',
        handler : this.onSearch,
        scope : this
      }],
      
      bbar: new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsAttachments,
        iconCls: 'icon-grid',
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
      })
    });    
    
    return this.grdProductsAttachments;
  },
  
  onRefresh: function() {
    this.grdProductsAttachments.getStore().reload();
  },
  
  onSearch: function() {
    var attachments_name = this.txtSearch.getValue();
    var store = this.grdProductsAttachments.getStore(); 
    
    store.baseParams['attachments_name'] = attachments_name;
    store.reload();
  },
  
  onAdd: function() {
    var attachments = this.grdProductsAttachments.getSelectionModel().getSelections();
    
    this.fireEvent('saveSuccess', attachments);    
    
    this.close();
  }
});