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

Toc.configuration_wizard.ConfigurationWizardDialog = function (config) {
  config = config || {};
  
  config.id = 'configuration_wizard-win';
  config.iconCls = 'icon-configuration_wizard-win';
  config.height = 460;
  config.width = 520;
  config.resizable = true;
  
  config.headerConfig = {
    title: '<?php echo $osC_Language->get('head_title'); ?>',
    stepText: '{2}',
    height: 80
  };
  
  config.cardPanelConfig = {
    defaults: {
      baseCls: 'x-small-editor',
      bodyStyle : 'padding:15px 20px 15px 80px;background: white url(templates/default/images/wizard_card_bg.png) no-repeat left bottom;', 
      border: false
    }
  };
  
  config.previousButtonText = '<?php echo $osC_Language->get('button_previous'); ?>';
  config.nextButtonText = '<?php echo $osC_Language->get('button_next'); ?>';
  config.cancelButtonText = '<?php echo $osC_Language->get('button_cancel'); ?>';
  config.finishButtonText = '<?php echo $osC_Language->get('button_finish'); ?>';
  
  config.cards = [
    new Toc.configuration_wizard.StoreInformationCard(),
    new Toc.configuration_wizard.EmailOptionsCard(),
    new Toc.configuration_wizard.ShippingAndPackagingCard()
  ];
  
  Toc.configuration_wizard.ConfigurationWizardDialog.superclass.constructor.call(this, config);
};
 
Ext.extend(Toc.configuration_wizard.ConfigurationWizardDialog, Toc.ux.Wiz.Wizard, {
		
  show: function() {
    Ext.Ajax.request ({
      url: Toc.CONF.CONN_URL,
      params: {  
        module: 'configuration_wizard',
        action: 'load_cards_information'
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
         
        if (result.success == true) {
          Ext.each(this.cards, function(card, index) {
            card.form.setValues(result.data);

            if (index == 0) {
              card.onCountriesSelect();
              card.cboCountries.setRawValue(result.data.STORE_COUNTRY_NAME);
              card.cboCountries.getStore().on('load', function(){
                card.cboCountries.setValue(result.data.STORE_COUNTRY);
              });
              
              card.cboZones.setValue(result.data.STORE_ZONE);
              card.cboZones.setRawValue(result.data.STORE_ZONE_NAME);
            } else if (index == 1) {
              card.onEamilTransportMethodSelect();
            } else if (index == 2) {
              card.cboShippingOriginCountry.getStore().on('load', function(){
                card.cboShippingOriginCountry.setValue(result.data.SHIPPING_ORIGIN_COUNTRY);
              });
              card.cboShippingOriginCountry.getStore().load();
              
              card.cboShippingWeightUnit.getStore().on('load', function(){
                card.cboShippingWeightUnit.setValue(result.data.SHIPPING_WEIGHT_UNIT);
              });
              card.cboShippingWeightUnit.getStore().load();
            }
          });
          
          Toc.configuration_wizard.ConfigurationWizardDialog.superclass.show.call(this);
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  onFinish: function() {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {  
        module: 'configuration_wizard',
        action: 'save_wizard',
        data: Ext.encode(this.getWizardData())
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
    this.close();
  }
});