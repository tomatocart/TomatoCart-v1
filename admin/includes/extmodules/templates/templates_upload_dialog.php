<?php
/*
  $Id:templates_upload_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.templates.TemplatesUplaodDialog = function(config) {

  config = config || {};
  
  config.id = 'templates_uploadDialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
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
  
  Toc.templates.TemplatesUplaodDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.templates.TemplatesUplaodDialog, Ext.Window, {
      
  buildForm: function() {
    this.frmTemplates = new Ext.form.FormPanel({
      autoHeight: true,
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'templates',
        action: 'upload_template'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      labelAlign: 'top',  
      items: [
        {xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_upload_template'); ?>', name: 'template_file', anchor: '97%'}
      ]
    });
    
    return this.frmTemplates;
  },

  submitForm : function() {
    this.frmTemplates.form.submit({
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