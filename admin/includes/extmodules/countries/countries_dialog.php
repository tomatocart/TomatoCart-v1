<?php
/*
  $Id: countries_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.countries.CountriesDialog = function(config){
 
  config = config || {};

  config.id = 'countries-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_country'); ?>';
  config.width = 450;
  config.modal = true;
  config.iconCls = 'icon-countries-win';
  config.items =this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
        handler: function() {
          this.submitForm();
        }, 
        scope: this
      }, 
      {
        text: TocLanguage.btnClose,
        handler: function() {
          this.close();
        },
        scope:this
    }
  ];
    
  this.addEvents({'saveSuccess' : true});  
  
  Toc.countries.CountriesDialog.superclass.constructor.call(this, config);
};
  
Ext.extend(Toc.countries.CountriesDialog, Ext.Window,{

  show: function(id) {
    var countriesId = id || null;
    
    this.frmCountry.form.reset();
    this.frmCountry.form.baseParams['countries_id'] = countriesId;
    
    if (countriesId > 0) {
      this.frmCountry.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'countries',
          action: 'load_country'
        },
        success: function(form, action){
          Toc.countries.CountriesDialog.superclass.show.call(this);
        },
        failure: function(form, action){
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this  
      });
    } else {
      Toc.countries.CountriesDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmCountry = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'countries',
        action: 'save_country'
      },
      defaults: {
          anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      }, 
      items: [
        {xtype: 'hidden',name: 'id'},
        {xtype: 'textfield',fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', name: 'countries_name', allowBlank: false},
        {xtype: 'textfield',fieldLabel: '<?php echo $osC_Language->get('field_iso_code_2'); ?>', name: 'countries_iso_code_2', allowBlank: false},
        {xtype: 'textfield',fieldLabel: '<?php echo $osC_Language->get('field_iso_code_3'); ?>', name: 'countries_iso_code_3', allowBlank: false},
        {xtype: 'textarea',fieldLabel: '<?php echo $osC_Language->get('field_address_format'); ?>', name: 'address_format'}
      ]
    });
    
    return this.frmCountry;
  },
  
  submitForm : function() {
    this.frmCountry.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, scope: this
    });   
  }
});