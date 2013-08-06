<?php
/*
  $Id: watermark_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.watermark.WatermarkDialog = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  
  config.width = 750;
  config.height = 400;
  config.id = "watermark-win";
  config.bodyStyle = 'padding: 10px';
  config.iconCls = 'icon-watermark-win';

  config.items = this.buildForm();
  
  config.buttons = [{
    text: TocLanguage.btnClose,
    handler: function() {
      this.close();
    },
      scope:this
  }];
  
  Toc.watermark.WatermarkDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.watermark.WatermarkDialog, Ext.Window, {
  show: function() {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {  
        module: 'watermark',
        action: 'load_uploaded_image'
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          if (!Ext.isEmpty(result.image)) {
            this.imageUploaded = result.image;
	          var image = '<img src =" ' + '../images/' + result.image + ' " width="80" height="80" />';
	          
	          this.frmWatermarkImage.findById('watermark_image').body.update(image);
	        }else {
	          this.frmWatermarkImage.findById('watermark_image').body.update('');
	          this.btnDeleteWatermarkImage.setDisabled(true);
	        }  
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });

    Toc.watermark.WatermarkDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.pnlWatermarkGenerate = new Ext.Panel({
      columnWidth: 0.58,
      border: false,
      items: [
        this.getWatermarkGenerateFieldSet()
      ]
    });
    
    this.frmWatermarkReset = new Ext.FormPanel({
      columnWidth: 0.42,
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'watermark',
        action: 'delete_watermark'
      },
      border: false,
      items: [this.getWatermarkResetFieldSet()]
    });
    
    var pnlWatermark = new Ext.Panel({
      border: false,
      items: [{
        layout: 'column',
        border: false,
        items: [
          this.pnlWatermarkGenerate,
          this.frmWatermarkReset
        ]
      }]
    });
    
    return pnlWatermark;
  },
  
  getWatermarkGenerateFieldSet: function() {
    this.dsTypes = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [['articles', '<?php echo $osC_Language->get('option_articles'); ?>'],
             ['products', '<?php echo $osC_Language->get('option_products'); ?>']]
    });
    
    this.dsImageGroups = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'watermark',
        action: 'load_image_groups'
      },
      reader: new Ext.data.JsonReader ({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,      
      listeners: {
        load: function() {this.cboImageGroups.setValue('<?php echo DEFAULT_IMAGE_GROUP_ID; ?>');},
        scope: this
      }
    });
    
    this.cboTypes = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_type'); ?>',
      mode: 'local',
      valueField: 'id',
      editable: false,
      allowBlank: false,
      hiddenName: 'type',
      displayField: 'text',
      triggerAction: 'all',
      value: 'products',
      store: this.dsTypes,
    });
    
    this.cboImageGroups = new Ext.form.ComboBox({
      store: this.dsImageGroups,
      fieldLabel: '<?php echo $osC_Language->get('field_image_group'); ?>',
      valueField: 'id',
      displayField: 'text',
      hiddenName: 'image_group',
      triggerAction: 'all',
      allowBlank: false,
      editable: false,
      mode: 'remote'
    });
    
    this.btnDeleteWatermarkImage = new Ext.Button({
       text: '<?php echo $osC_Language->get('button_delete'); ?>',
       style: 'margin-top: 64px;', 
       handler: this.deleteWatermarkImage, 
       scope: this
    });
    
    this.frmWatermarkImage = new Ext.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      labelWidth: 115,
      timeout: 600000,
      baseParams: {  
        module: 'watermark',
        action: 'upload_watermark'
      },
      border: false,
      items: [
        {
	        layout:'column',
	        border:false,
	        items:[
		        {
		          columnWidth: 0.78,
		          layout: 'form',
		          defaults: {anchor: '97%'},
		          labelSeparator: ' ',
		          border: false,
		          items: [{xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_watermark'); ?>', name: 'watermark_image', allowBlank: false}]
		        },
		        {
		          columnWidth: 0.22,
		          layout: 'form',
		          style: 'margin: 2px; padding-left: 2px;',
		          border:false,
		          items: [{xtype: 'button', text: TocLanguage.btnUpload, iconCls: 'icon-upload', handler: this.uploadWatermarkImage, scope: this}]
		        }  
	        ]
        },
        {
          layout:'column',
          border:false,
          items: [
            {
	            columnWidth: 0.7,
	            border: false,
	            items: [{xtype: 'panel', id: 'watermark_image', border: false, height: 80, width: 125, style: 'margin: 5px 0 5px 128px; border: 1px solid #DDDDDD;text-align: center;'}]
            },{
              columnWidth: 0.3,
              border: false,
              items: [this.btnDeleteWatermarkImage]
            }
          ]
        }
      ]
    });
    
    this.txtOpacity = new Ext.form.NumberField({
      fieldLabel: '<?php echo $osC_Language->get('field_watermark_opacity'); ?>',
      name: 'watermark_opacity',
      maxValue: 100,
      value: 50,
      allowNegative: false,
      allowBlank: false
    });
    
    this.cboWatermarkPositions = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_watermark_position'); ?>',
      hiddenName: 'watermark_position',
      triggerAction: 'all',
      editable: false,
      displayField: 'text',
      valueField: 'id',
      mode: 'local',
      allowBlank: false,
      store: new Ext.data.SimpleStore({
        fields: ['id', 'text'],
        data: [['0', '<?php echo $osC_Language->get('parameter_left_top'); ?>'],
               ['1', '<?php echo $osC_Language->get('parameter_left_bottom'); ?>'],
               ['2', '<?php echo $osC_Language->get('parameter_right_top'); ?>'],
               ['3', '<?php echo $osC_Language->get('parameter_right_bottom'); ?>']]
      }),
      value: '3'
    });
    
    this.frmWartermarkConfigure = new Ext.FormPanel({
      border: false,
      labelWidth: 115,
      labelSeparator: ' ',
      defaults: {
        anchor: '98%'
      },
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'watermark',
        action: 'process_watermark'
      },
      items: [this.cboTypes, this.cboImageGroups, this.cboWatermarkPositions, this.txtOpacity]
    });
    
    this.btnGenerateWatermark = new Ext.Button({
      text: '<?php echo $osC_Language->get('button_generate'); ?>',
      iconCls: 'refresh',
      handler: this.generateWatermark,
      scope: this
    });
    
    this.fsGenerate = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_watermark_generator'); ?>', 
      labelSeparator: ' ',
      height: 310,
      layout: 'form', 
      defaults: {
        anchor: '97%'
      },
      items:[this.frmWatermarkImage, this.frmWartermarkConfigure],
      buttons: [this.btnGenerateWatermark]
    });
    
    return this.fsGenerate;
  },
  
  getWatermarkResetFieldSet: function() {
    this.cboResetType = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_type'); ?>',
      name: 'type',
      mode: 'local',
      editable: false,
      valueField: 'id',
      allowBlank: false,
      hiddenName: 'type',
      triggerAction: 'all',
      displayField: 'text',
      store: this.dsTypes 
    }); 
    
    this.cboResetImageGroups = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_image_group'); ?>',
      mode: 'remote',
      editable: false,
      valueField: 'id',
      allowBlank: false,
      name: 'image_group',
      displayField: 'text',
      triggerAction: 'all',
      hiddenName: 'image_group',
      store: this.dsImageGroups
    });
   
    this.fsReset = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_watermark_reset'); ?>', 
      labelSeparator: ' ',
      layout: 'form', 
      height: 310,
      style: 'margin-left: 10px',
      defaults: {
        anchor: '94%'
      },
      items:[
        {xtype:'statictextfield', hideLabel: true, value: '<?php echo $osC_Language->get('introduction_watermark_reset');?>', style: 'margin-bottom: 10px'},
        this.cboResetType, 
        this.cboResetImageGroups
      ],
      buttons: [{text: '<?php echo $osC_Language->get('button_reset');?>', iconCls:'back', width: 100, handler: this.resetWatermark, scope: this}]
    });
    
    return this.fsReset;
  },
  
  uploadWatermarkImage: function() {
    this.frmWatermarkImage.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        if (!Ext.isEmpty(action.result.image)) {
          this.imageUploaded = action.result.image;
                    
          var image = '<img src = "' + action.result.image + '" width="80" height="80" />';
                    
          this.frmWatermarkImage.findById('watermark_image').body.update(image);
          this.btnDeleteWatermarkImage.setDisabled(false);
         }
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  },
  
  generateWatermark: function() {
    this.frmWartermarkConfigure.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, 
      scope: this
    });
  },
  
  resetWatermark: function() {
    this.frmWatermarkReset.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  },
  
  deleteWatermarkImage: function() {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {  
        module: 'watermark',
        action: 'delete_watermark_image',
        image_name: this.imageUploaded
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
           this.frmWatermarkImage.findById('watermark_image').body.update('');
           this.btnDeleteWatermarkImage.setDisabled(true);
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  }
});