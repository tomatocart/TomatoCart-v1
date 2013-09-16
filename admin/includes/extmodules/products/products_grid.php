<?php
/*
  $Id: products_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.ProductsGrid = function(config) {

  config = config || {};
  
  config.border = false;
  config.region = 'center';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.animCollapse = false;
  config.enableDragDrop = true;
  config.ddGroup = 'productDD';

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products',
      action: 'list_products'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_id'
    }, [
      {name: 'products_id'},
      {name: 'products_name'},
      {name: 'products_sku'},
      {name: 'products_frontpage'},
      {name: 'products_status'},
      {name: 'products_price', type: 'string'},
      {name: 'products_quantity', type: 'int'}
    ]),
    remoteSort: true
  });
  
  renderStatus = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  }; 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete},
      {iconCls: 'icon-copy-record', qtip: '<?php echo $osC_Language->get('action_duplicate') ?>'}],
      widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id:'products_name', header: "<?php echo $osC_Language->get('table_heading_products'); ?>", sortable: true, dataIndex: 'products_name'},
    {header: "<?php echo $osC_Language->get('table_heading_frontpage'); ?>", align: 'center', renderer: renderStatus, dataIndex: 'products_frontpage', width: 90},
    {header: "<?php echo $osC_Language->get('table_heading_sku'); ?>", align: 'center', sortable: true, dataIndex: 'products_sku', width: 80},
    {header: "<?php echo $osC_Language->get('table_heading_status'); ?>", align: 'center', renderer: renderStatus, sortable: true, dataIndex: 'products_status', width: 80},
    {header: "<?php echo $osC_Language->get('table_heading_price'); ?>", dataIndex: 'products_price', sortable: true, width: 80, align: 'right'},
    {header: "<?php echo $osC_Language->get('table_heading_quantity'); ?>", dataIndex: 'products_quantity', sortable: true, width: 80, align: 'right'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'products_name';
  
  config.txtSearch = new Ext.form.TextField({
    width:160,
    paramName: 'search'
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    }, 
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    }, 
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->',
    config.txtSearch,
    ' ', 
    {
      iconCls : 'search',
      handler : this.onSearch,
      scope : this
    }
  ];

  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnActivate,
        iconCls:'publish',
        handler: function(){
          thisObj.onBatchStatusClick(1);
        }
      },
      {
        text: TocLanguage.btnDeactivate,
        iconCls:'unpublish',
        handler: function(){
          thisObj.onBatchStatusClick(0);
        }        
      }
    ],
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

  Toc.products.ProductsGrid.superclass.constructor.call(this, config);
};


Ext.extend(Toc.products.ProductsGrid, Ext.grid.GridPanel, {

  onAdd: function(){
    var dlg = this.owner.createProductDialog();

    dlg.on('saveSuccess', function(result){
      this.onRefresh();
      
      this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result});
    }, this);
    
    dlg.show(this.mainPanel.getCategoriesTree().getCategoriesPath(null));
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createProductDialog(record.get("products_id"));
    dlg.setTitle(record.get("products_name"));
    
    dlg.on('saveSuccess', function(result) {
      this.onRefresh();
      
      this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result});
    }, this);
    
    dlg.show();
  },
  
  onDelete: function(record) {
    var productsId = record.get('products_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'products',
              action: 'delete_product',
              products_id: productsId
            },
            callback: function(options, success, response){
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, this);
  },
  
  onDuplicate: function(record) {
    var dlg = this.owner.createProductDuplicateDialog(record.get("products_id"));
    dlg.setTitle(record.get("products_name"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'products',
                action: 'delete_products',
                batch: batch
              },
              callback: function(options, success, response){
                var result = Ext.decode(response.responseText);
                
                if(result.success == true){
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, 
        this);

    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function(){
    this.getStore().reload();
  },
  
  onClick: function(e, target) {
    var t = e.getTarget(),
        v = this.view,
        row = v.findRowIndex(t),
        col = v.findCellIndex(t),
        action = false,
        module;
        
    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }

      if (action != 'img-button') {
        var productsId = this.getStore().getAt(row).get('products_id');
        var colname = this.getColumnModel().getDataIndex(col);
        
        if(colname == 'products_frontpage') {
          module = 'set_frontpage';
        }
        
        if(colname == 'products_status') {
          module = 'set_status';
        }

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, productsId, flag);
            break;
        }
      }
    }
  },
  
  onAction: function(action, productsId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'products',
        action: action,
        products_id: productsId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          if(action == 'set_frontpage') {
            store.getById(productsId).set('products_frontpage', flag);
          } else {
            store.getById(productsId).set('products_status', flag);
          }
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        } else {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
      },
      scope: this
    });
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-copy-record':
        this.onDuplicate(record);
        break;
    }
  },
  
  refreshGrid: function (categoriesId) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    store.load();
  },
  
  onSearch: function(){
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();
          
    store.baseParams['search'] = filter;
    store.reload();
  },
  
  onBatchStatusClick: function(flag) {
    var keys = this.getSelectionModel().selections.keys;
    
    if(keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        flag ? TocLanguage.msgActiveConfirm : TocLanguage.msgDeactiveConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'products',
                action: 'batch_set_status',
                batch: batch,
                status: flag
              },
              callback: function(options, success, response){
                var result = Ext.decode(response.responseText);
                
                if(result.success == true){
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});

                  var store = this.getStore();
                  Ext.each(keys, function(key) {
                    store.getById(key).set('products_status', flag);
                  }, this);
                  
                  store.commitChanges();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, 
        this);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  }
});