<?php
/*
  $Id: images_resize_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.images.ImagesResizeDialog = function (config) {
  config = config || {};
  
  config.id = 'images-resize-dialog-win';
  config.layout = 'fit';
  config.width = 480;
  config.height = 300;
  config.modal = true;
  config.iconCls = 'icon-images-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      id: 'btn-execute-resize-images',
      text: TocLanguage.tipExecute,
      handler: function () {
        Ext.getCmp('btn-execute-resize-images').hide();
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.images.ImagesResizeDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.images.ImagesResizeDialog, Ext.Window, {
  buildForm: function () {
    this.lstImage = new Ext.ux.Multiselect({
      fieldLabel: '<?php echo $osC_Language->get('images_resize_field_groups'); ?>',
      name: "groups[]",
      width: 250,
      height: 150,
      style: 'margin-bottom: 10px',
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'images',
          action: 'get_image_groups'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          fields: [
            'id',
            'text'
          ]}
        ),
        autoLoad: true                                                                                 
      }),
      legend: '<?php echo $osC_Language->get('images_resize_table_heading_groups'); ?>',
      displayField: 'text',
      valueField: 'id',
      isFormField: true
    });
    
    this.chkImage = new Ext.form.Checkbox({
      fieldLabel: '<?php echo $osC_Language->get('images_resize_field_overwrite_images'); ?>',
      name: 'overwrite',
      inputValue: '1'
    });
    
    
    this.frmImage = new Ext.form.FormPanel({
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150,
      items:[this.lstImage, this.chkImage]
    });
    
    return this.frmImage;
  },
  
  submitForm: function () {
    groups = this.lstImage.getValue() || '';
    overwrite = this.chkImage.getValue() ? 1 : '';
    
    this. removeAll();
    
    this.grdImages = new Ext.grid.GridPanel({
      cm: new Ext.grid.ColumnModel([
        {header: '<?php echo $osC_Language->get("images_resize_table_heading_groups"); ?>', dataIndex: 'group'},
        {header: '<?php echo $osC_Language->get("images_resize_table_heading_total_resized"); ?>', dataIndex: 'count'}
      ]),
      store: new Ext.data.Store({
        proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
          timeout: 600000,
          url: Toc.CONF.CONN_URL,
          method: 'POST'})
        ),
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'images',
          action: 'list_images_resize_result',
          overwrite: overwrite,
          'groups[]': groups
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
      loadMask: true,
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
    
    this.add(this.grdImages);
    this.doLayout();
  }
});