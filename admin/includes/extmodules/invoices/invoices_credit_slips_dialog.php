<?php
/*
  $Id: invoices_credit_slip_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.invoices.InvoicesCreditSlipsDialog = function (config) {
  config = config || {};
  
  config.id = 'invoices_credit_slips-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_credit_slip"); ?>';
  config.layout = 'border';
  config.width = 600;
  config.height = 500;
  config.modal = true;
  config.iconCls = 'icon-invoices-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_create_credit_slip'); ?>',
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
  
  Toc.invoices.InvoicesCreditSlipsDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.invoices.InvoicesCreditSlipsDialog, Ext.Window, {

  show: function () {
    var record = this.record;
    this.grdProducts.getStore().baseParams['orders_id'] = record.get('orders_id');
    this.frmCreditSlip.form.baseParams['orders_id'] = record.get('orders_id');
    
    Toc.invoices.InvoicesCreditSlipsDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    this.grdProducts = new Ext.grid.EditorGridPanel({
      border: false,
      region: 'center',
      clicksToEdit: 1,
      viewConfig: {emptyText: TocLanguage.gridNoRecords},
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'invoices',
          action: 'get_available_products'        
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'orders_products_id'
        }, [
          'orders_products_id',
          'products_name',
          {name: 'products_price', type: 'float'},
          'products_format_price',
          'quantity_available',
          'return_quantity'
        ]),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'credit-slips-products-name', header: '<?php echo $osC_Language->get('table_heading_products'); ?>', dataIndex: 'products_name'},
        {header: '<?php echo $osC_Language->get('table_heading_price'); ?>', dataIndex: 'products_format_price', align: 'center'},
        {header: '<?php echo $osC_Language->get('table_heading_quantity_available'); ?>', dataIndex: 'quantity_available', align: 'center'},
        {header: '<?php echo $osC_Language->get('table_heading_return_quantity'); ?>', dataIndex: 'return_quantity', editor: new Ext.form.NumberField({allowDecimals: false, allowNegative: false, allowBlank: false}), align: 'center'}
      ]),
      autoExpandColumn: 'credit-slips-products-name',
      listeners: {
        validateedit: function(e) {
          var return_quantity = e.value;
          var qty_in_invoice = e.record.get('quantity_available');
          
          if (return_quantity > qty_in_invoice) {
            alert('<?php echo $osC_Language->get('error_return_quantity'); ?>');
            
            return false;
          }
        },
        afteredit: function(e) {
          var return_quantity = e.record.get('return_quantity');
          var qty_in_invoice = e.record.get('quantity_available');

          var total = 0;
          this.grdProducts.getStore().each(function(record) {
            total = total + record.get('products_price') * record.get('return_quantity');
          });  
          this.txtSubTotal.setValue(total);
          
          e.grid.store.commitChanges();
        },
        scope: this
      }
    });
    
    this.frmCreditSlip = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'invoices',
        action: 'create_credit_slip'
      },
      region: 'south',
      height: 240,
      layoutConfig: {labelSeparator: ''},
      defaults: {anchor: '96%'},
      labelWidth: 160,
      items: [
        {border: false, html: '<p class="form-info"><?php echo $osC_Language->get("field_credit_slip_title"); ?></p>'},
        this.txtSubTotal = new Ext.form.NumberField({xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_sub_total"); ?>', name: 'sub_total', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0}),
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_shipping_fee"); ?>', name: 'shipping_fee', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_handling"); ?>', name: 'handling', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get("field_restock_product_quantity"); ?>', name: 'restock_quantity', anchor: ''},
        {xtype: 'textarea',fieldLabel: '<?php echo $osC_Language->get("field_comment"); ?>', name: 'comments'}
      ]
    });
    
    return [this.grdProducts, this.frmCreditSlip];
  },
  
  submitForm: function () {
    var quantity = [];
    
    this.grdProducts.getStore().each(function(record) {
      var return_quantity = record.get('return_quantity');
      
      if (return_quantity > 0) {
        quantity.push(record.get('orders_products_id') + ':' + return_quantity);
      }
    });
    
    if (quantity.length == 0) {
      alert('<?php echo $osC_Language->get('error_at_least_return_one_product'); ?>');
      
      return;
    }
      
    this.frmCreditSlip.form.baseParams['return_quantity']  = quantity.join(';');
    this.frmCreditSlip.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
          this.close();
        }
      },
      scope: this
    });
  }
});