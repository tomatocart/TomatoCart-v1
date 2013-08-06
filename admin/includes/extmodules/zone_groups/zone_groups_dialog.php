<?php
/*
  $Id: zone_grous_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.zone_groups.ZoneGroupsDialog = function (config) {

	config = config || {};
  
	config.id = 'zone_groups-dialog-win';
	config.title = '<?php echo $osC_Language->get("action_heading_new_zone_group"); ?>';
  config.width = 440;
  config.modal = true;
	config.iconCls = 'icon-zone_groups-win';
  config.items = this.buildForm();
  
  config.buttons = [
	  {
      text: TocLanguage.btnSave,
      handler: function () {
  	    this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
	      this.close();
      },
      scope: this
    }
  ];
  
	this.addEvents({'saveSuccess': true});
  
	Toc.zone_groups.ZoneGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.zone_groups.ZoneGroupsDialog, Ext.Window, {
  
	show: function (id) {
		var geoZoneId = id || null;
			
		this.frmZoneGroup.form.reset();
		this.frmZoneGroup.form.baseParams['geo_zone_id'] = geoZoneId;
    
		if (geoZoneId > 0) {
			this.frmZoneGroup.load({
				url: Toc.CONF.CONN_URL,
        params: {
          module: 'zone_groups',
          action: 'load_zone_group'
        },      				
				success: function (form, action) {
					Toc.zone_groups.ZoneGroupsDialog.superclass.show.call(this);
				},
				failure: function (form, action) {
					Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				},
				scope: this
			});
		} else {
			Toc.zone_groups.ZoneGroupsDialog.superclass.show.call(this);
		}
	},
  
	buildForm: function () {
		this.frmZoneGroup = new Ext.form.FormPanel({
			url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'zone_groups',
        action: 'save_zone_group'
      },			
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {
          xtype: 'textfield',
          fieldLabel: '<?php echo $osC_Language->get("field_name"); ?>',
          name: 'geo_zone_name',
          allowBlank: false
        }, 
        {
          xtype: 'textfield',
          fieldLabel: '<?php echo $osC_Language->get("field_description"); ?>',
          name: 'geo_zone_description'
        }
      ]
    });
    
    return this.frmZoneGroup;
  },
  
  submitForm: function () {
    this.frmZoneGroup.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
}
);