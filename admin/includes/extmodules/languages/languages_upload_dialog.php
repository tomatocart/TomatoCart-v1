<?php
/*
  $Id: languages_add_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.LanguagesUploadDialog = function(config) {

  config = config || {};
  
  config.id = 'languages-upload-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_upload_language'); ?>';
  config.width = 400;
  config.height = 200;
  config.modal = true;
  config.iconCls = 'icon-languages-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnUpload,
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
  
  Toc.languages.LanguagesUploadDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.LanguagesUploadDialog, Ext.Window, {
  
  buildForm: function() {
    this.frmLanguage = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      baseParams: {  
        module: 'languages',
        action: 'upload_language'
      }, 
      border: false,
      layout: 'form',
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 100,
      items: [ 
        {
          xtype: 'fileuploadfield', 
          width: '250', 
          fieldLabel: '<?php echo $osC_Language->get('field_language_zip_file'); ?>', 
          name: 'upload_file',
          allowBlank: false
        },
        {
          xtype: 'statictextfield', 
          border: false, 
          encodeHtml: false,
          hideLabel: true,
          value:'<?php echo $osC_Language->get('introduction_upload_language'); ?>'
        }
      ]
    });
    
    return this.frmLanguage;
  },

  submitForm : function() {
    this.frmLanguage.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
				if (action.result.success) {
					this.fireEvent('saveSuccess', action.result.feedback);
					this.close();
					window.location.reload();  
				}else {
					Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				}
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