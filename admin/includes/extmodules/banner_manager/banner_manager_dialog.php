<?php
/*
  $Id: banner_manager_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.banner_manager.BannerManagerDialog = function(config) {

  config = config || {};
  
  config.id = 'banner_manager_dialog-win';
  config.title = '<?php echo $osC_Language->get('banner_manager_new_dialog'); ?>';
  config.layout = 'fit';
  config.width = 500;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-banner_manager-win';
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
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
  
  Toc.banner_manager.BannerManagerDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.banner_manager.BannerManagerDialog, Ext.Window, {
  show: function(bannersId) {
    this.bannersId = bannersId || null;

    this.frmBannerManager.form.reset();  
    this.frmBannerManager.form.baseParams['banner_id'] = this.bannersId;  
    
    this.txtHtml.disable();
    
    if (this.bannersId > 0) {
      this.frmBannerManager.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'banner_manager',
          action: 'load_banner'
        },
        success: function(form, action) {
          this.cboGroup.setValue(action.result.data.banners_group);

          if(action.result.data.banners_image){
            var img = '<img src ="../images/' + action.result.data.banners_image + '" style="margin-left: 145px;" width="120" height="80" />';  
            this.frmBannerManager.findById('banner_image').body.update(img);
            
            this.rdbTypeImage.setValue(true);
            this.rdbTypeHtml.setValue(false);
            
            this.txtHtml.disable();
            this.fileImage.enable();
          }  else {
            this.rdbTypeHtml.setValue(true);
            this.rdbTypeImage.setValue(false);
            
            this.fileImage.disable();
            this.txtHtml.enable();
          }
          
          Toc.banner_manager.BannerManagerDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.banner_manager.BannerManagerDialog.superclass.show.call(this);
    }
    
  },
  
  buildForm: function() {
    this.cboGroup = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_group'); ?>',
      store: new Ext.data.Store({
        url:Toc.CONF.CONN_URL,
        baseParams: {
          module: 'banner_manager',
          action: 'list_groups'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          fields: [
          'text'
          ]
        }),
        autoLoad: true
      }), 
      width: 100,
      name: 'group', 
      displayField: 'text', 
      valueField: 'text',
      triggerAction: 'all', 
      editable: false,
      forceSelection: true
    });
    
    this.rdbTypeImage = new Ext.form.Radio({
      fieldLabel: '<?php echo $osC_Language->get('field_banner_type'); ?>',
      name: 'banner_type', 
      type: 'image',
      inputValue: 'image',
      checked: true,
      boxLabel: '<?php echo $osC_Language->get('field_image'); ?>',
      listeners: {
      check: function (checkbox, checked) {
        if(checked){
          this.onBannerTypeCheck(checkbox.type);
        }
      },
      scope: this
      }
    });
    
    this.rdbTypeHtml = new Ext.form.Radio({
      name: 'banner_type',
      type: 'text',
      hideLabel: true,
      inputValue: 'html',
      checked: false,
      boxLabel: '<?php echo $osC_Language->get('field_html'); ?>',
      listeners: {
      check: function(checkbox, checked) {
        if(checked){
          this.onBannerTypeCheck(checkbox.type);
        }
      },
      scope: this
      }
    });
    
    this.frmBannerManager = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'banner_manager'
      },
      fileUpload: true,
      autoHeight: true,
      labelSeparator: ' ',
      labelWidth: 130,
      style: 'padding: 8px',
      border: false,
      defaults: {
        anchor: '97%'
      },
      items: [
        {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('field_title'); ?>', name: 'title', allowBlank: false},
        {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('field_url'); ?>', name: 'url', allowBlank: false},
        {
         layout: 'column',
         border: false,
         xtype: 'panel',
         items: [
           {
             layout: 'form',
             border: false,
             labelSeparator: ' ',
             items: [
               this.cboGroup
             ]
           },
           {
             layout: 'form',
             border: false,
             labelSeparator: ' ',
             labelWidth: 80,
             items: [
               this.txtGroupNew = new Ext.form.TextField({xtype: 'textfield', name: 'group_new', width: 100, fieldLabel: '<?php echo $osC_Language->get('field_group_new'); ?>'})
             ]
           }
         ]
       },
        {
         layout: 'column',
         border: false,
         items: [
           {
             layout: 'form',
             border: false,
             labelSeparator: ' ',
             width: 220,
             items: [
               this.rdbTypeImage
             ]
           },
           {
             layout: 'form',
             border: false,
             width: 100,
             items: [
               this.rdbTypeHtml
             ]
           }
         ]
       },
       this.fileImage = new Ext.form.FileUploadField({fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', name: 'image'}),
       {xtype: 'panel', name: 'banner_image', id: 'banner_image', border: false},
       this.txtHtml = new Ext.form.TextArea({fieldLabel: '<?php echo $osC_Language->get('field_html_text'); ?>', name: 'html_text'}),
       {
         xtype: 'datefield', 
         fieldLabel: '<?php echo $osC_Language->get('field_scheduled_date'); ?>', 
         name: 'date_scheduled',  
         format: 'Y-m-d',
         readOnly: true
       },
        {
         xtype: 'datefield',
         fieldLabel: '<?php echo $osC_Language->get('field_expiry_date'); ?>',
         name: 'expires_date',
         format: 'Y-m-d',
         readOnly: true
        },
        {
          xtype:'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_maximum_impressions'); ?>', 
          name: 'expires_impressions'
        },
        {
          xtype:'checkbox', 
          fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', 
          name: 'status'
        }
     ]
   });  
    
   return this.frmBannerManager;
  },
  
  submitForm : function() {
    this.frmBannerManager.form.submit({
      params: {
        'action' : 'save_banner',
        'banners_id': this.bannersId
      },
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
  },
  
  onBannerTypeCheck: function (type) {
    if(type == 'text'){
      this.txtHtml.enable();
      this.fileImage.disable();
    } else {
      this.txtHtml.disable();
      this.fileImage.enable();
    }
  }
});