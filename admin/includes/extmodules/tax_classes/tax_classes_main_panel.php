<?php
/*
  $Id: tax_classes_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tax_classes.TaxClassesMainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';
  
  config.grdTaxClasses = new Toc.tax_classes.TaxClassesGrid({owner : config.owner});
  config.grdTaxRates = new Toc.tax_classes.TaxRatesGrid({owner: config.owner}); 
  
  config.grdTaxClasses.on('selectchange', this.onGrdTaxClassesSelectChange, this);
  config.grdTaxClasses.getStore().on('load', this.onGrdTaxClassesLoad, this);
  config.grdTaxRates.getStore().on('load', this.onGrdTaxRatesLoad, this);
      
  config.items = [config.grdTaxClasses, config.grdTaxRates];  
  
  Toc.tax_classes.TaxClassesMainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.tax_classes.TaxClassesMainPanel, Ext.Panel, {   

  onGrdTaxClassesLoad: function() {
    if (this.grdTaxClasses.getStore().getCount() > 0) {
        this.grdTaxClasses.getSelectionModel().selectFirstRow();
        record = this.grdTaxClasses.getStore().getAt(0);
        
        this.onGrdTaxClassesSelectChange(record);
    } else {
      this.grdTaxRates.reset();
    }
  },
  
  onGrdTaxClassesSelectChange: function(record) {
    this.grdTaxRates.setTitle('<?php echo $osC_Language->get('heading_title'); ?>: '+ record.get('tax_class_title'));
    this.grdTaxRates.iniGrid(record);
  },

  onGrdTaxRatesLoad: function() {
    record = this.grdTaxClasses.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('tax_total_rates', this.grdTaxRates.getStore().getCount());
      this.grdTaxClasses.getStore().commitChanges();
    }
  }
});