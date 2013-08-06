<?php
/*
  $Id: images_check_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.images.ImagesCheckDialog = function (config) {

	config = config || {};
  
	config.id = 'images-check-dialog-win';
	config.layout = 'fit';
	config.width = 480;
	config.height = 300;
	config.modal = true;
	config.iconCls = 'icon-images-win';
	config.items = this.buildGrid();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
	Toc.images.ImagesCheckDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.images.ImagesCheckDialog, Ext.Window, {
	buildGrid: function () {
		this.grdImages = new Ext.grid.GridPanel({
	    store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'images',
          action: 'check_images'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'group'
        }, [
          'group', 
          'count'
        ]),
        autoLoad: true
      }),
	    cm: new Ext.grid.ColumnModel([
        {header: '<?php echo $osC_Language->get("images_check_table_heading_groups"); ?>', dataIndex: 'group'},
        {header: '<?php echo $osC_Language->get("images_check_table_heading_results"); ?>', dataIndex: 'count', align: 'center'}
      ]),
      viewConfig: {forceFit: true},      
	    tbar: [
		    {
	        text: TocLanguage.btnRefresh,
	        iconCls: 'refresh',
	        handler: this.onRefresh,
	        scope: this
        }
      ]
    });
    
		return this.grdImages;
	},
	
	onRefresh: function () {
		this.grdImages.getStore().reload();
	}
})
