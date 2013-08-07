<?php
/*
  $Id: banner_table_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.banner_manager.TablePanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('panel_table_title'); ?>';
  config.layout = 'fit';
  
  config.items = this.buildForm(config.bannerId);

  Toc.banner_manager.TablePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.banner_manager.TablePanel, Ext.Panel, {

  changeTables: function(bannerId, type, month, year){
    this.frmStatic.store.baseParams['banners_id'] = bannerId;
    this.frmStatic.store.baseParams['type'] = type;
    this.frmStatic.store.baseParams['month'] = month;
    this.frmStatic.store.baseParams['year'] = year;
    
    this.frmStatic.store.reload();
  },

  buildForm: function(bannerId) {
    this.frmStatic = new Ext.grid.GridPanel({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'banner_manager',
          action: 'get_table', 
          banners_id: bannerId       
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
        }, 
        [
          'source',
          'views',
          'clicks'
        ])
      }),
      border: false,
      viewConfig: {emptyText: TocLanguage.gridNoRecords},
      columns:[
        new Ext.grid.CheckboxSelectionModel(),
        {id: 'banners_source', header: '<?php echo $osC_Language->get('table_heading_source'); ?>', dataIndex: 'source'},
        {header: '<?php echo $osC_Language->get('table_heading_views'); ?>', dataIndex: 'views'},
        {header: '<?php echo $osC_Language->get('table_heading_clicks'); ?>', dataIndex: 'clicks'}
      ],
      autoExpandColumn: 'banners_source'
    });
    
    return this.frmStatic;
  }
});