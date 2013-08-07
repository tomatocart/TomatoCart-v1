<?php
/*
  $Id: countries_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.countries.MainPanel = function(config) {

  config = config || {};
      
  config.layout = 'border';
  
  config.grdCountries = new Toc.countries.CountriesGrid({owner: config.owner});
  config.grdZones = new Toc.countries.ZonesGrid({owner: config.owner});
  
  config.grdCountries.on('selectchange', this.onGrdCountriesSelectChange, this);
  config.grdCountries.getStore().on('load', this.onGrdCountriesLoad, this);
  config.grdZones.getStore().on('load', this.onGrdZonesLoad, this);
  
  config.items = [config.grdCountries, config.grdZones];

  Toc.countries.MainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.countries.MainPanel, Ext.Panel, {

  onGrdCountriesLoad: function() {
    if (this.grdCountries.getStore().getCount() > 0) {
      this.grdCountries.getSelectionModel().selectFirstRow();
      record = this.grdCountries.getStore().getAt(0);
      
      this.onGrdCountriesSelectChange(record);
    } else {
      this.grdZones.reset();
    }
  },
  
  onGrdCountriesSelectChange: function(record) {
    this.grdZones.setTitle('<?php echo $osC_Language->get('heading_title'); ?>: '+ record.get('countries_name'));
    this.grdZones.iniGrid(record);
  },
  
  onGrdZonesLoad: function() {
    record = this.grdCountries.getSelectionModel().getSelected() || null;
    if (record) {
      record.set('total_zones', this.grdZones.getStore().getCount());
      this.grdCountries.getStore().commitChanges();
    }
  }
});
