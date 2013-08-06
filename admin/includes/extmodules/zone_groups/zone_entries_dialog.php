<?php
/*
  $Id: zone_entries_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.zone_groups.ZoneEntriesDialog = function(config) {

  config = config || {};
  
  config.id = 'zone_entries-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_zone_group"); ?>';
  config.width = 440;
  config.modal = true;
  config.iconCls = 'icon-zone_groups-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.zone_groups.ZoneEntriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.zone_groups.ZoneEntriesDialog, Ext.Window, {

  show: function (geoZoneId, entryId) {
    this.geoZoneId = geoZoneId || null;
    var geoZoneEntryId = entryId || null;  
        
    this.frmZoneEntry.form.reset();  
    this.frmZoneEntry.form.baseParams['geo_zone_id'] = this.geoZoneId;
    this.frmZoneEntry.form.baseParams['geo_zone_entry_id'] = geoZoneEntryId;

    if (geoZoneEntryId > 0) {
      this.frmZoneEntry.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'zone_groups',
          action: 'load_zone_entry',
          geo_zone_entry_id: geoZoneEntryId
        },
        success: function (form, action) {
          this.cboCountries.setValue(action.result.data.zone_country_id);
          this.cboCountries.setRawValue(action.result.data.countries_name);
          this.updateCboZones(action.result.data.zone_id);
          
          Toc.zone_groups.ZoneEntriesDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.zone_groups.ZoneEntriesDialog.superclass.show.call(this);
    }  
  },
  
  buildForm: function() {
    dsCountries = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'zone_groups',
        action: 'list_countries'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'countries_id', 
          'countries_name'
        ]
      }),
      autoLoad: true
    });
    
    this.cboCountries = new Ext.form.ComboBox({
      name: 'countries',
      store: dsCountries,
      fieldLabel: '<?php echo $osC_Language->get("field_country"); ?>',
      valueField: 'countries_id',
      displayField: 'countries_name',
      hiddenName: 'countries_id',
      triggerAction: 'all',
      readOnly: true,
      allowBlank: false,
      listeners: {
        select: this.onCboCountriesSelect,
        scope: this
      }
    });
     
    dsZones = new Ext.data.Store({ 
      url: Toc.CONF.CONN_URL,  
      baseParams: {
        module: 'zone_groups',
        action: 'list_zones'
      },
      reader: new Ext.data.JsonReader({  
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'zone_id', 
          'zone_name'
        ]
      })
    });  
     
    this.cboZones = new Ext.form.ComboBox({  
      name: 'zones',
      store: dsZones,  
      fieldLabel: '<?php echo $osC_Language->get("field_zone"); ?>',  
      valueField: 'zone_id',  
      displayField: 'zone_name',  
      hiddenName: 'zone_id',  
      triggerAction: 'all',  
      disabled: true,
      allowBlank: false,
      readOnly: true
    });
                           
    this.frmZoneEntry = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'zone_groups',
        action: 'save_zone_entry'
      }, 
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [this.cboCountries, this.cboZones]
    });
     
    return this.frmZoneEntry;
  },
  
  updateCboZones: function(zoneId) {
    this.cboZones.reset();
    this.cboZones.enable();  
    this.cboZones.getStore().baseParams['countries_id'] = this.cboCountries.getValue();  
    
    if(zoneId) {
      this.cboZones.getStore().on('load', function(){
        this.cboZones.setValue(zoneId);
      }, this);
    }
    
    this.cboZones.getStore().load();
  },
  
  onCboCountriesSelect: function() {
    this.updateCboZones();
  },

  submitForm: function() {
    this.frmZoneEntry.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client'){
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});