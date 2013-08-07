<?php
/*
  $Id: data_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.products.DataPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_data'); ?>';
  config.activeTab = 0;
  config.productsType = 1;
  config.tabExtraOptions = null;
  
  config.items = this.buildForm();
  
  Toc.products.DataPanel.superclass.constructor.call(this, config);
  
  this.addEvents({'producttypechange': true});
};

Ext.extend(Toc.products.DataPanel, Ext.TabPanel, {

  buildForm: function() {
    var dsProductsType = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: 
        [
          ['<?php echo PRODUCT_TYPE_SIMPLE; ?>','<?php echo $osC_Language->get('products_type_simple'); ?>'],
          ['<?php echo PRODUCT_TYPE_VIRTUAL; ?>','<?php echo $osC_Language->get('products_type_vrtual'); ?>'],
          ['<?php echo PRODUCT_TYPE_DOWNLOADABLE; ?>','<?php echo $osC_Language->get('products_type_downloadable'); ?>'],
          ['<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>','<?php echo $osC_Language->get('products_type_gift_certificate'); ?>']
        ]
    });

    this.cboProductsType = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_products_type'); ?>',
      xtype: 'combo', 
      store: dsProductsType, 
      name: 'products_type_ids', 
      mode: 'local',
      hiddenName: 'products_type', 
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      editable: false,
      forceSelection: true,      
      value: '<?php echo PRODUCT_TYPE_SIMPLE; ?>',
      listeners: {
        select: this.onProductsTypeSelect,
        scope: this
      }
    });
    
    dsManufacturers = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_manufacturers'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboManufacturers.setValue('0');},
        scope: this
      }
    });
    
    this.cboManufacturers = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_manufacturer'); ?>', 
      xtype:'combo', 
      store: dsManufacturers, 
      name: 'manufacturers', 
      hiddenName: 'manufacturers_id', 
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      editable: false,
      forceSelection: true      
    });    
    
    dsWeightClasses = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_weight_classes'
      },
      reader: new Ext.data.JsonReader({
          fields: ['id', 'text'],
          root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboWeightClasses.setValue('<?php echo SHIPPING_WEIGHT_UNIT; ?>');},
        scope: this
      }
    });
    
    this.cboWeightClasses = new Ext.form.ComboBox({
      width: 95, 
      xtype: 'combo', 
      store: dsWeightClasses, 
      id: 'combWeightClasses', 
      name: 'products_weight_class_ids', 
      hiddenName: 'products_weight_class', 
      hideLabel: true,
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      editable: false,
      forceSelection: true     
    });

      
    this.fsStatus = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_data'); ?>', 
      layout: 'column', 
      width: 750,
      autoHeight: true,
      labelSeparator: ' ',
      items:[
        {
          columnWidth: .52,
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          defaults: {
            anchor: '90%'
          },
          items:[
            this.cboProductsType,
            {
              layout: 'column',
              border: false,
              items:[
                {
                  width: 210,
                  layout: 'form',
                  labelSeparator: ' ',
                  border: false,
                  items:[
                    {fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', xtype:'radio', name: 'products_status', boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>', xtype:'radio', inputValue: '1', checked: true}
                  ]
                },
                {
                  width: 80,
                  layout: 'form',
                  border: false,
                  items: [
                    {fieldLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', xtype:'radio', name: 'products_status', hideLabel: true, inputValue: '0'}
                  ]
                }
              ]
            },
            {fieldLabel: '<?php echo $osC_Language->get('field_date_available'); ?>', name: 'products_date_available', format: 'Y-m-d', xtype: 'datefield', readOnly: true, width: 165}         
          ]
        },
        {
          columnWidth: .47,
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          defaults: {
            anchor: '97%'
          },              
          items: [
            {fieldLabel: '<?php echo $osC_Language->get('field_sku'); ?>', xtype:'textfield', name: 'products_sku'},
            {fieldLabel: '<?php echo $osC_Language->get('field_model'); ?>', xtype:'textfield', name: 'products_model'},
            this.cboManufacturers,
            {
              layout: 'column',
              border: false,
              items:[
                {
                  width: 210,
                  layout: 'form',
                  labelSeparator: ' ',
                  border: false,
                  items:[
                    {fieldLabel: '<?php echo $osC_Language->get('field_weight'); ?>', xtype:'textfield', name: 'products_weight', width: 75}
                  ]
                },
                {
                  layout: 'form',
                  border: false,
                  items: this.cboWeightClasses
                }
              ]
            }
          ]
        }
      ]
    });  
      
    dsTaxClasses = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_tax_classes'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'rate', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboTaxClass.setValue('0');},
        scope: this
      }
    });

    this.cboTaxClass = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_tax_class'); ?>', 
      xtype:'combo', 
      store: dsTaxClasses, 
      name: 'products_tax_class', 
      hiddenName: 'products_tax_class_id', 
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      forceSelection: true,
      editable: false,
      forceSelection: true,
      listeners: {
        select: this.onTaxClassSelect,
        scope: this
      }
    });    
    
    this.txtPriceNet = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_price_net'); ?>', 
      xtype:'textfield', 
      name: 'products_price',
      value: '0',
      listeners: {
        change: this.onPriceNetChange,
        scope: this
      }
    });
    
    this.txtPriceGross = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_price_gross'); ?>', 
      xtype:'textfield', 
      name: 'products_price_gross',
      value: '0',
      listeners: {
        change: this.onPriceGrossChange,
        scope: this
      }
    });

    dsQuantityDiscountGroup = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_quantity_discount_groups'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboPriceDiscountGroups.setValue('0');},
        scope: this
      }
    });

    this.cboPriceDiscountGroups = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_price_discount_groups'); ?>', 
      store: dsQuantityDiscountGroup, 
      name: 'quantity_discount_groups', 
      hiddenName: 'quantity_discount_groups_id', 
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      editable: false,
      forceSelection: true           
    });
                  
    this.fsPrice = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_price'); ?>', 
      layout: 'form', 
      columnWidth: 0.49,
      height: 205,
      labelSeparator: ' ',
      defaults: {
        anchor: '95%'
      },
      items:[this.cboTaxClass, this.txtPriceNet, this.txtPriceGross, this.cboPriceDiscountGroups] 
    });

    dsUnitClass = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_quantity_units'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboUnitClasses.setValue('<?php echo DEFAULT_UNIT_CLASSES; ?>');},
        scope: this
      }
    });

    this.cboUnitClasses = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_quantity_unit'); ?>', 
      store: dsUnitClass, 
      name: 'quantity_unit', 
      hiddenName: 'quantity_unit_class', 
      displayField: 'text', 
      valueField: 'id', 
      triggerAction: 'all', 
      editable: false,
      forceSelection: true     
    });
      
    this.fsInformation = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('subsection_information'); ?>', 
      layout: 'form', 
      height: 205,
      labelSeparator: ' ',
      columnWidth: 0.51,
      style: 'margin-left: 10px',
      defaults: {
        anchor: '95%'
      },
      items:[
        this.txtQuantity = new Ext.form.NumberField({fieldLabel: '<?php echo $osC_Language->get('field_quantity'); ?>', name: 'products_quantity', allowDecimals: false, value: 0}) , 
        {fieldLabel: '<?php echo $osC_Language->get('field_minimum_order_quantity'); ?>', xtype:'numberfield', name: 'products_moq', allowDecimals: false, value: 1},
        {fieldLabel: '<?php echo $osC_Language->get('field_increment'); ?>', xtype:'numberfield', name: 'order_increment', allowDecimals: false, value: 1},
        this.cboUnitClasses,
        this.txtMaxOrderQuantity = new Ext.form.NumberField({fieldLabel: '<?php echo $osC_Language->get('field_Maximum_order_quantity'); ?>', name: 'products_max_order_quantity', allowDecimals: false, disabled:true, minValue: 1}),
        this.chkUnlimited = new Ext.form.Checkbox({
          fieldLabel: '',
          boxLabel: '<?php echo $osC_Language->get('field_unlimited'); ?>',
          name: 'unlimited',
          checked: true,
          listeners: {
            check: this.onChkUnlimitedChecked,
            scope: this
          }
        })
       ] 
    });
    
    var pnlGeneral = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_general'); ?>',
      style: 'padding: 10px',
      items: [
        this.fsStatus,
        {
          layout: 'column',
          border: false,
          width: 750,
          items: [
            this.fsPrice,
            this.fsInformation
          ]
        }
      ]
    });  
    
    return pnlGeneral;      
  },
  
  getTaxRate: function() {
    rate = 0;
    rateId = this.cboTaxClass.getValue();
    store = this.cboTaxClass.getStore();

    for (i = 0; i < store.getCount(); i++) {
      record = store.getAt(i);
       
      if(record.get('id') == rateId) {
        rate = record.get('rate');
        break;
      }
    }
    
    return rate;  
  },
  
  onPriceNetChange: function() {
    value = this.txtPriceNet.getValue();
    rate = this.getTaxRate();

    if (rate > 0) {
      value = value * ((rate / 100) + 1);
    }

    this.txtPriceGross.setValue(Math.round(value * Math.pow(10, 4)) / Math.pow(10, 4));
  },
  
  onPriceGrossChange: function() {
    value = this.txtPriceGross.getValue();
    rate = this.getTaxRate();

    if (rate > 0) {
      value = value / ((rate / 100) + 1);
    }

    this.txtPriceNet.setValue(Math.round(value * Math.pow(10, 4)) / Math.pow(10, 4));
  },
  
  onTaxClassSelect: function(combo, record) {
    value = this.txtPriceNet.getValue();
    rate = record.get('rate');
    
    if (rate > 0) {
      value = value * ((rate / 100) + 1);
    }
    
    this.txtPriceGross.setValue(Math.round(value * Math.pow(10, 4)) / Math.pow(10, 4));
  },
  
  onProductsTypeSelect: function(combo, record) {
    var type = record.get('id');
    
    if (this.productsType != type) {
      if ( (this.productsType != '<?php echo PRODUCT_TYPE_SIMPLE; ?>') && (this.productsType != '<?php echo PRODUCT_TYPE_VIRTUAL; ?>') ) {
        this.remove(this.tabExtraOptions);
      }
      
      this.productsType = type;
      if(this.productsType == '<?php echo PRODUCT_TYPE_DOWNLOADABLE; ?>') {
        this.tabExtraOptions = new Toc.products.DownloadablesPanel();
        this.add(this.tabExtraOptions);
        this.setActiveTab(this.tabExtraOptions);
      } else if (this.productsType == '<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>') {
        this.tabExtraOptions = new Toc.products.GiftCertificatesPanel({owner: this});
        this.add(this.tabExtraOptions);
        this.setActiveTab(this.tabExtraOptions);   
      }
      
      //tax class
      this.updateCboTaxClass(type);
      
      this.fireEvent('producttypechange', type);
    }
  },  
  
  updateCboTaxClass: function (type) {
    if (type == '<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>') {
      this.cboTaxClass.setValue('0');
      this.cboTaxClass.disable();
    } else {
      this.cboTaxClass.enable();
    }
  },

  onVariantsChange: function(hasVariant, quantity) {
    if(hasVariant) {
      this.txtQuantity.disable();
      this.txtQuantity.setValue(quantity);
    } else {
      this.txtQuantity.enable();
    }
  },
  
  onChkUnlimitedChecked: function(checkbox, checked) {
    if (checked) {
      this.txtMaxOrderQuantity.disable();
      this.txtMaxOrderQuantity.allowBlank = true;
      this.txtMaxOrderQuantity.setValue('');
    } else {
      this.txtMaxOrderQuantity.enable();
      this.txtMaxOrderQuantity.allowBlank = false;
    }
  },
  
  loadExtraOptionTab: function(data) {
    var type = data.products_type;
    
    if (type == '<?php echo PRODUCT_TYPE_DOWNLOADABLE; ?>') {
      this.tabExtraOptions = new Toc.products.DownloadablesPanel();
      this.add(this.tabExtraOptions);
      this.setActiveTab(this.tabExtraOptions);
      this.setActiveTab(0);
      this.tabExtraOptions.loadForm(data);
    } else if (type == '<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>') {
      this.tabExtraOptions = new Toc.products.GiftCertificatesPanel({owner: this});
      this.add(this.tabExtraOptions);
      this.setActiveTab(this.tabExtraOptions);
      this.setActiveTab(0);
      this.tabExtraOptions.loadForm(data);
    }
    
    if (data.products_max_order_quantity > 0) {
      this.txtMaxOrderQuantity.enable();
      this.txtMaxOrderQuantity.setValue(data.products_max_order_quantity);
      this.chkUnlimited.setValue(false);
    } else if (data.products_max_order_quantity <= 0) {
      this.txtMaxOrderQuantity.disable();
      this.txtMaxOrderQuantity.setValue('');
      this.chkUnlimited.setValue(true);
    }
    
    this.cboProductsType.disable();
  },
  
  getProductsType: function() {
    return this.cboProductsType.getValue();
  }
});