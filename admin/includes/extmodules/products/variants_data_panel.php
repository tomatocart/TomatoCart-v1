<?php
/*
  $Id: variants_data_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.VariantDataPanel = function(config) {
  config = config || {};
  
  config.id = config.valuesId;
  config.border = false;
  config.bodyStyle = 'padding: 3px;';
  config.layout = 'form';
  config.autoScroll = true;
  config.split = true;
  this.dlgProducts = config.dlgProducts;
  config.items = this.buildForm(config.valuesId, config.data, config.downloadable);
  
  Toc.products.VariantDataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.VariantDataPanel, Ext.Panel, {

  buildForm: function(valuesId, data, downloadable) {
    var items = [
      {
        xtype: 'fieldset',
        title: '<?php echo $osC_Language->get('fieldset_lengend_data_title'); ?>',
        labelWidth: 80,
        labelSeparator: ' ',
        autoHeight: true,
        defaults: {
          anchor: '94%'
        },
        items: [
          {
            fieldLabel: '<?php echo $osC_Language->get('field_quantity'); ?>',
            name: 'variants_quantity[' + valuesId + ']',
            xtype: 'numberfield',
            allowDecimals: false,
            allowNegative: false,
            value: data.variants_quantity
          },
          {
            fieldLabel: '<?php echo $osC_Language->get('field_price_net'); ?>',
            xtype: 'textfield',
            name: 'variants_net_price[' + valuesId + ']',
            value: data.variants_net_price
          },
          {
            fieldLabel: '<?php echo $osC_Language->get('field_sku'); ?>',
            xtype: 'textfield',
            name: 'variants_sku[' + valuesId + ']',
            value: data.variants_sku
          },
          {
            fieldLabel: '<?php echo $osC_Language->get('field_model'); ?>',
            xtype: 'textfield',
            name: 'variants_model[' + valuesId + ']',
            value: data.variants_model
          },
          {
            fieldLabel: '<?php echo $osC_Language->get('field_weight'); ?>',
            xtype: 'textfield',
            name: 'variants_weight[' + valuesId + ']',
            value: data.variants_weight
          }, 
          {
            layout: 'column',
            border: false,
            items:[
              {
                layout: 'form',
                labelSeparator: ' ',
                labelWidth: 80,
                border: false,
                items: [{
                  fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', 
                  xtype:'radio', 
                  name: 'variants_status_' + valuesId, 
                  boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>', 
                  xtype:'radio', 
                  inputValue: '1', 
                  checked: (data.variants_status == 1 ? true: false) 
                }]
              }, 
              {
                layout: 'form',
                border: false,
                items: [{
                  boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', 
                  xtype:'radio', 
                  name: 'variants_status_' + valuesId, 
                  hideLabel: true, 
                  inputValue: '0', 
                  checked: (data.variants_status == 1 ? false: true) 
                }]
              }
            ]
          }
        ]
      }, 
      
      this.fsImages = new Ext.form.FieldSet({
        title: '<?php echo $osC_Language->get("fieldset_lengend_image_title"); ?>',
        labelWidth: 80, 
        labelSeparator: ' ', 
        defaults: {anchor: '94%'}, 
        autoHeight: true, 
        items: this.buildImagesPanel(valuesId, data.variants_image)
      })
    ];

    if (downloadable == true || !Ext.isEmpty(data.variants_download_filename)) {
      items.push(this.fsUpload = this.buildUploadFieldset(valuesId, data.variants_download_file, data.variants_download_filename));
    }

    //register a listener to images grid to update images panel
    this.dlgProducts.pnlImages.grdImages.getStore().on('load', this.onImagesChange, this);
    this.dlgProducts.pnlData.on('producttypechange', this.onProductTypeChange, this);

    return items;
  },
  
  buildImagesPanel: function(valuesId, selectedImage) {
    var dsImages = this.dlgProducts.pnlImages.grdImages.getStore();
    var pnlImages = {
      layout: 'column',
      border: false,
      items: []
    };
    
    if ((count = dsImages.getCount()) > 0) {
      for (var i = 0; i < count; i++ ) {
        var imageID = dsImages.getAt(i).get('id');
        var imageName = dsImages.getAt(i).get('name');
        var imagePath = dsImages.getAt(i).get('image');
        var inputValue = imageID || imageName; 
        
        var pnlImage = {
          layout: 'column',
          columnWidth: .33,
          border: false,
          items: [
            {
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items: [
                {
                  xtype: 'radio', 
                  name: 'variants_image_' + valuesId, 
                  hideLabel: true,
                  inputValue: inputValue,
                  checked: ((inputValue == selectedImage) ? true : false)
                }
              ]
            },
            {
              xtype: 'panel',
              border: false,
              html: '<div style="margin: 3px; cursor:pointer;">' + imagePath + '</div>'
            }
          ]
        };
        
        pnlImages.items.push(pnlImage);
      }
    } else {
      pnlImages.items.push({
        xtype: 'statictextfield', 
        hideLabel: true, 
        value: '<?php echo $osC_Language->get('ms_notice_no_products_image');?>', 
        style: 'margin-bottom: 10px'
      });
    }
    
    return pnlImages;
  },
  
  buildUploadFieldset: function(valuesId, downloadFilePath, downloadFileName) {
    var html = '';
    var file = this.dlgProducts.pnlData.tabExtraOptions;
    if (!Ext.isEmpty(downloadFileName)) {
      html = '<a href ="' + downloadFilePath +'">' + downloadFileName + '</a>';
    }
    
    var fsDownLoad = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get("fieldset_lengend_download_title"); ?>',
      labelWidth: 80,
      labelSeparator: ' ',
      autoHeight: true,
      defaults: {
        anchor: '95%'
      },
      items: [  
        {
          xtype: 'fileuploadfield', 
          fieldLabel: '<?php echo $osC_Language->get("field_file"); ?>', 
          name: 'products_variants_download_' + valuesId
        },
        {xtype: 'panel',  html: html, style: 'text-align: center', border: false }
      ]
    });
    
    return fsDownLoad;
  },

  onImagesChange: function() {
    this.fsImages.removeAll();
    this.fsImages.add(this.buildImagesPanel(this.valuesId, this.data.variants_image));
  },
  
  onProductTypeChange: function (type) {
    if (type == '<?php echo PRODUCT_TYPE_DOWNLOADABLE; ?>') {
      this.downloadable = true;
      
      this.add(this.fsUpload = this.buildUploadFieldset(this.valuesId, this.data.variants_download_file, this.data.variants_download_filename));

      this.doLayout(); 
    } else {
      this.downloadable = false;
      
      if (!Ext.isEmpty(this.fsUpload)) {
        this.remove(this.fsUpload);
        this.doLayout();
      }
    }
  }
});