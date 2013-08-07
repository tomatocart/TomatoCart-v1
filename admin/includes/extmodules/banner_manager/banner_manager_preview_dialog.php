<?php
/*
  $Id: banner_manager_preview_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.banner_manager.BannerManagerPreviewDialog = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('banner_manager_preview_dialog'); ?>';
  config.id = 'banner_manager_preview_dialog-win';
  config.width = 440;
  config.height = 280;
  config.modal = true;
  config.iconCls = 'icon-banner_manager-win';
  config.layout = 'fit';
  config.items = this.buildForm();  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: this.close,
      scope:this
    }
  ];
  
  Toc.banner_manager.BannerManagerPreviewDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.banner_manager.BannerManagerPreviewDialog, Ext.Window, {
  show: function(bannerId) {
    this.frmBannerManager.load({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'banner_manager',
        action: 'load_banner',
        banner_id: bannerId
      },
      success: function(form, action) {
        this.setTitle(action.result.data.title);
        
        if(action.result.data.banners_image){
          var html = '<p align="center"><img src ="../images/' + action.result.data.banners_image + '" style = "width: 360px; height: 180px" /></p>';
        } else {
          var html = '<p>' + action.result.data.html_text + '</p>';
        }
        
        this.pnlPreviewImage.body.update(html);          
        
        Toc.banner_manager.BannerManagerDialog.superclass.show.call(this);
      },
      failure: function() {
        Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
      },
      scope: this       
    });
    
  },
  
  buildForm: function() {
    this.frmBannerManager = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'banner_manager'
      },
      border: false,
      items: [
        this.pnlPreviewImage = new Ext.Panel({ border: false })
      ]
    });
    
    return this.frmBannerManager;
  }
});