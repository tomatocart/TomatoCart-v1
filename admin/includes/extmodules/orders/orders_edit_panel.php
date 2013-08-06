<?php
/*
  $Id: orders_adress_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.OrdersEditPanel = function(config) {
  
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_address'); ?>';
  config.layout = 'border';
  config.border = false;
  config.items = this.buildForm(config);
  
  Toc.orders.OrdersEditPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.OrdersEditPanel, Ext.Panel, {
  buildForm: function(config){
    this.cboCurrencies = new Ext.form.ComboBox({  
      store: new Ext.data.Store({ 
        url: Toc.CONF.CONN_URL,  
        baseParams: {
          module: 'orders',
          action: 'list_currencies'
        },
        reader: new Ext.data.JsonReader({  
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'id', 
            'text',
            'symbol_left',
            'symbol_right',
            'decimal_places'
          ]
        }),
        autoLoad: false
      }),  
      fieldLabel: '<?php echo $osC_Language->get("field_order_currencies"); ?>',  
      valueField: 'id',
      displayField: 'text',
      hiddenName: 'currency_id',
      triggerAction: 'all',
      allowBlank: false,
      readOnly: true,
      editable: false,
      anchor: '97%',
      listeners: {
        select: this.oncboCurrenciesSelect,
        scope: this
      }
    });
    
    this.cboCurrencies.getStore().on('load', function() {
      this.grdProducts.getStore().load();
    }, this);
    
    this.fsOrderInfo = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_order_information'); ?>',
      layout: 'column',
      autoHeight: true,
      width: 792,
      items: [
        {
          columnWidth: 0.53,
          layout: 'form',
          border: false,
          labelSeparator: ' ',    
          items: [
            {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_customers_name"); ?>', name: 'customers_name'},
            {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_customers_email_address"); ?>', name: 'email_address'}
          ]
        },
        {
          columnWidth: 0.46,
          layout: 'form',
          border: false,
          labelSeparator: ' ', 
          items: this.cboCurrencies
        }
      ]
    });

    dsBillingAddresses = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders',
        action: 'get_customer_addresses',
        orders_id: config.ordersId
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id', 
          'text'
        ]
      })
    });
    
    this.cboBillingAdresses = new Ext.form.ComboBox({
      store: dsBillingAddresses,
      valueField: 'text',
      displayField: 'text',
      hideLabel: true,
      triggerAction: 'all',
      readOnly: true,
      allowBlank: true,
      emptyText: '<?php echo $osC_Language->get('choose_from_addess_book'); ?>',
      listeners: {
        select: this.onCboBillingAdressesSelect,
        scope: this
      }
    });
    
    dsShippingAddresses = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders',
        action: 'get_customer_addresses',
        orders_id: config.ordersId
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id', 
          'text'
        ]
      })
    });
    
    this.cboShippingAdresses = new Ext.form.ComboBox({
      store: dsShippingAddresses,
      valueField: 'text',
      displayField: 'text',
      hideLabel: true,
      triggerAction: 'all',
      readOnly: true,
      allowBlank: true,
      emptyText: '<?php echo $osC_Language->get('choose_from_addess_book'); ?>',
      listeners: {
        select: this.onCboShippingAdressesSelect,
        scope: this
      }
    });
    
    this.cboBillingCountries = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'orders',
          action: 'list_countries'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'countries_id', 
            'countries_name'
          ]
        }),
        autoLoad: false
      }),
      fieldLabel: '<?php echo $osC_Language->get("field_customers_country"); ?>',
      valueField: 'countries_id',
      displayField: 'countries_name',
      hiddenName: 'billing_countries_id',
      triggerAction: 'all',
      readOnly: true,
      allowBlank: false,
      mode: 'local',
      listeners: {
        select: this.onCboBillingCountriesSelect,
        scope: this
      }
    });
    
    this.cboShippingCountries = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'orders',
          action: 'list_countries'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'countries_id', 
            'countries_name'
          ]
        }),
        autoLoad: false
      }),
      fieldLabel: '<?php echo $osC_Language->get("field_customers_country"); ?>',
      valueField: 'countries_id',
      displayField: 'countries_name',
      hiddenName: 'shipping_countries_id',
      triggerAction: 'all',
      readOnly: true,
      allowBlank: false,
      mode: 'local',
      listeners: {
        select: this.onCboShippingCountriesSelect,
        scope: this
      }
    });
    
    this.cboBillingZones = new Ext.form.ComboBox({  
      store: new Ext.data.Store({ 
        url: Toc.CONF.CONN_URL,  
        baseParams: {
          module: 'orders',
          action: 'list_zones'
        },
        reader: new Ext.data.JsonReader({  
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'zone_id',
            'zone_code',
            'zone_name'
          ]
        }),
        autoLoad: false
      }),  
      fieldLabel: '<?php echo $osC_Language->get("field_customers_state"); ?>',  
      valueField: 'zone_id',
      displayField: 'zone_name',  
      hiddenName: 'billing_zone_id',  
      triggerAction: 'all',  
      disabled: true,
      allowBlank: false,
      editable: true
    });
    
    this.cboShippingZones = new Ext.form.ComboBox({
      store: new Ext.data.Store({ 
        url: Toc.CONF.CONN_URL,  
        baseParams: {
          module: 'orders',
          action: 'list_zones'
        },
        reader: new Ext.data.JsonReader({  
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'zone_id',
            'zone_code',
            'zone_name'
          ]
        }),
        autoLoad: false
      }),  
      fieldLabel: '<?php echo $osC_Language->get("field_customers_state"); ?>',  
      valueField: 'zone_id',
      displayField: 'zone_name',  
      hiddenName: 'shipping_zone_id',  
      triggerAction: 'all',  
      disabled: true,
      allowBlank: false,
      readOnly: true
    });
    
    this.fsBillingAddress = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_billing_address'); ?>',
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {xtype: 'textfield', anchor: '97%'},
      items: [
        this.cboBillingAdresses,
        this.txtBillingCustomerName = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_name'); ?>', name: 'billing_name'}),
        this.txtBillingCompany = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_company'); ?>', name: 'billing_company'}),
        this.txtBillingStreet = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_street_address'); ?>', name: 'billing_street_address'}),
        this.txtBillingSuburb = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_suburb'); ?>', name: 'billing_suburb'}),
        this.txtBillingCity = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_city'); ?>', name: 'billing_city'}),
        this.txtBillingPostcode = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_customers_postcode'); ?>', name: 'billing_postcode'}),
        this.cboBillingCountries,
        this.cboBillingZones
      ],
      buttons: [{text: '<?php echo $osC_Language->get('button_update');?>', iconCls:'refresh', handler: this.submitForm, scope: this}]
    });
    
    this.fsShippingAddress = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_shipping_address'); ?>',
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {xtype: 'textfield', anchor: '97%'},
      items: [
        this.cboShippingAdresses,
        this.txtShippingCustomerName = new Ext.form.TextField({id: 'fsShippingAddress-name', fieldLabel: '<?php echo $osC_Language->get('field_customers_name'); ?>', name: 'shipping_name'}),
        this.txtShippingCompany = new Ext.form.TextField({id: 'fsShippingAddress-company', fieldLabel: '<?php echo $osC_Language->get('field_customers_company'); ?>', name: 'shipping_company'}),
        this.txtShippingStreet = new Ext.form.TextField({id: 'fsShippingAddress-street_adress', fieldLabel: '<?php echo $osC_Language->get('field_customers_street_address'); ?>', name: 'shipping_street_address'}),
        this.txtShippingSuburb = new Ext.form.TextField({id: 'fsShippingAddress-suburb', fieldLabel: '<?php echo $osC_Language->get('field_customers_suburb'); ?>', name: 'shipping_suburb'}),
        this.txtShippingCity = new Ext.form.TextField({id: 'fsShippingAddress-city', fieldLabel: '<?php echo $osC_Language->get('field_customers_city'); ?>', name: 'shipping_city'}),
        this.txtShippingPostcode = new Ext.form.TextField({id: 'fsShippingAddress-postcode', fieldLabel: '<?php echo $osC_Language->get('field_customers_postcode'); ?>', name: 'shipping_postcode'}),
        this.cboShippingCountries,
        this.cboShippingZones
      ],
      buttons: [{text: '<?php echo $osC_Language->get('button_update');?>', iconCls:'refresh', handler: this.submitForm, scope: this}]
    });
    
    this.cboPaymentMethods = new Ext.form.ComboBox({  
      store: new Ext.data.Store({ 
        url: Toc.CONF.CONN_URL,  
        baseParams: {
          module: 'orders',
          action: 'list_payment_methods'
        },
        reader: new Ext.data.JsonReader({  
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'id', 
            'text'
          ]
        }),
        autoLoad: false
      }),  
      fieldLabel: '<?php echo $osC_Language->get("field_payment_method"); ?>',  
      valueField: 'id',  
      displayField: 'text',  
      hiddenName: 'method_id',  
      triggerAction: 'all',  
      allowBlank: false,
      readOnly: true,
      editable: false,
      anchor: '97%'
    });

    var rowActions = new Ext.ux.grid.RowActions({
      actions: [{iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
      widthIntercept: Ext.isSafari ? 4: 2
    });
    rowActions.on('action', this.onRowAction, this),  
    
    this.fsGiftWrapping = new Ext.form.FieldSet({ 
      title: '<?php echo $osC_Language->get('subsection_gift_wrapping'); ?>',
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {anchor: '97%'},
      width: 792,
      items: [
        {
          xtype: 'panel',
          layout: 'column',
          border: false,
          items: [
            {
              columnWidth: 0.46,
              border: false,
              layout: 'form',
              items: [
                this.chkGiftWrapping = new Ext.form.Checkbox({fieldLabel: '<?php echo $osC_Language->get("field_gift_wrapping"); ?>', name: 'gift_wrapping'})
              ]
            },
            {
              columnWidth: 0.53,
              border: false,
              layout: 'form',
              defaults: {anchor: '97%'},
              items: [
                this.txtWrappingMessage = new Ext.form.TextArea({fieldLabel: '<?php echo $osC_Language->get("field_wrapping_message"); ?>', name: 'wrapping_message', height:60})
              ]
            }
          ]
        }
      ],
      buttons: [{text: '<?php echo $osC_Language->get('button_update');?>', iconCls:'refresh', handler: this.updateGiftWrapping, scope: this}]
    });
    
    this.frmOrder = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders',
        action: 'save_address',
        orders_id: config.ordersId
      },      
      border: false,
      autoHeight: true,
      region: 'center',
      style: 'padding: 8px 0px 8px 10px',
      items: [
        this.fsOrderInfo,
        {
          xtype : 'panel', 
          layout: 'column',
          border: false,
          items: [
            {
              columnWidth: 0.5,
              layout: 'form',
              border: false,
              items: [
                this.fsBillingAddress,
                {
                  xtype: 'fieldset', 
                  title: '<?php echo $osC_Language->get('subsection_payment_method'); ?>',
                  layout: 'form',
                  height: 110,
                  labelSeparator: ' ',
                  items: [
                    this.cboPaymentMethods, 
                    this.chkUseStoreCredit = new Ext.form.Checkbox({name: 'use_store_credit', boxLabel: '<?php echo $osC_Language->get('field_use_store_credit'); ?>'})
                  ]
                },
                {
                  xtype: 'fieldset', 
                  title: '<?php echo $osC_Language->get('subsection_coupon'); ?>',
                  layout: 'column',
                  height: 120,
                  width: 403,
                  items:[
                    {
                      columnWidth: 0.7,
                      layout: 'form',
                      border: false,
                      labelSeparator: ' ',
                      items: [this.txtCouponCode = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get("field_coupon_code"); ?>', name: 'coupon_code', emptyText: '<?php echo $osC_Language->get('empty_text_coupon_code'); ?>'})]
                    },
                    {
                      columnWidth: 0.29,
                      border: false,
                      items: [this.btnCoupon = new Ext.Button({text: '<?php echo $osC_Language->get('button_redeem');?>', iconCls:'add', handler: this.onCouponCode, scope: this})]
                    }
                  ]
                }
              ]
            },
            {
              columnWidth: 0.49,
              layout: 'form',
              border: false,
              style: 'padding-left: 18px',
              defaults: {
                anchor: '99%'
              },
              items: [
                this.fsShippingAddress,
                {
                  xtype: 'fieldset', 
                  title: '<?php echo $osC_Language->get('subsection_delivery_method'); ?>',
                  layout: 'column',
                  height: 110,
                  labelSeparator: ' ',
                  defaults: {anchor: '97%'},
                  items:[
                    {
                      columnWidth: 0.5,
                      border: false,
                      items: [this.stxShippingMethod = new Ext.ux.form.StaticTextField({hideLabel: true})]
                    },
                    {
                      columnWidth: 0.49,
                      border: false,
                      items:[this.btnEditShippingMethod = new Ext.Button({text: '<?php echo $osC_Language->get('button_change_delivery_method');?>', iconCls:'add', handler: this.onEditShippingMethod, scope: this})]
                    }
                  ]
                },
                {
                  xtype: 'fieldset', 
                  title: '<?php echo $osC_Language->get('subsection_gift_certificate'); ?>',
                  layout: 'form',
                  height: 120,
                  labelSeparator: ' ',
                  defaults: {anchor: '97%'},
                  items: [
                    {
                      xtype: 'panel',
                      layout: 'column',
                      border: false,
                      items: [
                        {
                          columnWidth: 0.50,
                          border: true,
                          layout: 'fit',
                          items: [
                            this.grdGiftCertificates = new Ext.grid.GridPanel({
                              viewConfig: {emptyText: TocLanguage.gridNoRecords},
                              height: 80,
                              ds: new Ext.data.Store({
                                url: Toc.CONF.CONN_URL,
                                baseParams: {
                                    module: 'orders',
                                    action: 'list_gift_certificates',
                                    orders_id: config.ordersId
                                },
                                reader: new Ext.data.JsonReader({
                                  root: Toc.CONF.JSON_READER_ROOT
                                },[
                                  'gift_code'
                                ]),
                                autoLoad: true
                              }),
                              plugins: rowActions,
                              cm: new Ext.grid.ColumnModel([
                                {id: 'gift_code', header: '<?php echo $osC_Language->get('table_heading_gift_certificate_code');?>', dataIndex: 'gift_code'},
                                rowActions
                              ]),
                              autoExpandColumn: 'gift_code'
                            })
                          ]
                        },
                        {
                          columnWidth: 0.49,
                          border: false,
                          layout: 'form',
                          items: [this.txtGiftCertificate = new Ext.form.TextField({anchor: '95%', hideLabel: true, emptyText: '<?php echo $osC_Language->get('empty_text_gift_certificate'); ?>'})],
                          buttons: [this.btnGiftCertificate = new Ext.Button({text: '<?php echo $osC_Language->get('button_redeem');?>', iconCls:'add', handler: this.onAddGiftCertificate, scope: this})]
                        }
                      ]
                    }
                  ]
                }
              ]
            }
          ]
        },
        this.fsGiftWrapping,
        this.grdProducts = new Toc.orders.OrdersEditProductsGrid({ordersId: config.ordersId, cboCurrencies: this.cboCurrencies, owner: config.owner, outStockProduct: config.outStockProduct}),
        {
          layout: 'fit',
          border: false,
          style: 'padding: 0 24px 8px 420px;',
          autoHeight: true,
          items:[
            this.fsOrderTotals = new Ext.form.FieldSet ({
              title: '<?php echo $osC_Language->get('subsection_order_totals'); ?>',
              layout: 'form',
              labelSeparator: ' ',
              autoHeight: true,
              width: 250
            })
          ]
        }        

      ]
    });
    
    this.grdProducts.getStore().on('load', function() {
      this.unmask();
      
      if (this.grdProducts.getStore(). getCount() > 0) {
        this.stxShippingMethod.setValue(this.grdProducts.store.reader.jsonData.shipping_method);
        this.fsOrderTotals.body.update(this.grdProducts.store.reader.jsonData.totals);

        this.btnCoupon.enable();
        this.btnGiftCertificate.enable();
        this.cboPaymentMethods.enable();
        this.btnEditShippingMethod.enable();

        if (this.enable_store_credit == true) {
          this.chkUseStoreCredit.enable();
        }        
      } else {
        this.btnCoupon.disable();
        this.btnGiftCertificate.disable();
        this.cboPaymentMethods.disable();
        this.chkUseStoreCredit.disable();
        this.btnEditShippingMethod.disable();
      }
    }, this);

    this.loadForm(config);
    
    return this.frmOrder;
  },
  
  updateGiftWrapping: function() {
    this.mask();
    
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'set_gift_wrapping',
        orders_id: this.ordersId,
        checked: this.chkGiftWrapping.getValue(),
        message: this.txtWrappingMessage.getValue()
      },
      callback: function (options, success, response) {
        this.unmask();

        var result = Ext.decode(response.responseText);
        if (result.success == true) {
          this.grdProducts.getStore().load();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  loadForm: function (config) {
    this.frmOrder.load({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'load_order',
        orders_id: config.ordersId
      },
      success: function (form, action) {
        //currency
        this.cboCurrencies.getStore().on('load', function(){
          this.cboCurrencies.setValue(action.result.data.currency);
        }, this);
        this.cboCurrencies.getStore().load();
        
        //payment method
        this.cboPaymentMethods.getStore().on('load', function(){
          this.cboPaymentMethods.setValue(action.result.data.payment_method);
          
          this.cboPaymentMethods.on('select', this.onPaymentMethodChange, this);
        }, this);
        this.cboPaymentMethods.getStore().load();
        
        if (action.result.data.has_payment_method == false) {
          this.cboPaymentMethods.disable();
        }
        
        //store credit
        this.enable_store_credit = action.result.data.enable_store_credit;
        if (action.result.data.enable_store_credit == true) {
          this.chkUseStoreCredit.setValue(action.result.data.use_store_credit);
          this.chkUseStoreCredit.enable();
          this.chkUseStoreCredit.on('check', this.onPaymentMethodChange, this);
        } else {
          this.chkUseStoreCredit.disable();
        }
        
        //coupon code
        if (!Ext.isEmpty(action.result.data.coupon_code)) {
          this.txtCouponCode.disable();
          this.btnCoupon.setText('<?php echo $osC_Language->get("button_remove"); ?>');
          this.btnCoupon.setIconClass('remove');
        } else {
          this.txtCouponCode.enable();
        }
        
        //billing address
        this.cboBillingCountries.getStore().on('load', function(){
          this.setBillingAddress(action.result.data.billing_address);
        }, this);
        this.cboBillingCountries.getStore().load();
        
        //billing address
        this.cboShippingCountries.getStore().on('load', function(){
          this.setShippingAddress(action.result.data.shipping_address);
        }, this);
        this.cboShippingCountries.getStore().load();
      },
      failure: function (form, action) {
        Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
      },
      scope: this
    });
  },
  
  mask: function() {
    this.el.mask(TocLanguage.loadingText, 'x-mask-loading');
  },
  
  unmask: function() {
    this.el.unmask();
  },  
  
  oncboCurrenciesSelect: function() {
    this.mask();

    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'change_currency',
        orders_id: this.ordersId,
        currency: this.cboCurrencies.getValue()
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.grdProducts.getStore().load();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  onCboBillingAdressesSelect: function() {
    var address = this.cboBillingAdresses.getValue().toString();
    
    if (address == '<?php echo $osC_Language->get('add_new_address'); ?>') {
      this.resetBillingAddress();
    } else {
      this.setBillingAddress(address);
    }
  },
  
  resetBillingAddress: function () {
    this.txtBillingCustomerName.setValue('');
    this.txtBillingCompany.setValue('');
    this.txtBillingStreet.setValue('');
    this.txtBillingSuburb.setValue('');
    this.txtBillingCity.setValue('');
    this.txtBillingPostcode.setValue('');
    this.cboBillingZones.reset();
    this.cboBillingZones.disable();
    this.cboBillingCountries.reset();
  },
  
  setBillingAddress: function(address) {
    var data = address.split(',');
    
    if (data.length > 1){
       this.txtBillingCustomerName.setValue(data[0]);
       this.txtBillingCompany.setValue(data[1]);
       this.txtBillingStreet.setValue(data[2]);
       this.txtBillingSuburb.setValue(data[3]);
       this.txtBillingCity.setValue(data[4]);
       this.txtBillingPostcode.setValue(data[5]);
       
       if(!Ext.isEmpty(data[7])) {
         countries_id = this.cboBillingCountries.getStore().getAt(this.cboBillingCountries.getStore().find('countries_name', data[7])).get('countries_id');
         this.cboBillingCountries.setValue(countries_id);
                    
         this.cboBillingZones.getStore().on('load', function() {
           var index = this.cboBillingZones.getStore().find('zone_name', data[6]);
           
           if (index != -1) {
             this.cboBillingZones.setValue(this.cboBillingZones.getStore().getAt(index).get('zone_id'));
           } else {
             this.cboBillingZones.setRawValue(data[6]);
             this.cboBillingZones.setEditable(true);
           }
           
           this.cboBillingZones.getStore().purgeListeners();
         }, this);
         
         this.cboBillingZones.getStore().baseParams['countries_id'] = countries_id;
         this.cboBillingZones.getStore().load();
         this.cboBillingZones.enable();
       }
    } else {
      this.resetBillingAddress();
    }
  },
  
  onCboShippingAdressesSelect: function() {
    var address = this.cboShippingAdresses.getValue().toString();
    
    if (address == '<?php echo $osC_Language->get('add_new_address'); ?>') {
      this.resetShippingAddress();
    } else {
      this.setShippingAddress(address);
    }
  },
  
  resetShippingAddress: function () {
    this.txtShippingCustomerName.setValue('');
    this.txtShippingCompany.setValue('');
    this.txtShippingStreet.setValue('');
    this.txtShippingSuburb.setValue('');
    this.txtShippingCity.setValue('');
    this.txtShippingPostcode.setValue('');
    this.cboShippingZones.reset();
    this.cboShippingZones.disable();
    this.cboShippingCountries.reset();
  },
  
  setShippingAddress: function(address) {
    var data = address.split(',');
    
    if (data.length > 1){
       this.txtShippingCustomerName.setValue(data[0]);
       this.txtShippingCompany.setValue(data[1]);
       this.txtShippingStreet.setValue(data[2]);
       this.txtShippingSuburb.setValue(data[3]);
       this.txtShippingCity.setValue(data[4]);
       this.txtShippingPostcode.setValue(data[5]);
       
       if(!Ext.isEmpty(data[7])) {
         countries_id = this.cboShippingCountries.getStore().getAt(this.cboShippingCountries.getStore().find('countries_name', data[7])).get('countries_id');
         this.cboShippingCountries.setValue(countries_id);
                    
         this.cboShippingZones.getStore().on('load', function() {
           var index = this.cboShippingZones.getStore().find('zone_name', data[6]);
           
           if (index != -1) {
             this.cboShippingZones.setValue(this.cboShippingZones.getStore().getAt(index).get('zone_id'));
           } else {
             this.cboShippingZones.setRawValue(data[6]);
             this.cboShippingZones.setEditable(true);
           }
           
           this.cboShippingZones.getStore().purgeListeners();
         }, this);
         
         this.cboShippingZones.getStore().baseParams['countries_id'] = countries_id;
         this.cboShippingZones.getStore().load();
         this.cboShippingZones.enable();
       }
    } else {
       this.resetBillingAddress();
    }
  },
  
  onCboBillingCountriesSelect: function() {
    this.cboBillingZones.reset();
    this.cboBillingZones.getStore().baseParams['countries_id'] = this.cboBillingCountries.getValue();  
    this.cboBillingZones.getStore().load();
    this.cboBillingZones.enable();
  },
  
  onCboShippingCountriesSelect: function() {
    this.cboShippingZones.reset();
    this.cboShippingZones.getStore().baseParams['countries_id'] = this.cboShippingCountries.getValue();
    this.cboShippingZones.getStore().load();
    this.cboShippingZones.enable();
  },
  
  onEditShippingMethod: function() {
    if (this.owner.owner.owner == undefined) {
      dlg = this.owner.owner.createOrdersChooseShippingMethodDialog(this.ordersId);
    } else { 
      dlg = this.owner.owner.owner.createOrdersChooseShippingMethodDialog(this.ordersId);
    }
    
    dlg.on('saveSuccess', function() {
      this.grdProducts.getStore().load();
    }, this);
      
    dlg.show();
  },
  
  onCouponCode: function() {
    if ( this.btnCoupon.getText() == '<?php echo $osC_Language->get("button_redeem"); ?>' ) {
      if ( this.txtCouponCode.getValue() != '' ) {
        this.mask();
        
        Ext.Ajax.request({
          waitMsg: TocLanguage.formSubmitWaitMsg,
          url: Toc.CONF.CONN_URL,
          params: {
            module: 'orders',
            action: 'add_coupon',
            orders_id: this.ordersId,
            coupon_code: this.txtCouponCode.getValue()
          },
          callback: function (options, success, response) {
            this.unmask();
            
            var result = Ext.decode(response.responseText);
            
            if (result.success == true) {
              this.grdProducts.getStore().load();
              this.txtCouponCode.disable();
              this.btnCoupon.setText('<?php echo $osC_Language->get("button_remove"); ?>');
              this.btnCoupon.setIconClass('remove');
            } else {
              Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
          },
          scope: this
        });
      } else {
        this.txtCouponCode.markInvalid('<?php echo $osC_Language->get("ms_coupon_is_null"); ?>');
        this.txtCouponCode.focus();
      }
    } else {
      this.mask();
      
      Ext.Ajax.request({
        waitMsg: TocLanguage.formSubmitWaitMsg,
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'orders',
          action: 'delete_coupon',
          orders_id: this.ordersId
        },
        callback: function (options, success, response) {
          this.unmask();
          
          var result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            this.grdProducts.getStore().load();
            this.txtCouponCode.enable();
            this.txtCouponCode.reset();
            this.btnCoupon.setText('<?php echo $osC_Language->get("button_redeem"); ?>');
            this.btnCoupon.setIconClass('add');
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
          }
        },
        scope: this
      });
    }
  },
  
  onAddGiftCertificate: function() {
    if (!Ext.isEmpty(this.txtGiftCertificate.getValue())) {
      this.mask();
      
      Ext.Ajax.request({
        waitMsg: TocLanguage.formSubmitWaitMsg,
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'orders',
          action: 'add_gift_certificate',
          orders_id: this.ordersId,
          gift_certificate_code: this.txtGiftCertificate.getValue()
        },
        callback: function (options, success, response) {
          this.unmask();
          
          var result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            this.grdProducts.getStore().load();
            this.txtGiftCertificate.reset();
            this.grdGiftCertificates.getStore().load();
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
          }
        },
        scope: this
      });
    } else {
      this.txtGiftCertificate.markInvalid('<?php echo $osC_Language->get("ms_gift_certificate_is_null"); ?>');
      this.txtGiftCertificate.focus();
    }
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDeleteGiftCertificate(record);
        break;
    }
  },
  
  onDeleteGiftCertificate: function (record) {
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          this.mask();
          
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'orders',
              action: 'delete_gift_certificate',
              gift_certificate_code: record.get('gift_code'),
              orders_id: this.ordersId
            },
            callback: function (options, success, response) {
              this.unmask();
              
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.grdGiftCertificates.getStore().load();
                this.grdProducts.getStore().load();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      }, 
      this
    );
  },
  
  onPaymentMethodChange: function() {
    this.mask();
    
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'update_payment_method',
        payment_method: this.cboPaymentMethods.getValue(),
        use_store_credit: this.chkUseStoreCredit.getValue(),
        orders_id: this.ordersId
      },
      callback: function (options, success, response) {
        this.unmask();
        
        var result = Ext.decode(response.responseText);
        if (result.success == true) {
          this.grdProducts.getStore().load();
          
          if (result.disable_cbo_payment == true) {
            this.cboPaymentMethods.allowBlank = true;
            this.cboPaymentMethods.setValue('');
            this.cboPaymentMethods.setRawValue('');
            this.cboPaymentMethods.disable();
          } else {
            this.cboPaymentMethods.allowBlank = false;
            this.cboPaymentMethods.enable();
          }
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
    
  },

  submitForm : function() {
    this.frmOrder.baseParams['billing_countries'] = this.cboBillingCountries.getRawValue();
    this.frmOrder.baseParams['shipping_countries'] = this.cboShippingCountries.getRawValue();
    this.frmOrder.baseParams['billing_state'] = this.cboBillingZones.getRawValue();
    this.frmOrder.baseParams['shipping_state'] = this.cboShippingZones.getRawValue();
    
    var index = this.cboBillingZones.getStore().find('zone_name', this.cboBillingZones.getRawValue());
    if (index != -1) {
     this.frmOrder.baseParams['billing_state_code'] = this.cboBillingZones.getStore().getAt(index).get('zone_code');
    } else {
     this.frmOrder.baseParams['billing_state_code'] = '';
    }
    
    var index = this.cboShippingZones.getStore().find('zone_name', this.cboShippingZones.getRawValue());
    if (index != -1) {
     this.frmOrder.baseParams['shipping_state_code'] = this.cboShippingZones.getStore().getAt(index).get('zone_code');
    } else {
     this.frmOrder.baseParams['shipping_state_code'] = '';
    }
    
    
    this.cboBillingCountries.disable();
    this.cboShippingCountries.disable();
    this.cboBillingAdresses.disable();
    this.cboShippingAdresses.disable();

   
    this.frmOrder.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.grdProducts.getStore().load();
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });
    
    this.cboBillingCountries.enable();
    this.cboShippingCountries.enable();
    this.cboBillingAdresses.enable();
    this.cboShippingAdresses.enable();   
  }
});