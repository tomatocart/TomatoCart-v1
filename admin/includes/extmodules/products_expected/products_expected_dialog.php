<?php
/*
  $Id: products_expected_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products_expected.ProductsExpectedDialog = function (config) {
  config = config || {};
  
  config.id = 'products_expected-dialog-win';
  config.title = '<?php echo $osC_Language->get("table_heading_date_expected"); ?>';
  config.layout = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-products_expected-win';
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
  
  Toc.products_expected.ProductsExpectedDialog.superclass.constructor.call( this, config );
}
Ext.extend(Toc.products_expected.ProductsExpectedDialog, Ext.Window, {
  show: function (id) {
    var productsId = id || null;
    
    this.frmProductsExpected.form.reset();
    this.frmProductsExpected.form.baseParams['products_id'] = productsId;
    
      this.frmProductsExpected.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'products_expected',
          action: 'load_products_expected'
        },
        success: function (form, action) {
          Toc.products_expected.ProductsExpectedDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.MessageBox.alert( TocLanguage.msgErrTitle, action.result.feedback );
        },
        scope: this
      });
  },
  
  buildForm: function () {
    this.frmProductsExpected = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products_expected',
        action: 'save_products_expected'
      },
      autoHeight: true,
      defaults: {anchor: '97%'},
      layoutConfig: {labelSeparator: ''},
      items: [
        {
          xtype: 'datefield',
          fieldLabel: '<?php echo $osC_Language->get("table_heading_date_expected"); ?>',
          name: 'products_date_available',
          format: 'Y-m-d',
          readOnly: true
        }
      ]
    });
    
    return this.frmProductsExpected;
  },
  
  submitForm: function () {
    this.frmProductsExpected.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert( TocLanguage.msgErrTitle, action.result.feedback );
        }
      },
      scope: this
    });
  }
});