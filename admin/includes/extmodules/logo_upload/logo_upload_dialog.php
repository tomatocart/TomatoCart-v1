<?php
/*
  $Id: logo_upload_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.logo_upload.LogoUploadDialog = function(config) {

  config = config || {};
  
  config.id = 'logo_upload-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 400;
  config.height = 250;
  config.iconCls = 'icon-logo_upload-win';
  config.layout = 'fit';
  config.items = this.buildForm();
   
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_save'); ?>',
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

  Toc.logo_upload.LogoUploadDialog.superclass.constructor.call(this,config); 
};

Ext.extend(Toc.logo_upload.LogoUploadDialog, Ext.Window, {

  show: function () {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'logo_upload',
        action: 'get_logo'
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.frmUpload.findById('logo-image').body.update(result.image);
        }
      },
      scope: this
    });

    Toc.logo_upload.LogoUploadDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.frmUpload = new Ext.form.FormPanel({
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      layout: 'border',
      baseParams: {  
        module: 'logo_upload',
        action : 'save_logo'
      }, 
      items: [
        {
          xtype: 'panel',
          region: 'north',
          layout: 'form',
          autoHeight: true,
          border: false,
          items: [
            {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_logo_image'); ?>', name: 'logo_image', anchor: '97%', labelSeparator: ' '}
          ]
        },
        {
          xtype: 'panel',
          region: 'center',
          border: false,
          id: 'logo-image',
          style: 'text-align: center'
        }
      ]
    });
         
    return this.frmUpload; 
  },
  
  submitForm : function() {
    this.frmUpload.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         image = '<img src ="' + action.result.image + '" width="' + action.result.width + '" height="' + action.result.height + '" style="padding: 10px" />';
         this.frmUpload.findById('logo-image').body.update(image);
         this.doLayout();
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