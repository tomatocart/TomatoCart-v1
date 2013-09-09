<?php
/*
  $Id: specials_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.specials.SpecialsDialog = function (config) {

	onfig = config || {};
    
	config.id = 'specials-dialog-win';
	config.layout = 'fit';
	config.width = 800;
	config.autoheight = true;
	config.modal = true;
	config.iconCls = 'icon-specials-win';
  config.items = this.buildForm();
  
	config.buttons = [
    {
	    text: TocLanguage.btnSave,
	    handler: function () {
        this.cboProducts.enable();
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
  
	this.addEvents({'saveSuccess': true, 'addVariants': true});
  
	Toc.specials.SpecialsDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.specials.SpecialsDialog, Ext.Window, {
	show: function (id) {
		var specialsId = id || null;
		
		this.frmSpecials.form.reset();
    this.frmSpecials.form.baseParams['specials_id'] = specialsId;
    
    //support variants specials
    switch(this.productsType) {
      case '<?php echo PRODUCTS_TYPE_GENERAL;?>':
        var action = 'load_specials';
        break;
        
      case '<?php echo PRODUCTS_TYPE_VARIANTS; ?>':
        var action = 'load_variants_specials';
        break;
      default:
        var action = 'load_specials';
    }
    
		if (specialsId > 0) {
			this.frmSpecials.load({
				url: Toc.CONF.CONN_URL,
				params: {
					action: action
				},
				success: function (form, action) {
				  var netValue = action.result.data.specials_new_products_price;
				  var rate = this.getTaxRate();

          if (rate > 0) {
            netValue = netValue / ((rate / 100) + 1);
          }
          
          this.cboProducts.setRawValue(action.result.data.products_name);
          this.cboProducts.disable();
          this.txtPriceGross.setValue(Math.round(netValue * Math.pow(10, 4)) / Math.pow(10, 4));
				  
					Toc.specials.SpecialsDialog.superclass.show.call(this);
				},
				failure: function (form, action) {
					Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				},
				scope: this
			});
		} else {
			Toc.specials.SpecialsDialog.superclass.show.call(this);
		}
	},
  	
	buildForm: function () {
    dsProducts = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'specials',
        action: 'list_products'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'products_id',
        fields: [
          'products_id', 
          'products_name',
          'rate'
        ]
      }),
      autoLoad: true
    });
  
    this.cboProducts = new Ext.form.ComboBox({
      store: dsProducts,
      fieldLabel: '<?php echo $osC_Language->get("field_product"); ?>',
      allowBlank: false,
      displayField: 'products_name',
      mode: 'local',
      valueField: 'products_id',
      hiddenName: 'products_id',
      triggerAction: 'all',
      editable: false,
      pageSize: Toc.CONF.GRID_PAGE_SIZE
    });
    
    this.txtPriceNet = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get("field_price_net_percentage"); ?>', 
      xtype:'textfield',
      name: 'specials_new_products_price',
      value: '0'
    });
    this.txtPriceNet.on('change', this.onPriceNetChange, this);
    
    this.txtPriceGross = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get("field_price_gross"); ?>', 
      xtype:'textfield',
      name: 'products_price_gross',
      value: '0'
    });
    this.txtPriceGross.on('change', this.onPriceGrossChange, this);
    
		this.frmSpecials = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
			baseParams: {
				module: 'specials',
        action: 'save_specials'				
			},
			border: false,
			frame: false,
			autoHeight: true,
      layoutConfig: {labelSeparator: ''},
			defaults: {anchor: '97%'},
      labelWidth: 200,
			items: [
			  this.chkVariants = new Ext.form.Checkbox({
          fieldLabel: '<?php echo $osC_Language->get('field_variants'); ?>',
          name: 'variants',
          checked: false,
          listeners: {
            check: this.onChkVariantsChecked,
            scope: this
          }
        }),
        this.cboProducts,
        this.txtPriceNet, 
        this.txtPriceGross, 
        {
			    xtype: 'checkbox',
			    fieldLabel: '<?php echo $osC_Language->get("field_status"); ?>',
			    name: 'status',
			    anchor: ''
		    },
        {
          xtype: 'datefield',
          fieldLabel: '<?php echo $osC_Language->get("field_date_start"); ?>',
          name: 'start_date',
          format: 'Y-m-d',
          allowBlank: false,
          readOnly: true
        }, 
        {
          xtype: 'datefield',
          fieldLabel: '<?php echo $osC_Language->get("field_date_expires"); ?>',
          name: 'expires_date',
          format: 'Y-m-d',
          allowBlank: false,
          readOnly: true
        }
      ]
		});
    
    return this.frmSpecials;
	},
	
	onPriceNetChange: function() {
    netValue = this.txtPriceNet.getValue();
    taxRate = this.getTaxRate();

    if (netValue.indexOf('%') > -1) {
      this.txtPriceGross.setValue('');
      this.txtPriceGross.disable();
      return false;
    } else if ( this.txtPriceGross.disabled == true ) {
      this.txtPriceGross.enable();
    }
    
    if (taxRate > 0) {
      netValue = netValue * ((taxRate / 100) + 1);
    }

    this.txtPriceGross.setValue(Math.round(netValue * Math.pow(10, 4)) / Math.pow(10, 4));
  },
  
  onPriceGrossChange: function(){
    grossValue = this.txtPriceGross.getValue();
    rate = this.getTaxRate();
    
    if (grossValue.indexOf('%') > -1) {
      this.txtPriceGross.setValue('');
      this.txtPriceGross.disable();
      this.txtPriceNet.focus();
      return false;
    } 
    
    if (rate > 0) {
      grossValue = grossValue / ((rate / 100) + 1);
    }

    this.txtPriceNet.setValue(Math.round(grossValue * Math.pow(10, 4)) / Math.pow(10, 4));
  },
  
  getTaxRate: function() {
    rate = 0;
    rateId = this.cboProducts.getValue();
    ds = this.cboProducts.getStore();
    
    for (i = 0; i < ds.getCount(); i++) {
      record = ds.getAt(i);
      
      if(record.id == rateId) {
        rate = record.get('rate');
        break;
      }
    }
    return rate;  
  },
  
  onChkVariantsChecked: function(checkbox, checked) {
    var store = this.cboProducts.getStore();
    
    if (checked) {
      store.baseParams['variants'] = 1;
      
      this.fireEvent('addVariants', 1);
    } else {
      store.baseParams['variants'] = 0;
      
      this.fireEvent('addVariants', 0);
    }
    
    store.reload();
  },
  
	submitForm: function () {
		this.frmSpecials.form.submit({
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
	}
});