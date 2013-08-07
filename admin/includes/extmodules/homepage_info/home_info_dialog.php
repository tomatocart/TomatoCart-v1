<?php
/*
  $Id: home_info_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.homepage_info.HomepageInfoDialog = function (config) {
  config = config || {};
  
  config.id = "homepage_info-win";
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'fit';
  config.width = 870;
  config.height = 450;
  config.iconCls = 'icon-homepage_info-win';
  config.border = false;
  config.items = this.buildForm();
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];
  
  Toc.homepage_info.HomepageInfoDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.homepage_info.HomepageInfoDialog, Ext.Window, {
  show: function() {
    this.frmPagehomeInfo.load({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'homepage_info',
        action: 'load_info'
      },
      scope: this
    }, this);
    
    Toc.homepage_info.HomepageInfoDialog.superclass.show.call(this);
  },

  buildForm: function () {
    var pnlMetaInfo = new Toc.homepage_info.MetaInfoPanel();
    var pnlHomepageInfo = new Toc.homepage_info.HomepageInfoPanel();
    
    var tabProduct = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        pnlHomepageInfo,
        pnlMetaInfo
      ]
    }); 
    
    this.frmPagehomeInfo = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      layout: 'fit',
      labelWidth: 120,
      border: false,
      baseParams: {  
        module: 'homepage_info',
        action : 'save_info'
      },
      items: tabProduct
    });
    
    return this.frmPagehomeInfo;
  },
  
  submitForm: function() {
    <?php if (USE_WYSIWYG_TINYMCE_EDITOR == '1') { ?>
      tinyMCE.triggerSave();
    <?php } ?>
  
    this.frmPagehomeInfo.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: action.result.feedback});
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