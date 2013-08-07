<?php
/*
  $Id:file_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.file_manager.FileEditDialog = function(config) {

  config = config || {};
  
  config.id = 'file_edit_dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 600;
  config.height = 450;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-file_manager-win';
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
  
  this.current_directory = null;
  
  Toc.file_manager.FileEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.file_manager.FileEditDialog, Ext.Window, {
  show: function (directory, name) {
    var fileName = name || null;
    
    this.frmFileEdit.form.reset();  
    this.frmFileEdit.form.baseParams['file_name'] = fileName;
    this.frmFileEdit.form.baseParams['directory'] = directory;
    
    this.stxDirectory.setValue(directory);

    if (fileName != null) {
      this.txtFilename.setValue(fileName);
      
      this.frmFileEdit.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'file_manager',
          action: 'load_file'
        },
        success: function() {
          Toc.file_manager.FileEditDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.file_manager.FileEditDialog.superclass.show.call(this);
    }
  },    
  
  buildForm: function() {
    this.frmFileEdit = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      labelWidth: 150,
      defaults:{
        anchor:'97%'
      },
      baseParams: {  
        module: 'file_manager',
        action: 'save_file'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.txtFilename = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_file_name');?>'}),
        this.stxDirectory = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get('field_directory');?>'}),
        {xtype:'textarea', fieldLabel:'<?php echo $osC_Language->get('field_file_contents');?>', name:'content', height: 300}
      ]
    });
    
    return this.frmFileEdit;
  },

  submitForm : function() {
    this.frmFileEdit.form.baseParams['file_name'] = this.txtFilename.getValue();
    
    this.frmFileEdit.form.submit({
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