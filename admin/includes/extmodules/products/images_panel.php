<?php
/*
  $Id: images_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.ImagesPanel = function(config) {

  config = config || {};

  config.title = '<?php echo $osC_Language->get('section_images'); ?>';
  config.layout = 'fit';
  
  config.productsId = config.productsId || null;
  config.items = this.buildForm(config.productsId);
  
  Toc.products.ImagesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.ImagesPanel, Ext.Panel, {

  buildForm: function(productsId) {
    this.grdImages = new Toc.products.ImagesGrid({productsId: productsId});
    pnlImages = new Ext.Panel({
      layout: 'border',
      border: false,
      items:  [{
        region:'east',
        layout:'accordion',
        split: true,
        width: 250,
        minSize: 175,
        maxSize: 400,
        border:false,
        items: [this.getImageUploadPanel(productsId), this.getLocalImagesPanel(productsId)]
      }, 
      this.grdImages
     ]
    });
    
   return pnlImages;
  },
  
  getImageUploadPanel: function(productsId) {
    var appendURl = '?module=products&action=upload_image';
    
    if (productsId > 0 ) {
      appendURl += ('&products_id=' + productsId);
    }
      
    this.pnlImagesUpload = new Ext.ux.UploadPanel({
      title: '<?php echo $osC_Language->get('image_remote_upload'); ?>', 
      border: false,
      id: 'products-img-upload',
      removeAllIconCls: 'remove',
      maxFileSize: 4194304,
      addText: TocLanguage.btnAdd,
      uploadText: TocLanguage.btnUpload,
      enableProgress: false,
      url: Toc.CONF.CONN_URL + appendURl
    });
    
    this.pnlImagesUpload.on('allfinished', function() {
      this.grdImages.getStore().reload();
      this.pnlImagesUpload.removeAll();
    }, this);
    
    return this.pnlImagesUpload;
  },
  
  getLocalImagesPanel: function(productsId) {
    dsLocalImages = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_local_images'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true
    });
    
    this.selLocalImages = new Ext.ux.Multiselect({
      fieldLabel:"Multiselect",
      name:"multiselect",
      style: 'padding: 5px 5px 0px 10px',
      width: 230,
      height: 220,
      store: dsLocalImages,
      legend: '<?php echo $osC_Language->get('section_images'); ?>',
      hiddenName: 'localimages[]',
      displayField: 'text',
      valueField: 'id',
      isFormField: true
    });
    
    pnlLocalImages = new Ext.Panel({
      title: '<?php echo $osC_Language->get('image_local_files'); ?>',
      layout: 'border',
      border: false,
      items:[
        {
          region: 'north',
          border: false,
          html: '<p class="form-info"><?php echo $osC_Language->get('introduction_select_local_images'); ?></p>'
        },  
        {
          region: 'center',
          border: false,
          items: this.selLocalImages
        }
      ],
      tbar: [{
        text: TocLanguage.btnAdd,
        iconCls: 'add',
        handler: this.onLocalImageAdd,
        scope:this
      }]   
    });
    
    return pnlLocalImages;
  },
  
  onLocalImageAdd: function() {
    var images = this.selLocalImages.getValue();
    if (Ext.isEmpty(images)) return;
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL, 
      params: {
        module: 'products',
        action: 'assign_local_images',
        products_id: this.productsId,
        localimages: images
      },
      callback: function(options, success, response) {
        if (success == true) {
          var result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            this.grdImages.getStore().reload();
            this.selLocalImages.store.reload();
          }
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrTitle);
        }
      },
      scope: this
    });
  }  
});