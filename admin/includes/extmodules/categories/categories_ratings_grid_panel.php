<?php
/*
  $Id: categories_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.categories.RatingsGridPanel = function(config){
  config = config||{};
  
  config.title = '<?php echo $osC_Language->get('section_ratings'); ?>';
  config.layout = 'fit';
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'categories',
      action: 'list_ratings'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'ratings_id'
    }, [
      'ratings_id', 
      'ratings_text'
    ]),
    autoLoad: true
  });
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'ratings_text', header: '<?php echo $osC_Language->get("table_heading_ratings"); ?>', dataIndex: 'ratings_text'} 
  ]);
  config.autoExpandColumn = 'ratings_text';
  
  Toc.categories.RatingsGridPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.categories.RatingsGridPanel, Ext.grid.GridPanel, {

  getRatings: function() {
    var keys = this.getSelectionModel().selections.keys;
    this.ratings = keys.join(',');
  }
  
});