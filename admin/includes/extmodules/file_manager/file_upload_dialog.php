<?php
/*
  $Id:file_upload_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.file_manager.FileUploadDialog = function(config) {

  config = config || {};
  
  config.id = 'file_upload_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_upload_file'); ?>';
  config.width = 400;
  config.modal = false;
  config.iconCls = 'icon-templates-win';
  config.items =  this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.file_manager.FileUploadDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.file_manager.FileUploadDialog, Ext.Window, {
  show: function (directory) {
    this.frmUpload.form.reset();  
    this.frmUpload.form.baseParams['directory'] = directory;
    
    Toc.file_manager.FileEditDialog.superclass.show.call(this);
  },  
  
  buildForm: function() {
    this.frmUpload = new Ext.form.FormPanel({
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      style: 'padding: 8px',
      border: false, 
      labelWidth: 60,
      baseParams: {  
        module: 'file_manager',
        action: 'upload_file'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_file');?>', anchor: '97%'
      },
      items: [
        {name: 'file_upload1'},
        {name: 'file_upload2'},
        {name: 'file_upload3'},
        {name: 'file_upload4'},
        {name: 'file_upload5'},
      ]
    });
    
    return this.frmUpload;
  },

  submitForm : function() {
    this.frmUpload.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});