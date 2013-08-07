<?php
/*
  $Id: attachments_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.AttachmentsDialog = function(config) {
  config = config || {};
  
  config.id = 'products_attachments_dialog-win';
  config.width = 450;
  config.height = 300;
  config.iconCls = 'icon-products_attachments-win';
  
  config.items = this.buildForm();
  
  config.buttons = [{
    text: TocLanguage.btnSave,
    handler: function() {
      this.submitForm();
    },
    scope: this
  }, {
    text: TocLanguage.btnClose,
    handler: function() { 
      this.close();
    },
    scope: this
  }];
  
  Toc.products.AttachmentsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.AttachmentsDialog, Ext.Window, {
  show: function (id) {
    var attachmentsId = id || null;
    this.frmAttachment.form.baseParams['attachments_id'] = attachmentsId;
    
    if (attachmentsId > 0) {
      this.frmAttachment.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'products',
          action: 'load_attachment'
        },
        success: function (form, action) {
          Toc.products.AttachmentsDialog.superclass.show.call(this);
          var htmFile = '<a href="' + action.result.attachments_file + '" style="padding: 2px;">' + action.result.data['filename'] + '</a>';
          
          this.pnlAttachmentFile.findById('attachments_file').body.update(htmFile);
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.products.AttachmentsDialog.superclass.show.call(this);
    }
  },
  
  getAttachmentFilePanel: function() {
    this.pnlAttachmentFile = new Ext.Panel({
      border: false,
      layout: 'form',
      defaults: {
        anchor: '96%'
      },
      items: [{
        xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_attachments_file'); ?>',name: 'attachments_file_name'
      },{
        xtype: 'panel',
        border: false,
        id: 'attachments_file',
        style: 'margin-left: 115px; text-decoration: underline'
      }]
    });
    
    return this.pnlAttachmentFile;
  },
  
  getAttachmentDescriptionPanel: function() {
    this.tabLanguage = new Ext.TabPanel({
      activeTab: 0,
      enableTabScroll: true,
      deferredRender: false,
      border: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'var pnlLang' . $l['code'] . ' = new Ext.Panel({
          labelWidth: 100,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          autoHeight: true,
          labelSeparator: \' \',
          defaults: {
            anchor: \'96%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_attachments_name') . '\', name: \'attachments_name[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_attachments_description') . '\', name: \'attachments_description[' . $l['id'] . ']\', height: 120}
          ]
        });
        
        this.tabLanguage.add(pnlLang' . $l['code'] . ');
        ';
      }
    ?>
    
    return this.tabLanguage;
  },

  buildForm: function() {
    this.frmAttachment = new Ext.form.FormPanel({
      border: false,
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      labelWidth: 100,
      baseParams: {  
        module: 'products',
        action: 'save_attachment'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.getAttachmentFilePanel(),
        this.getAttachmentDescriptionPanel()
      ]
    });
    
    return this.frmAttachment;
  },
  
  submitForm: function() {
    this.frmAttachment.form.submit({
      success:function(form, action) {
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