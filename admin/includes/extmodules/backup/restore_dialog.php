<?php
/*
  $Id: restore_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.backup.RestoreDialog = function (config) {

  config = config || {};
  
  config.id = 'backup-restore-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_restore_local_file"); ?>';
  config.layout = 'fit';
  config.width = 480;
  config.autoHeight = true;
  config.iconCls = 'icon-backup-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text : '<?php echo $osC_Language->get('button_restore'); ?>',
      handler : function () {
        this.submitForm();
      },
      scope : this
    },
    {
      text : TocLanguage.btnClose,
      handler : function () {
        this.close();
      },
      scope : this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.backup.RestoreDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.backup.RestoreDialog, Ext.Window, {

  buildForm : function () {
    this.frmRestore = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'backup',
        action: 'restore_local'
      },      
      fileUpload: true,
      autoHeight: true,
      labelAlign: 'top',
      layoutConfig : {labelSeparator : ''},
      defaults: {anchor: '97%'},
      labelWidth: 160,
      items : [
        {
          xtype: 'fileuploadfield',
          fieldLabel: '<?php echo $osC_Language->get("introduction_restore_local_file"); ?>',
          name: 'sql_file'
        }
      ]
    });
    
    return this.frmRestore;
  },
  
  submitForm : function () {
    this.frmRestore.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success : function (form, action) {
        alert(action.result.feedback);
        window.location = "<?php echo osc_href_link_admin(FILENAME_DEFAULT, 'login&action=logoff'); ?>";
      },
      failure : function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope : this
    });
  }
});