<?php
/*
  $Id: tax_rates_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tax_classes.TaxRatesDialog = function(config) {

  config = config || {}; 
  
  config.id = 'tax-rate-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_tax_rate'); ?>';
  config.width = 500;
  config.modal = true;
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
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.tax_classes.TaxRatesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.tax_classes.TaxRatesDialog, Ext.Window, {  

  show: function (taxClassId, ratesId) {
    this.taxClassId = taxClassId || null;
    var taxRatesId = ratesId || null; 
    
    this.frmTaxRate.form.reset();
    this.frmTaxRate.form.baseParams['tax_class_id'] = this.taxClassId;
    this.frmTaxRate.form.baseParams['tax_rates_id'] = taxRatesId;

    if (taxRatesId > 0) {
      this.frmTaxRate.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'tax_classes',
          action: 'load_tax_rate',
          tax_rates_id: taxRatesId
        },
        success: function(form, action) {
          Toc.tax_classes.TaxRatesDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this       
      });
    } else {   
      Toc.tax_classes.TaxRatesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    var dsGeoZone = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'tax_classes', 
        action: 'list_geo_zones'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['geo_zone_id', 'geo_zone_name']
      }),
      autoLoad: true                                                                                    
    });
    
    this.cobZoneGroup = new Ext.form.ComboBox({
      name: 'geo_zone',
      fieldLabel: '<?php echo $osC_Language->get('field_tax_rate_zone_group'); ?>', 
      store: dsGeoZone, 
      valueField: 'geo_zone_id', 
      displayField: 'geo_zone_name', 
      hiddenName: 'geo_zone_id', 
      editable: false, 
      triggerAction: 'all', 
      allowBlank: false
    });
    
    this.frmTaxRate = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'tax_classes',
        action: 'save_tax_rate'
      }, 
      border: false,
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        anchor: '97%'
      },
      items: [
        this.cobZoneGroup,
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_tax_rate'); ?>', name: 'tax_rate', decimalPrecision: 4, width:300},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_tax_rate_description'); ?>', name: 'tax_description', width:300},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_tax_rate_priority'); ?>', name: 'tax_priority', width:300}
      ]
    });
    
    return this.frmTaxRate;
  },

  submitForm: function() {
    this.frmTaxRate.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback); 
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