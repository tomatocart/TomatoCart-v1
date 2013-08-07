<?php
/*
  $Id: categories_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.categories.CategoriesDialog = function (config) {
  config = config || {};
  
  config.id = 'categories-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_category"); ?>';
  config.layout = 'fit';
  config.width = 520;
  config.height = 380;
  config.modal = true;
  config.iconCls = 'icon-categories-win';
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
  
  Toc.categories.CategoriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.categories.CategoriesDialog, Ext.Window, {
  
  show: function (id, pId) {
    var categoriesId = id || null;
    var parentId = pId || 0;
    
    this.frmCategories.form.reset();
    this.frmCategories.form.baseParams['categories_id'] = categoriesId;
    
    if (categoriesId > 0) {
      this.frmCategories.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_category'
        },
        success: function (form, action) {
          var ratings = action.result.data.ratings;
          var records = new Array();
          this.pnlRatings.getStore().each(function(record) { 
            if (ratings.contains(record.id))   
              records.push(record);   
          });   
          this.pnlRatings.getSelectionModel().selectRecords(records, true);
          
          this.pnlGeneral.cboParentCategories.disable();
          var img = action.result.data.categories_image;
          
          if (img) {
            var html = '<img src ="../images/categories/' + img + '"  style = "margin-left: 170px; width: 70px; height:70px" /><br/><span style = "padding-left: 170px;">/images/categories/' + img + '</span>';
            this.frmCategories.findById('categories_image_panel').body.update(html);
          }
          
          Toc.categories.CategoriesDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      },
        this
      );
    } else {
      Toc.categories.CategoriesDialog.superclass.show.call(this);
    }
    
    this.pnlGeneral.cboParentCategories.getStore().on('load', function() {
      this.pnlGeneral.cboParentCategories.setValue(parentId);
    }, this);
  },
  
  buildForm: function () {
    this.pnlGeneral = new Toc.categories.GeneralPanel();
    this.pnlMetaInfo = new Toc.categories.MetaInfoPanel();
    this.pnlRatings = new Toc.categories.RatingsGridPanel();
    
    tabCategories = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlGeneral,
        this.pnlMetaInfo,
        this.pnlRatings   
      ]
    });
    
    this.frmCategories = new Ext.form.FormPanel({
      id: 'form-categories',
      layout: 'fit',
      fileUpload: true,
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'categories',
        action: 'save_category'
      },
      scope: this,
      items: tabCategories
    });
    
    return this.frmCategories; 
  },
  
  submitForm: function () {
    this.frmCategories.form.baseParams['ratings'] = this.pnlRatings.getSelectionModel().selections.keys;
    
    var status = this.pnlGeneral.findById('status').findByType('radio');
    status = status[0].getGroupValue();
    
    if(status == 0) {
      this.frmCategories.form.baseParams['product_flag'] = 1;
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDisableProducts, 
        function (btn) {
          if (btn == 'no') {
            this.frmCategories.form.baseParams['product_flag'] = 0;

				    this.frmCategories.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          } else{
				    this.frmCategories.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback, action.result.categories_id, action.result.text);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          }
        }, 
        this
      );       
    } else {
	    this.frmCategories.form.submit({
	      waitMsg: TocLanguage.formSubmitWaitMsg,
	      success: function (form, action) {
	        this.fireEvent('saveSuccess', action.result.feedback, action.result.categories_id, action.result.text);
	        this.close();
	      },
	      failure: function (form, action) {
	        if (action.failureType != 'client') {
	          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
	        }
	      },
	      scope: this
	    });
    }
  }
});