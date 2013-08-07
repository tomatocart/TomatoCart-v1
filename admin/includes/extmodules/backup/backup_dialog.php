<?php
/*
  $Id: backup_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.backup.BackupDialog = function (config) {
  config = config || {};

  config.id = 'backup-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_backup"); ?>';
  config.layout = 'fit';
  config.width = 480;
  config.autoHeight = true;
  config.iconCls = 'icon-backup-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnBackup,
      handler: function () {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.backup.BackupDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.backup.BackupDialog, Ext.Window, {

  buildForm: function () {
    this.frmBackup = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'backup',
        action: 'back_backup'
      },
      layoutConfig: { labelSeparator: ''},
      autoHeight: true,
      defaults: {anchor: '97%'},
      labelWidth: 160,
      
      items: [
        {
          border: false,
          html: '<?php 
              $compression_array = array(array("id" => "none", "text" => $osC_Language->get("field_compression_none")));
              if ( !osc_empty(CFG_APP_GZIP) && file_exists(CFG_APP_GZIP) ) {
                $compression_array[] = array("id" => "gzip","text" => $osC_Language->get("field_compression_gzip"));
              } 
              if ( !osc_empty(CFG_APP_ZIP) && file_exists(CFG_APP_ZIP) ) {
                $compression_array[] = array("id" => "zip","text" => $osC_Language->get("field_compression_zip"));
              }
              
              echo '<p class="form-info">' . $osC_Language->get("introduction_new_backup") . '</p>';
              echo '<p>' . osc_draw_radio_field("compression", $compression_array, "none", null, "<br />") . '</p>';
            ?>'
        }
      ]
    });
    
    return this.frmBackup;
  },
  
  submitForm: function () {
    this.frmBackup.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.error_info );
        }
      },
      scope: this
    });
  }
});