<?php
/*
  $Id: articles_categories_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.articles_categories.ArticlesCategoriesDialog = function(config) {
  
  config = config || {};
  
  config.id = 'articles_categories-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_category'); ?>';
  config.layout = 'fit';
  config.width = 440;
  config.height = 350;
  config.modal = true;
  config.iconCls = 'icon-articles_categories-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text:TocLanguage.btnSave,
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
  
  Toc.articles_categories.ArticlesCategoriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.articles_categories.ArticlesCategoriesDialog, Ext.Window, {
  
  show: function(id) {
    var categoriesId = id || null;
    
    this.frmArticlesCategory.form.reset();  
    this.frmArticlesCategory.form.baseParams['articles_categories_id'] = categoriesId;
    
    if (categoriesId > 0) {
      this.frmArticlesCategory.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_articles_categories'
        },
        success: function(form, action) {
          Toc.articles_categories.ArticlesCategoriesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this       
      });
    } else {
      Toc.articles_categories.ArticlesCategoriesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.pnlGeneral = new Toc.articles_categories.GeneralPanel();
    this.pnlMetaInfo = new Toc.articles_categories.MetaInfoPanel();
    
    tabArticlesCategories = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlGeneral,
        this.pnlMetaInfo  
      ]
    });
    
    this.frmArticlesCategory = new Ext.form.FormPanel({
      id: 'form-categories',
      layout: 'fit',
      fileUpload: true,
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'articles_categories',
        action: 'save_articles_category'
      },
      scope: this,
      items: tabArticlesCategories
    });
    
    return this.frmArticlesCategory;
  },

  submitForm : function() {
    this.frmArticlesCategory.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});