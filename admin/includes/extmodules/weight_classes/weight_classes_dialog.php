<?php
/*
  $Id: weight_classes_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.weight_classes.WeightClassesDialog = function (config) {

  config = config || {};
  
	config.id = 'weight_classes-dialog-win';
	config.title = '<?php echo $osC_Language->get("action_heading_new_weight_class"); ?>';
	config.layout = 'fit';
	config.width = 480;
	config.height = 360;
	config.modal = true;
	config.iconCls = 'icon-weight_classes-win';
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
  
	this.addEvents({ 'saveSuccess': true });
  
	Toc.weight_classes.WeightClassesDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.weight_classes.WeightClassesDialog, Ext.Window, {

	show: function (id) {
    var weightClassesId = id || null;
    
		this.frmWeightClass.form.reset();
    this.frmWeightClass.form.baseParams['weight_class_id'] = weightClassesId;
    
    if (weightClassesId > 0) {
  		this.frmWeightClass.load({
  			url: Toc.CONF.CONN_URL,
  			params: {
  				action: 'load_weight_classes',
          weight_class_id: weightClassesId
  			},
  			success: function (form, action) {
          var rules = action.result.data.rules;
          
          for (var i=0 ; i < rules.length ; i++){
            this.frmWeightClass.add({
              xtype: 'numberfield',
              fieldLabel: rules[i].weight_class_title,
              name: 'rules[' + rules[i].weight_class_id + ']',
              value: rules[i].weight_class_rule
            });
          }
          
          if (!action.result.data.is_default) {    
            this.frmWeightClass.add({
              xtype: 'checkbox',
              name: 'is_default',
              fieldLabel: '<?php echo $osC_Language->get("field_set_as_default"); ?>'
            });
          }
          
          this.doLayout();
          
  				Toc.weight_classes.WeightClassesDialog.superclass.show.call(this);
  			},
  			failure: function (form, action) {
  				Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
  			},
  			scope: this
  		});
    } else {
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'weight_classes',
          action: 'get_weight_classes_rules'
        },
        callback: function(options, success, response) {
          var result = Ext.decode(response.responseText);
          var rules = result.rules;
          
          for (var i = 0; i < rules.length; i++) {
            this.frmWeightClass.add({
              xtype: 'numberfield',
              name: 'rules[' + rules[i].weight_class_id + ']',
              fieldLabel: rules[i].weight_class_title,
              value: rules[i].weight_class_rule
            });
          }
          
          this.frmWeightClass.add({
            xtype: 'checkbox',
            name: 'default',
            fieldLabel: '<?php echo $osC_Language->get("field_set_as_default"); ?>',
            anchor: ''
          });
          
          this.doLayout();
          
          Toc.weight_classes.WeightClassesDialog.superclass.show.call(this);
        },
        scope: this
      });
    }
	},
	
  
	buildForm: function () {
		this.frmWeightClass = new Ext.form.FormPanel({
			url: Toc.CONF.CONN_URL,
			baseParams: {
				module: 'weight_classes',
        action: 'save_weight_classes'				
			},
      autoScroll: true,
      defaults: {
        anchor: '95%'
      },
			layoutConfig: { 
			  labelSeparator: '' 
			}
	  });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        $fieldLabel = 'fieldLabel: ' . (($i == 1) ? '"' . $osC_Language->get('field_title_and_code') . '"' : '"&nbsp;"');
          
        echo 'var lang' . $l['id'] . ' = { 
          id: "la' . $i . '", 
          layout: "column", 
          border: false, 
          items: [
            {
              width: 210,
              layout: "form", 
              labelSeparator: " ", 
              border: false, 
              items: [
                {
                  xtype: "textfield", 
                  name: "name[' . $l['id'] . ']",
                  labelStyle: "background: url(../images/worldflags/' . $l['country_iso'] . '.png) no-repeat right center !important",
                  width: 100,
                  allowBlank: false,' . 
                  $fieldLabel . '
                }
              ]
            },
            {
              layout: "form",
              border: false,
              items: {xtype: "textfield", name: "key[' . $l['id'] . ']",  width: 100, allowBlank: false, hideLabel: true}
            }
          ]};';
                  
        echo 'this.frmWeightClass.add(lang' . $l['id'] . ');';
        $i++;
      }     
    ?>
    
    this.frmWeightClass.add({
      xtype: 'statictextfield',
      border: false,
      fieldLabel: '<?php echo $osC_Language->get("field_rules"); ?>',
      value: ''
    });
    
    return this.frmWeightClass;
	},
  
	submitForm: function () {
		this.frmWeightClass.form.submit({
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
});