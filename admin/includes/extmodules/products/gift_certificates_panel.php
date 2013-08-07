<?php
/*
  $Id: gift_certificates_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.GiftCertificatesPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_gift_certificates'); ?>';
  config.layout = 'form';
  config.labelSeparator = ' ';
  config.style = 'padding: 10px';
  config.labelWidth = 180;
  
  config.items = this.buildForm();
  
  Toc.products.GiftCertificatesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.GiftCertificatesPanel, Ext.Panel, {
  buildForm: function() {
    this.rdoEmail = new Ext.form.Radio({
      fieldLabel: '<?php echo $osC_Language->get('field_gift_certificates_type'); ?>', 
      name: 'gift_certificates_type', 
      boxLabel: '<?php echo $osC_Language->get('gift_certificate_type_email'); ?>', 
      inputValue: '0', 
      checked: true
    });    
    
    this.rdoPhysical = new Ext.form.Radio({
      fieldLabel: ' ', 
      name: 'gift_certificates_type', 
      boxLabel: '<?php echo $osC_Language->get('gift_certificate_type_physical'); ?>', 
      inputValue: '1',
      listeners: {
        check: function(rdo, checked) {
          if (checked == true) {
            this.onCertificateTypeChange('1');
          } else {
            this.onCertificateTypeChange('0');          
          }
        },
        scope: this
      }
    });
    
    this.rdoFixAmount = new Ext.form.Radio({
      fieldLabel: '<?php echo $osC_Language->get('field_gift_certificates_amount_type'); ?>', 
      name: 'gift_certificates_amount_type', 
      boxLabel: '<?php echo $osC_Language->get('gift_certificate_type_fix_amount'); ?>', 
      inputValue: '0', 
      checked: true
    });    
    
    this.rdoOpenAmount = new Ext.form.Radio({
      fieldLabel: ' ', 
      name: 'gift_certificates_amount_type', 
      boxLabel: '<?php echo $osC_Language->get('gift_certificate_type_open_amount'); ?>', 
      inputValue: '1',
      listeners: {
        check: function(rdo, checked) {
          if (checked == true) {
            this.onCertificateAmountTypeChange('1');
          } else {
            this.onCertificateAmountTypeChange('0');          
          }
        },
        scope: this
      }
    });
    
    this.txtMinValue = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_open_amount_min_value'); ?>', 
      name: 'open_amount_min_value', 
      width: 180
    });
    
    this.txtMaxValue = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_open_amount_max_value'); ?>', 
      name: 'open_amount_max_value', 
      width: 180
    });
    
    this.txtMinValue.disable();
    this.txtMaxValue.disable();    
    
    return [this.rdoEmail, this.rdoPhysical, this.rdoFixAmount, this.rdoOpenAmount, this.txtMinValue, this.txtMaxValue];
  },
  
  onCertificateTypeChange: function(type) {
    if(type == '0') {
      this.rdoPhysical.setValue(false);
      this.rdoEmail.setValue(true);
    } else {
      this.rdoPhysical.setValue(true);
      this.rdoEmail.setValue(false);
    }
  },
    
  onCertificateAmountTypeChange: function(type) {
    if(type == '0') {
      this.rdoOpenAmount.setValue(false);
      this.rdoFixAmount.setValue(true);
          
      this.txtMinValue.setValue('');
      this.txtMaxValue.setValue('');  
      this.txtMinValue.disable();
      this.txtMaxValue.disable();  
      
      this.owner.txtPriceNet.enable();
      this.owner.txtPriceGross.enable();        
    } else {
      this.rdoOpenAmount.setValue(true);
      this.rdoFixAmount.setValue(false);
          
      this.txtMinValue.enable();
      this.txtMaxValue.enable();
      
      this.owner.txtPriceNet.disable();
      this.owner.txtPriceGross.disable();
    }
  },
  
  loadForm: function(data) {
    this.onCertificateTypeChange(data.gift_certificates_type);
    this.onCertificateAmountTypeChange(data.gift_certificates_amount_type);
    
    if (data.gift_certificates_amount_type == '1') {
      this.txtMinValue.setValue(data.open_amount_min_value);
      this.txtMaxValue.setValue(data.open_amount_max_value);
    } 
  }
});