<?php
/*
  $Id: images_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.images.ImagesGrid = function (config) {

  config = config || {};
    
  config.region = 'center'; 
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'images',
      action: 'list_images'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'module'
    }, [
      'module', 
      'run'
    ]),
    autoLoad: true
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-execute-record', qtip: TocLanguage.tipExecute}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    {id: 'images-module', header: '<?php echo $osC_Language->get("table_heading_modules"); ?>', dataIndex: 'module'},
    rowActions
  ]);
  config.autoExpandColumn = 'images-module';

  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
      
  Toc.images.ImagesGrid.superclass.constructor.call(this, config);
};
Ext.extend(Toc.images.ImagesGrid, Ext.grid.GridPanel, {
  onCheck: function () {
    var dlg = this.owner.createImagesCheckDialog();
    
    dlg.setTitle(this.getSelectionModel().getSelected().data.module);
    
    dlg.show();
  },
  
  onResizeImages: function() {
    var dlg = this.owner.createImagesResizeDialog();
    
    dlg.setTitle(this.getSelectionModel().getSelected().data.module);
    
    dlg.show();
  },
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (this.getSelectionModel().getSelected().data.run) {
      case 'check':
        this.onCheck();
        break;
        
      case 'resize':
        this.onResizeImages();
        break;
    }
  }
});