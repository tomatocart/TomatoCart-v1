<?php
/*
  $Id: import_export_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.import_export.ImportExportDialog = function (config) {
  config = config || {};

  config.id = 'import_export-win';
  config.title = '<?php echo $osC_Language->get("heading_title"); ?>';
  config.layout = 'fit';
  config.width = 700;
  config.height = 390;
  config.iconCls = 'icon-import_export-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  Toc.import_export.ImportExportDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.import_export.ImportExportDialog, Ext.Window, {

  buildForm: function() {
    this.dsFileType = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [
         ['csv', '<?php echo $osC_Language->get('file_type_csv'); ?>'],
         ['xml', '<?php echo $osC_Language->get('file_type_xml'); ?>']
       ]
    });
  
    this.dsCompression = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [
         ['none','<?php echo $osC_Language->get('compression_none'); ?>'],
         ['zip','<?php echo $osC_Language->get('compression_zip'); ?>']
       ]
    });
    
    this.frmCustomersExport = new Ext.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      baseParams: {
        module: 'import_export',
        action: 'export',
        type: 'customers' 
      }, 
      border: false,
      items: [this.getCustomersExportFieldSet()]
    });
    
    this.frmCustomersImport = new Ext.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      baseParams: {
        module: 'import_export',
        action: 'import',
        type: 'customers' 
      },
      border: false,
      items: [this.getCustomersImportFieldSet()]
    });
    
    var pnlCustomers = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_customers'); ?>',
      style: 'padding: 5px 0px 5px 10px',
      layout: 'table',
      border: false,
      items: [this.frmCustomersExport, this.frmCustomersImport]
    });
    
    this.frmProductsExport = new Ext.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      baseParams: {
        module: 'import_export',
        action: 'export',
        type: 'products' 
      }, 
      border: false,
      items: [this.getProductsExportFieldSet()]
    });
    
    this.frmProductsImport = new Ext.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      baseParams: {
        module: 'import_export',
        action: 'import',
        type: 'products' 
      },
      border: false,
      items: [this.getProductsImportFieldSet()]
    });
    
    var pnlProducts = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_products'); ?>',
      style: 'padding: 5px 0px 5px 10px',
      layout: 'table',
      border: false,
      split: true,
      items: [this.frmProductsExport, this.frmProductsImport]
    });
    
    var tabExport = new Ext.TabPanel({
      activeTab: 0,
      layoutOnTabChange: true,
      defaults:{
        autoScroll: true
      },
      items: [pnlCustomers, pnlProducts]
    });
    
    return tabExport;
  },
  
  getCustomersExportFieldSet: function() {
		this.cboCustomersExportFileType = new Ext.form.ComboBox({
      store: this.dsFileType,
      hiddenName: 'file_type',
      valueField: 'id',
      displayField: 'text',
      value: 'csv',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_file_type"); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: function(){
          if(this.cboCustomersExportFileType.getValue() == 'xml'){
            this.txtCustomersExportSeperator.disable();
            this.txtCustomersExportEnclosed.disable();
          } else {
            this.txtCustomersExportSeperator.enable();
            this.txtCustomersExportEnclosed.enable();
          }
        },
        scope: this
      }
    });
    
    this.cboCustomersExportCompression = new Ext.form.ComboBox({
      store: this.dsCompression,
      hiddenName: 'compression',
      valueField: 'id',
      displayField: 'text',
      value: 'none',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_compression"); ?>',
      readOnly: true,
      triggerAction: 'all'
    });
    
    this.txtCustomersExportSeperator = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_seperator'); ?>', 
      name: 'seperator', 
      value: ',', 
      allowBlank: false
    });
    
    this.txtCustomersExportEnclosed = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_enclosed'); ?>', 
      name: 'enclosed', 
      value: '"', 
      allowBlank: false
    });
    
    var fsCustomersExport = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_export'); ?>',
      layoutConfig: { labelSeparator: ''},
      width: 320,
      height: 270,
      labelWidth: 120,
      labelSeparator: ' ',
      defaults: {
        anchor: '97%'
      },
      items: [
        this.cboCustomersExportFileType, 
        this.txtCustomersExportSeperator, 
        this.txtCustomersExportEnclosed, 
        this.cboCustomersExportCompression 
      ],
      buttons: [{
        text: '<?php echo $osC_Language->get("button_export"); ?>',
        handler: function () {
          this.frmCustomersExport.form.submit();
        },
        scope: this
      }]
    });  
    
    return fsCustomersExport;
  }, 
  
  getCustomersImportFieldSet: function() {
  	this.cboCustomersImportFileType = new Ext.form.ComboBox({
      store: this.dsFileType,
      hiddenName: 'file_type',
      valueField: 'id',
      displayField: 'text',
      value: 'csv',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_file_type"); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: function(){
          if(this.cboCustomersImportFileType.getValue() == 'xml'){
            this.txtCustomersImportSeperator.disable();
            this.txtCustomersImportEnclosed.disable();
            this.txtCustomersImportLineLength.disable();
          } else {
            this.txtCustomersImportSeperator.enable();
            this.txtCustomersImportEnclosed.enable();
            this.txtCustomersImportLineLength.enable();
          }
        },
        scope: this
      }
    });
    
    this.cboCustomersImportCompression = new Ext.form.ComboBox({
      store: this.dsCompression,
      hiddenName: 'compression',
      valueField: 'id',
      displayField: 'text',
      value: 'none',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_compression"); ?>',
      readOnly: true,
      triggerAction: 'all'
    });
    
    this.txtCustomersImportSeperator = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_seperator'); ?>', 
      name: 'seperator', 
      value: ',', 
      allowBlank: false
    });
    
    this.txtCustomersImportEnclosed = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_enclosed'); ?>', 
      name: 'enclosed', 
      value: '"', 
      allowBlank: false
    });
    
    this.txtCustomersImportLineLength = new Ext.form.NumberField({
      fieldLabel: '<?php echo $osC_Language->get('field_line_length'); ?>', 
      name: 'line_length', 
      value: 1000, 
      allowBlank: false
    });
    
    var fsCustomersImport = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_import'); ?>',
      layoutConfig: { labelSeparator: ''},
      labelWidth: 120,
      width: 320,
      height: 270,    
      defaults: {
        anchor: '97%'
      },
      items: [
        this.cboCustomersImportFileType, 
        this.txtCustomersImportSeperator, 
        this.txtCustomersImportEnclosed, 
        this.txtCustomersImportLineLength,
        this.cboCustomersImportCompression,
        {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_import_file');?>', buttonText:'...', name: 'files', allowBlank: false}
      ],
      buttons: [{
        text: '<?php echo $osC_Language->get("field_import"); ?>',
        handler: function () {
          this.frmCustomersImport.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function(form, action){
            	Ext.MessageBox.alert('saveSuccess!', action.result.feedback);
            },    
            failure: function(form, action) {
              if(action.failureType != 'client') {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
              }
            },
            scope: this
          });
        },
        scope: this
      }]
    });
    
    return fsCustomersImport;
  },
  
  getProductsExportFieldSet: function() {
  	this.cboProductsExportFileType = new Ext.form.ComboBox({
      store: this.dsFileType,
      hiddenName: 'file_type',
      valueField: 'id',
      displayField: 'text',
      value: 'csv',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_file_type"); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: function(){
          if(this.cboProductsExportFileType.getValue() == 'xml'){
            this.txtProductsExportSeperator.disable();
            this.txtProductsExportEnclosed.disable();
          } else {
            this.txtProductsExportSeperator.enable();
            this.txtProductsExportEnclosed.enable();
          }
        },
        scope: this
      }
    });
    
    this.cboProductsExportCompression = new Ext.form.ComboBox({
      store: this.dsCompression,
      hiddenName: 'compression',
      valueField: 'id',
      displayField: 'text',
      value: 'none',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_compression"); ?>',
      readOnly: true,
      triggerAction: 'all'
    });
    
    this.txtProductsExportSeperator = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_seperator'); ?>', 
      name: 'seperator', 
      value: ',', 
      allowBlank: false
    });
    
    this.txtProductsExportEnclosed = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_enclosed'); ?>', 
      name: 'enclosed', 
      value: '"', 
      allowBlank: false
    });
    
    var fsProductsExport = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('field_export'); ?>',
      layoutConfig: { labelSeparator: ''},
      width: 320,
      height: 270,
      labelWidth: 120,
      defaults: {
        anchor: '97%'
      },
      items: [
        this.cboProductsExportFileType, 
        this.txtProductsExportSeperator, 
        this.txtProductsExportEnclosed,
        this.cboProductsExportCompression
      ],
      buttons: [{
        text: '<?php echo $osC_Language->get("field_export"); ?>',
        handler: function () {
          this.frmProductsExport.form.submit();
        },
        scope: this
      }]
    });
    
    return fsProductsExport;
  },
  
  getProductsImportFieldSet :function() {
  	this.cboProductsImportFileType = new Ext.form.ComboBox({
      store: this.dsFileType,
      hiddenName: 'file_type',
      valueField: 'id',
      displayField: 'text',
      value: 'csv',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_file_type"); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: function(){
          if(this.cboProductsImportFileType.getValue() == 'xml'){
            this.txtProductsImportSeperator.disable();
            this.txtProductsImportEnclosed.disable();
            this.txtProductsImportLineLength.disable();
          } else {
            this.txtProductsImportSeperator.enable();
            this.txtProductsImportEnclosed.enable();
            this.txtProductsImportLineLength.enable();
          }
        },
        scope: this
      }
    });
    
    this.cboProductsImportCompression = new Ext.form.ComboBox({
      store: this.dsCompression,
      hiddenName: 'compression',
      valueField: 'id',
      displayField: 'text',
      value: 'none',
      mode: 'local',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_compression"); ?>',
      readOnly: true,
      triggerAction: 'all'
    });
    
    this.txtProductsImportSeperator = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_seperator'); ?>', 
      name: 'seperator', 
      value: ',', 
      allowBlank: false
    });
    
    this.txtProductsImportEnclosed = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_enclosed'); ?>', 
      name: 'enclosed', 
      value: '"', 
      allowBlank: false
    });
    
    this.txtProductsImportLineLength = new Ext.form.NumberField({
      fieldLabel: '<?php echo $osC_Language->get('field_line_length'); ?>', 
      name: 'line_length', 
      value: 1000, 
      allowBlank: false
    });
    
    var fsProductsImport = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('field_import'); ?>',
      layoutConfig: { labelSeparator: ''},
      labelWidth: 120,
      width: 320,
      height: 270,    
      defaults: {
        anchor: '97%'
      },
      items: [
        this.cboProductsImportFileType, 
        this.txtProductsImportSeperator, 
        this.txtProductsImportEnclosed, 
        this.txtProductsImportLineLength,
        this.cboProductsImportCompression,
        {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_import_file');?>', buttonText:'...', name: 'files', allowBlank: false},
        {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_products_import_image_file');?>', buttonText:'...', name: 'image_zip'}
      ],
      buttons: [{
        text: '<?php echo $osC_Language->get("field_import"); ?>',
        handler: function () {
          this.frmProductsImport.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function(form, action){
            	Ext.MessageBox.alert('saveSuccess!', action.result.feedback);
            },    
            failure: function(form, action) {
              if(action.failureType != 'client') {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
              }
            },
            scope: this
          });
        },
        scope: this
      }]
    });
    
    return fsProductsImport;
  } 

});