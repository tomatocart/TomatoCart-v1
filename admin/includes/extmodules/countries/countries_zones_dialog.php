<?php
/*
  $Id: countries_zones_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
  
Toc.countries.ZonesDialog = function(config){

  config = config || {};
  
  config.id = 'zones-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_zone'); ?>';
  config.layout = 'fit';
  config.width = 500;
  config.height = 150;
  config.modal = true;
  config.iconCls = 'icon-countries-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
        handler: function() {
          this.submitForm();
        },
        scope: this
      },{
        text: TocLanguage.btnClose,
        handler: function() {
          this.close();
        },
        scope: this
    }
  ];
   
  this.addEvents({'saveSuccess' : true});  
   
  Toc.countries.ZonesDialog.superclass.constructor.call(this, config);
};


Ext.extend(Toc.countries.ZonesDialog, Ext.Window, {
  show: function(countriesId, zId) {
    this.countriesId = countriesId || null;
    var zoneId = zId || null;
    
    this.frmZone.form.reset();
    this.frmZone.form.baseParams['zone_id'] = zoneId;
    this.frmZone.form.baseParams['countries_id'] = countriesId;
    
    if (zoneId > 0) {
      this.frmZone.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'countries',
          action: 'load_zone',
          zone_id: zoneId
        },
        success: function(form, action) {
          Toc.countries.ZonesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this
      });
    } else {
      Toc.countries.ZonesDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmZone = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'countries',
        action: 'save_zone'
      },
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_zone_name'); ?>', name: 'zone_name', allowBlank: false},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_zone_code'); ?>', name: 'zone_code', allowBlank: false}
      ]
    });
    
    return this.frmZone;
  },
  
  submitForm: function() {
    this.frmZone.form.submit({
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