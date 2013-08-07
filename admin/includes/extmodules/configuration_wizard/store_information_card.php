<?php
/*
  $Id: configuration_wizard_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.configuration_wizard.StoreInformationCard = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('store_information_header_title'); ?>';
  config.description = '<?php echo $osC_Language->get('store_information_header_description'); ?>';
  config.labelWidth = 150;
  config.monitorValid = true;
  config.defaults = {
    xtype: 'textfield',
    allowBlank: false,
    labelStyle: 'font-size: 11px',
    anchor: '97%'
  };
  
  config.cboCountries = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_country'); ?>',
    name: 'STORE_COUNTRY',
    hiddenName: 'STORE_COUNTRY',
    editable: false,
    triggerAction: 'all',
    displayField: 'text',
    valueField: 'id',
    store: new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module:'configuration_wizard',
        action:'get_countries'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true
    }),
    listeners: {
      select: this.onCountriesSelect,
      scope: this
    }
  });
  
  config.cboZones = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_zone'); ?>',
    name: 'STORE_ZONE',
    hiddenName: 'STORE_ZONE',
    triggerAction: 'all',
    editable: false,
    displayField: 'text',
    valueField: 'id',
    mode: 'local',
    store: new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'configuration_wizard',
        action: 'get_zones'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      })
    })
  });
  
  config.items = [
    {name: 'STORE_NAME', fieldLabel: '<?php echo $osC_Language->get('field_store_name'); ?>'},
    {name: 'STORE_OWNER', fieldLabel: '<?php echo $osC_Language->get('field_store_owner'); ?>'},
    config.cboCountries,
    config.cboZones,
    {name: 'TAX_DECIMAL_PLACES', fieldLabel: '<?php echo $osC_Language->get('field_tax_decimal_places'); ?>'},
    {name: 'INVOICE_START_NUMBER', fieldLabel: '<?php echo $osC_Language->get('field_invoice_start_number'); ?>'},
    {name: 'STORE_NAME_ADDRESS', xtype: 'textarea', fieldLabel: '<?php echo $osC_Language->get('field_address_and_phone'); ?>'}
  ];
  
  Ext.ux.Wiz.Card.superclass.constructor.call(this, config);
};

Ext.extend(Toc.configuration_wizard.StoreInformationCard, Ext.ux.Wiz.Card, {
  show: function() {
    this.onCardShow();
    
    Toc.configuration_wizard.StoreInformationCard.superclass.show.call(this);
  },

  onCountriesSelect: function() {
    this.cboZones.setValue('');
    this.cboZones.getStore().baseParams['countries_id'] = this.cboCountries.getValue() || null;  
    this.cboZones.getStore().reload();
  }
});
