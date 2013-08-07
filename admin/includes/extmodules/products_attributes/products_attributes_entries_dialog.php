<?php
/*
  $Id: products_attributes_entries_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products_attributes.AttributeEntriesDialog = function(config) {

  config = config || {}; 
  
  config.id = 'products_attributes_entries-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_group_entry'); ?>';
  config.width = 500;
  config.modal = true;
  config.iconCls = 'icon-zones-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];
  
  this.addEvents({'saveSuccess' : true});  
  
  Toc.products_attributes.AttributeEntriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products_attributes.AttributeEntriesDialog, Ext.Window, {  
  show: function (groupsId, valuesId) {
    var attributesGroupsId = groupsId;
    var attributesValuesId = valuesId || null; 
     
    this.frmAttributeEntry.form.reset();
    this.frmAttributeEntry.form.baseParams['products_attributes_groups_id'] = attributesGroupsId;
    this.frmAttributeEntry.form.baseParams['products_attributes_values_id'] = attributesValuesId;

    if (attributesValuesId > 0) {
      this.frmAttributeEntry.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'products_attributes',
          action: 'load_products_attributes_entries',
          products_attributes_values_id: attributesValuesId
        },
        success: function(form, action) {
          if (action.result.data.attribute_module == 'text_field') {
            <?php
              foreach ($osC_Language->getAll() as $l) {
                echo 'this.txtLangValue' . $l['id'] . '.allowBlank = true;';
                echo 'this.txtLangValue' . $l['id'] . '.reset();';
                echo 'this.txtLangValue' . $l['id'] . '.disable();';
              }
            ?>
          } else {
            <?php
              foreach ($osC_Language->getAll() as $l) {
                echo 'this.txtLangValue' . $l['id'] . '.allowBlank = false;';
                echo 'this.txtLangValue' . $l['id'] . '.enable();';
              }
            ?>
          }
          
          Toc.products_attributes.AttributeEntriesDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        }, scope: this       
      });
    } else {   
      Toc.products_attributes.AttributeEntriesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.cboEntryModule = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_group_entry_type'); ?>', 
      store: new Ext.data.SimpleStore({
        fields: ['id', 'text'],
        data: [
          ['pull_down_menu','pull_down_menu'],
          ['text_field','text_field']
        ]                                                                                    
      }), 
      displayField: 'text', 
      valueField: 'id', 
      hiddenName: 'attribute_module', 
      mode: 'local',
      triggerAction: 'all', 
      allowBlank: false,
      readOnly: true,
      editable: false,
      listeners: {
        select: function(select, record) {
          if (record.get('id') == 'text_field') {
            <?php
              foreach ($osC_Language->getAll() as $l) {
                echo 'this.txtLangValue' . $l['id'] . '.allowBlank = true;';
                echo 'this.txtLangValue' . $l['id'] . '.reset();';
                echo 'this.txtLangValue' . $l['id'] . '.disable();';
              }
            ?>
          } else {
            <?php
              foreach ($osC_Language->getAll() as $l) {
                echo 'this.txtLangValue' . $l['id'] . '.allowBlank = false;';
                echo 'this.txtLangValue' . $l['id'] . '.enable();';
              }
            ?>
          }
        },
        scope: this
      }
    });
    
    this.frmAttributeEntry = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        'module' : 'products_attributes',
        'action' : 'save_products_attributes_entries'
      }, 
      labelWidth: 120,
      defaults: {
          anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      }
    });
    <?php
      $i = 1;
      foreach ($osC_Language->getAll() as $l) {
        echo 'var txtLangName' . $l['id'] . ' = new Ext.form.TextField({name: \'name[' . $l['id'] . ']\',';
        if ($i != 1) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_group_entry_name') . '", ';
          
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        
        echo 'this.frmAttributeEntry.add(txtLangName' . $l['id'] . ');';
        
        $i++;
      }
    ?>
    
    var pnlPublish = {
      layout: 'column',
      border: false,
      items: [
        {
          width: 200,
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          items: [
            {
              xtype: 'radio', 
              name: 'status', 
              fieldLabel: '<?php echo $osC_Language->get('field_group_entry_status'); ?>', 
              inputValue: '1', 
              boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>', 
              checked: true,
              anchor: ''
            }
          ]
        },
        {
          layout: 'form',
          width: 150,
          border: false,
          items: [
            {
              xtype: 'radio', 
              hideLabel: true, 
              name: 'status', 
              inputValue: '0', 
              boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>'
            }
          ]
        }
      ]
    };
    
    this.frmAttributeEntry.add(pnlPublish);
    this.frmAttributeEntry.add(this.cboEntryModule);
    
    <?php
      $i = 1;
      foreach ($osC_Language->getAll() as $l) {
        echo 'this.txtLangValue' . $l['id'] . ' = new Ext.form.TextField({name: \'value[' . $l['id'] . ']\',';
        
        if ($i != 1) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_group_entry_value') . '", ';
          
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        
        echo 'this.frmAttributeEntry.add(this.txtLangValue' . $l['id'] . ');';
        $i++;
      }
    ?>
    this.frmAttributeEntry.add({xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_group_sort_order'); ?>', name: 'sort_order', allowBlank: false, labelSeparator: '', anchor: '98%'});
    
    return this.frmAttributeEntry;
  },
  
  submitForm : function() {
    this.frmAttributeEntry.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function(form, action) {
        if (action.failureType != 'client') {         
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);      
        }         
      },
      scope: this
    });   
  }
});