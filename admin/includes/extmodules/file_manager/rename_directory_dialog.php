<?php
/*
  $Id:rename_directory_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.file_manager.RenameDirectoryDialog = function(config) {

  config = config || {};
  
  config.id = 'rename_directory_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_rename_directory'); ?>';
  config.width = 350;
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
  
  Toc.file_manager.RenameDirectoryDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.file_manager.RenameDirectoryDialog, Ext.Window, {
  show: function (directory) {
    this.frmDirectory.form.reset();  
    this.frmDirectory.form.baseParams['directory'] = directory;
    
    Toc.file_manager.FileEditDialog.superclass.show.call(this);
  },  
  
  buildForm: function() {
    this.frmDirectory = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'file_manager',
        action: 'rename_directory'
      }, 
      labelAlign: 'top', 
      style: 'padding: 6px',
      border: false,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_new_directory_name');?>', name:'new_directory', width: 300},
      ]
    });
    
    return this.frmDirectory;
  },

  submitForm : function() {
    this.frmDirectory.form.submit({
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