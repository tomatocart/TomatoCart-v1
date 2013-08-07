<?php
/*
  $Id: stroe_inforamtion_card.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.configuration_wizard.ShippingAndPackagingCard = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('shipping_packaging_field_header_title'); ?>';
  config.description = '<?php echo $osC_Language->get('shipping_packaging_field_header_description'); ?>';
  config.labelWidth = 150;
  config.monitorValid = true;
  config.defaults = {
    allowBlank: false,
    labelStyle: 'font-size: 11px',
    anchor: '97%'
  };
  
  config.cboShippingOriginCountry = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_country_of_original'); ?>',
    name: 'SHIPPING_ORIGIN_COUNTRY',
    hiddenName: 'SHIPPING_ORIGIN_COUNTRY',
    triggerAction: 'all',
    editable: false,
    displayField: 'text',
    valueField: 'id',
    mode: 'local',
    store: new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'configuration_wizard',
        action: 'get_countries'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      })
    })
  });
  
  config.cboShippingWeightUnit = new Ext.form.ComboBox({
     fieldLabel: '<?php echo $osC_Language->get('field_country_default_shipping_unit'); ?>',
     name: 'SHIPPING_WEIGHT_UNIT',
     triggerAction: 'all',
     hiddenName: 'SHIPPING_WEIGHT_UNIT',
     editable: false,
     displayField: 'text',
     valueField: 'id',
     mode: 'local',
     store: new Ext.data.Store({
       url: Toc.CONF.CONN_URL,
       baseParams: {
         module: 'configuration_wizard',
         action: 'get_weight_classes'
       },
       reader: new Ext.data.JsonReader ({
         fields: ['id', 'text'],
         root: Toc.CONF.JSON_READER_ROOT
       })
     })
  });
  
  config.items = [
    config.cboShippingOriginCountry,
    {xtype: 'textfield', name: 'SHIPPING_ORIGIN_ZIP', fieldLabel: '<?php echo $osC_Language->get('field_postal_code'); ?>'},
    {xtype: 'textfield', name: 'SHIPPING_MAX_WEIGHT', fieldLabel: '<?php echo $osC_Language->get('field_maxinum_package_weight'); ?>'},
    {xtype: 'textfield', name: 'SHIPPING_BOX_WEIGHT', fieldLabel: '<?php echo $osC_Language->get('field_package_tare_weight'); ?>'},
    {xtype: 'textfield', name: 'SHIPPING_BOX_PADDING', fieldLabel: '<?php echo $osC_Language->get('field_country_percentage_increase'); ?>'},
    config.cboShippingWeightUnit
  ];
  
  Toc.configuration_wizard.ShippingAndPackagingCard.superclass.constructor.call(this, config);
};
 
Ext.extend(Toc.configuration_wizard.ShippingAndPackagingCard, Ext.ux.Wiz.Card, {
  show: function() {
    this.onCardShow();
    
    Toc.configuration_wizard.ShippingAndPackagingCard.superclass.show.call(this);
  }
});
