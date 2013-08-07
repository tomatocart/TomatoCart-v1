<?php
/*
  $Id: products_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.ProductDialog = function(config) {
  config = config || {};
  
  config.id = 'products-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_product'); ?>';
  config.layout = 'fit';
  config.width = 870;
  config.height = 540;
  config.modal = true;
  config.iconCls = 'icon-products-win';
  config.productsId = config.products_id || null;
  this.owner = config.owner || null;
  this.flagContinueEdit = false;
  
  config.items = this.buildForm(config.productsId);
  
  config.buttons = [
    {
      text: TocLanguage.btnSaveAndContinue,
      handler: function(){
        this.flagContinueEdit = true;
        
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnSubmit,
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
    
  this.addEvents({'saveSuccess': true});      

  Toc.products.ProductDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.ProductDialog, Ext.Window, {
  buildForm: function(productsId) {
    this.pnlData = new Toc.products.DataPanel();
    this.pnlVariants = new Toc.products.VariantsPanel({owner: this.owner, productsId: productsId, dlgProducts: this}); 
    this.pnlXsellProducts = new Toc.products.XsellProductsGrid({productsId: productsId});
    this.pnlAttributes = new Toc.products.AttributesPanel({productsId: productsId});
    this.pnlAttachments = new Toc.products.AttachmentsPanel({productsId: productsId, owner: this.owner});
    this.pnlCustomizations = new Toc.products.CustomizationsPanel({productsId: productsId, owner: this.owner});
    this.pnlImages = new Toc.products.ImagesPanel({productsId: productsId}); 
    
    this.pnlData.on('producttypechange', this.pnlVariants.onProductTypeChange, this.pnlVariants);
    this.pnlVariants.on('variantschange', this.pnlData.onVariantsChange, this.pnlData);
    
    this.pnlAccessories = new Toc.products.AccessoriesPanel({productsId: productsId});
    
    tabProduct = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        new Toc.products.GeneralPanel(), 
        this.pnlMeta = new Toc.products.MetaPanel(),
        this.pnlData,
        this.pnlCategories = new Toc.products.CategoriesPanel({productsId: productsId}),
        this.pnlImages,
        this.pnlVariants, 
        this.pnlAttributes, 
        this.pnlXsellProducts,
        this.pnlCustomizations,
        this.pnlAttachments,
        this.pnlAccessories
      ]
    }); 

    this.frmProduct = new Ext.form.FormPanel({
      layout: 'fit',
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      labelWidth: 120,
      baseParams: {  
        module: 'products',
        action: 'save_product'
      },
      items: tabProduct
    });

    return this.frmProduct;
  },
    
  show: function(categoryId) {
    this.frmProduct.form.reset();  

    this.pnlImages.grdImages.store.load();
    this.pnlVariants.grdVariants.store.load();
    
    if (this.productsId > 0) {
      this.frmProduct.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_product',
          products_id: this.productsId
        },
        success: function(form, action) {
          this.pnlData.onPriceNetChange(); 
          this.pnlData.updateCboTaxClass(action.result.data.products_type);
          this.pnlData.loadExtraOptionTab(action.result.data);   
          this.pnlCategories.setCategories(action.result.data.categories_id);
          this.pnlVariants.onProductTypeChange(action.result.data.products_type);
          this.pnlAttributes.setAttributesGroupsId(action.result.data.products_attributes_groups_id);
          
          Toc.products.ProductDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.products.ProductDialog.superclass.show.call(this);
    }
    
    if (!Ext.isEmpty(categoryId) && (categoryId > 0)) {
      this.pnlCategories.setCategories(categoryId);
    }
  },

  submitForm: function() {
    var params = {
      action: 'save_product',
      accessories_ids: this.pnlAccessories.getAccessoriesIds(),
      xsell_ids: this.pnlXsellProducts.getXsellProductIds(),
      products_variants: this.pnlVariants.getVariants(), 
      products_id: this.productsId,
      attachments_ids: this.pnlAttachments.getAttachmentsIDs(),
      categories_id: this.pnlCategories.getCategories(),
      customization_fields: this.pnlCustomizations.getCustomizations()
    };
    
    <?php if (USE_WYSIWYG_TINYMCE_EDITOR == '1') { ?>
      tinyMCE.triggerSave();
    <?php } ?>
    
    if (this.productsId > 0) {
      params.products_type = this.pnlData.getProductsType();
    }
    
    var status = this.pnlVariants.checkStatus();
    
    if (status == true) { 
      this.frmProduct.form.submit({
        params: params,
        waitMsg: TocLanguage.formSubmitWaitMsg,
        success:function(form, action){
          this.fireEvent('saveSuccess', action.result.feedback);

          if (this.flagContinueEdit == true) {
            this.productsId = action.result.productsId;
            this.frmProduct.form.baseParams['products_id'] = this.productsId;
            this.pnlImages.grdImages.getStore().baseParams['products_id'] = this.productsId;
            this.pnlImages.pnlImagesUpload.uploader.setUrl(Toc.CONF.CONN_URL + '?module=products&action=upload_image&products_id=' + this.productsId);
            this.pnlImages.productsId = this.productsId;
            
            this.pnlData.cboProductsType.disable();
            this.pnlCustomizations.getStore().reload();
          
            var onDsImagesLoad = function () {
              this.pnlVariants.pnlVariantDataContainer.removeAll(true);
              this.pnlVariants.pnlVariantDataContainer.doLayout();
              this.pnlVariants.grdVariants.getStore().baseParams['products_id'] = this.productsId;
              this.pnlVariants.grdVariants.getStore().reload();       

              this.pnlImages.grdImages.getStore().removeListener('load', onDsImagesLoad, this);
            }
            
            this.pnlImages.grdImages.getStore().on('load', onDsImagesLoad, this);
            this.pnlImages.grdImages.getStore().reload();

            this.flagContinueEdit = false;  
            
            //Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, action.result.feedback);
            
            Ext.each(action.result.urls, function(url) {
              this.pnlMeta.txtProductUrl[url.languages_id].setValue(url.url);
            }, this);
          } else {
            this.close();
          }
        },    
        failure: function(form, action) {
          if(action.failureType != 'client') {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
          }
        },
        scope: this
      });  
    } else {
      Ext.MessageBox.alert(TocLanguage.msgErrTitle, '<?php echo $osC_Language->get('msg_select_default_variants_records'); ?>');
    }
  }
});