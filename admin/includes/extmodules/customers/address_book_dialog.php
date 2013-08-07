<?php
/*
  $Id: address_book_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.AddressBookDialog = function(config) {
  config = config || {}; 
  
  config.id = 'address-book-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_address_book_entry'); ?>';
  config.modal = true;
  config.width = 500;
  config.iconCls = 'icon-customers-win';
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

  this.addEvents({'saveSuccess' : true});  
  
  Toc.customers.AddressBookDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers.AddressBookDialog, Ext.Window, {  
  show: function (customersId, abId) {
    var addressBookId = abId || null; 

    this.frmAddressBook.form.reset();
    this.frmAddressBook.form.baseParams['customers_id'] = customersId;
    this.frmAddressBook.form.baseParams['address_book_id'] = addressBookId;
   
    if (addressBookId > 0) {
      this.frmAddressBook.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'customers',
          action: 'load_address_book',
          address_book_id: addressBookId
        },
        success: function(form, action) {
          if (action.result.data.primary == true) {
            Ext.getCmp('primary').disable();
          }
          
          this.cboCountries.setRawValue(action.result.data.country_title);

          this.cboZones.enable();
          this.cboZones.getStore().baseParams['country_id'] = action.result.data.country_id;
          onDsZonesLoad = function() {
            this.cboZones.setValue(action.result.data.zone_code);
            this.cboZones.getStore().removeListener('load', onDsZonesLoad, this);
          };
          this.cboZones.getStore().on('load', onDsZonesLoad, this);
          this.cboZones.getStore().load();
          
          Toc.customers.AddressBookDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this       
      });
    } else {   
      Toc.customers.AddressBookDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {

    dsCountries = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'customers', 
        action: 'get_countries'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['country_id', 'country_title']
      }),
      autoLoad: true                                                                                    
    });
    
    this.cboCountries = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_country'); ?>', 
      store: dsCountries, 
      displayField: 'country_title', 
      valueField: 'country_id', 
      name: 'country',
      hiddenName: 'country_id', 
      mode: 'local',
      readOnly: true, 
      triggerAction: 'all', 
      forceSelection: true,
      allowBlank: false,
      listeners :{
        select: this.onCboCountriesSelect,
        scope: this
      } 
    });
    
    dsZone = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'customers', 
        action: 'get_zones'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['zone_code', 'zone_name']
      }),
      autoLoad: false                                                                                    
    });
    
    this.cboZones = new Ext.form.ComboBox({
      store: dsZone, 
      fieldLabel: '<?php echo $osC_Language->get('field_state'); ?>', 
      disabled: true, 
      displayField: 'zone_name', 
      valueField: 'zone_code', 
      hiddenName: 'z_code', 
      triggerAction: 'all', 
      editable: false
    });
    
    this.frmAddressBook = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        'module' : 'customers',
        'action' : 'save_address_book'
      }, 
      defaults: {
        anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150,
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            { 
              width: 220,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '<?php echo $osC_Language->get('field_gender'); ?>', boxLabel: '<?php echo $osC_Language->get('gender_male'); ?>' , name: 'gender', xtype:'radio', inputValue: 'm'}
              ]
            },
            { 
              width: 120,
              layout: 'form',
              border: false,
              items:[
                { hideLabel: true, boxLabel: '<?php echo $osC_Language->get('gender_female'); ?>' , name: 'gender', xtype:'radio', inputValue: 'f', checked: true}
              ]
            }
          ]  
        },
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_first_name'); ?>', name: 'firstname'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_last_name'); ?>', name: 'lastname'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_company'); ?>', name: 'company'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_street_address'); ?>', name: 'street_address'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_suburb'); ?>', name: 'suburb'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_post_code'); ?>', name: 'postcode'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_city'); ?>', name: 'city'},
        this.cboCountries,
        this.cboZones, 
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_telephone_number'); ?>', name: 'telephone_number'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_fax_number'); ?>', name: 'fax_number'},
        {xtype: 'checkbox',  fieldLabel: '<?php echo $osC_Language->get('field_set_as_primary'); ?>', name: 'primary', id: 'primary', anchor: ''}
      ]
    });
    
    return this.frmAddressBook;
  },

  onCboCountriesSelect: function(combo, record, index) {
    this.cboZones.enable();
    this.cboZones.reset();
    this.cboZones.getStore().baseParams['country_id'] = record.get('country_id');
    this.cboZones.getStore().load();
  },
  
  submitForm : function() {
    this.frmAddressBook.form.submit({
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