<?php
/*
  $Id: file_upload_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.email.FileUploadDialog = function(config) {

  config = config || {};
  
  config.id = 'upload_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_attachment_upload'); ?>';
  config.width = 400;
  config.modal = true;
  config.items =  this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: this.submitForm,
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

  this.addEvents({'saveSuccess': true});  
  
  Toc.email.FileUploadDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.email.FileUploadDialog, Ext.Window, {

  buildForm: function() {
    this.frmUpload = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'email',
        action: 'upload_attachment'
      }, 
      fileUpload: true,
      items: [
        {xtype: 'statictextfield', hideLabel: true, value: '<?php echo $osC_Language->get('introduction_upload_file');?> '},
        {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_file');?>', name: 'file_upload', anchor: '97%', allowBlank: false}
      ]
    });
    
    return this.frmUpload;
  },

  submitForm: function() {
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