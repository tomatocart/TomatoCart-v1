<?php
/*
  $Id: attributes_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.AttributesPanel = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_attributes'); ?>';
  config.layout = 'form';
  config.border = false;
  
  config.productsId = config.productsId || null;
  this.buildForm(config.productsId);
  
  this.cboAttributeGroups = new Ext.form.ComboBox({
    name: 'attributeGroups',
    hiddenName: 'products_attributes_groups_id',
    store: new Ext.data.Store({
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_attribute_groups'
      },
      autoLoad: true
    }),
    displayField: 'text',
    valueField: 'id',
    triggerAction: 'all',
    selectOnFocus: true,
    editable: false,
    emptyText: '<?php echo $osC_Language->get('parameter_none'); ?>',
    width: 300,
    listeners: {
      select: this.onAttributeGroupsChange,
      scope: this
    }    
  });
  config.tbar = [this.cboAttributeGroups];

  Toc.products.AttributesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.AttributesPanel, Ext.Panel, {
 
  updateAttributesPnl: function(attributes) {
    Ext.each(attributes, function(attribute, i){
      if (attribute.module == 'pull_down_menu') {
        var data = [];
        var values = attribute.value.split(',');

        Ext.each(values, function(value, i) {
          data.push([(i+1), value]);
        });
      
        var cboAttribute = new Ext.form.ComboBox({
          name: 'products_attributes_select_' + attribute.products_attributes_values_id,
          fieldLabel: attribute.name,
          labelStyle: 'padding-left: 10px',
          hiddenName: 'products_attributes_select[' + attribute.products_attributes_values_id + ']',
          store: new Ext.data.SimpleStore({
            fields: ['id', 'text'],
            data : data
          }),
          mode: 'local',
          displayField: 'text',
          valueField: 'id',
          triggerAction: 'all',
          readOnly: true,
          width: 200,
          allowblank: false,
          value: attribute.choosed_value
        });
        
        this.add(cboAttribute);
      } else if (attribute.module == 'text_field') {
        Ext.each(Toc.Languages, function(l, i){
          var txtField = new Ext.form.TextField({
            name: attribute.products_attributes_values_id,
            name: 'products_attributes_text[' + attribute.products_attributes_values_id + '][' + l.id + ']',
            value: attribute.lang_values[l.id],
            fieldLabel: ((i == 0) ? (attribute.name + ':') : '&nbsp;'),
            labelStyle: 'background: url(../images/worldflags/' + l.country_iso + '.png) no-repeat right center !important; padding-left: 10px',
            labelSeparator: ' ',
            width: 200
          });
          
          this.add(txtField);
        }, this);
      }
    }, this);
    
    this.doLayout();
  },
    
  buildForm: function(productsId) {
    if (productsId != null) {
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL, 
        params: { 
          module: 'products',
          action: 'get_attributes',
          products_id: productsId
        },
        callback: function(options, success, response) {
          if (success == true) {
            var result = Ext.decode(response.responseText);
            if (result.success == true) {
              this.cboAttributeGroups.setRawValue(result.products_attributes_groups_id);
              this.updateAttributesPnl(result.attributes);
            }
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrTitle);
          }
        },
        scope: this
      });
    }
  }, 
  
  setAttributesGroupsId: function(products_attributes_groups_id) {
    this.cboAttributeGroups.setValue(products_attributes_groups_id);
  },
  
  
  onAttributeGroupsChange: function() {
    this.items.each(function(item){
      var el = item.el.up('.x-form-item');
      this.remove(item, true);
      el.remove();
    }, this);
    this.doLayout();

		var groupsId = this.cboAttributeGroups.getValue();
  	if(groupsId != '0') {
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL, 
        params: { 
          module: 'products',
          action: 'get_attributes',
          products_attributes_groups_id: groupsId
        },
        callback: function(options, success, response) {
          if (success == true) {
            var result = Ext.decode(response.responseText);
            
            if (result.success == true) {
              this.updateAttributesPnl(result.attributes);
            }
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrTitle);
          }
        },
        scope: this
      });
  	}
  }
});