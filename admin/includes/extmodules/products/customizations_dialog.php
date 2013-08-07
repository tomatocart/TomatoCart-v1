<?php
/*
  $Id: cutomizations_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.CustomizationsDialog = function(config) {
  config = config || {};
  
  config.id = 'customization_fields_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_customization') ?>';
  config.width = 400;
  config.iconCls = 'icon-products-win';
  this.owner = config.owner;
  this.row = -1;

  config.items = this.buildForm();
  
  config.buttons = [{
    text: TocLanguage.btnSave,
    handler: function() {
      this.submitForm();
    },
    scope: this
  }, {
    text: TocLanguage.btnClose,
    handler: function() { 
      this.close();
    },
    scope: this
  }];
  
  Toc.products.CustomizationsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.CustomizationsDialog, Ext.Window, {
  show: function (row, record) {
    var record = record || null;
    
    if (record != null) {
      this.row = row;
      this.record = record;
      
      this.cboTypes.setValue(record.get('customization_type'));
      if (record.get('is_required')) {
        this.rdbRequiredYes.setValue(true);
        this.rdbRequiredNo.setValue(false);
      } else {
        this.rdbRequiredYes.setValue(false);
        this.rdbRequiredNo.setValue(true);
      }
      
      var data = Ext.decode(record.get('name_data'));
      <?php
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'this.txtName' . $l['id'] . '.setValue(data.name' . $l['id'] . ');';
        }
      ?>
      
      Toc.products.CustomizationsDialog.superclass.show.call(this);
    } else {
      Toc.products.CustomizationsDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.cboTypes = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_customization_type'); ?>', 
      store: new Ext.data.SimpleStore({
        fields: ['id', 'text'],
        data: [
          ['1', '<?php echo $osC_Language->get('field_customization_type_text'); ?>'],
          ['0', '<?php echo $osC_Language->get('field_customization_type_file'); ?>']
        ]
      }), 
      displayField: 'text', 
      valueField: 'id', 
      name: 'type',
      hiddenName: 'type', 
      readOnly: true, 
      forceSelection: true,
      mode: 'local',
      value: '1',
      triggerAction: 'all'
    });
  
    this.frmCustomization = new Ext.form.FormPanel({
      border: false,
      url: Toc.CONF.CONN_URL,
      defaults: {
        anchor: '98%'
      },
      style: 'padding: 8px',
      border: false,
      labelWidth: 120,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.cboTypes,
        <?php
        $i = 1;
        
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'this.txtName' . $l['id'] . ' = new Ext.form.TextField({';
            if ($i == 1)
              echo 'fieldLabel: "' . $osC_Language->get('field_customization_name') . '",';
            else 
              echo 'fieldLabel: "&nbsp;",';
            echo 'name: "name[' . $l['id'] . ']",';
            echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;', "; 
            echo "allowBlank: false";
          echo "}),";
          
          $i++;
        }
        ?>
        {
          layout: 'column',
          border: false,
          items:[
            {
              layout: 'form',
              labelSeparator: ' ',
              labelWidth: 120,
              border: false,
              items: [
                this.rdbRequiredYes = new Ext.form.Radio({
                  fieldLabel: '<?php echo $osC_Language->get('field_customization_required'); ?>', 
                  name: 'is_required', 
                  boxLabel: '<?php echo $osC_Language->get('parameter_yes'); ?>', 
                  xtype:'radio', 
                  inputValue: '1' 
                })
              ]
            }, 
            {
              layout: 'form',
              border: false,
              items: [
                this.rdbRequiredNo = new Ext.form.Radio({
                  boxLabel: '<?php echo $osC_Language->get('parameter_no'); ?>', 
                  xtype:'radio', 
                  name: 'is_required', 
                  hideLabel: true, 
                  inputValue: '0',
                  checked: true
                })
              ]
            }
          ]
        }
      ]
    });
    
    return this.frmCustomization;
  },
  
  submitForm: function() {
    var data = {};
    var error = false;
    var name = '';
    
    <?php
    foreach ( $osC_Language->getAll() as $l ) {
      echo 'data.name' . $l['id'] . ' = this.txtName' . $l['id'] . '.getValue();';
      echo 'if(Ext.isEmpty(data.name' . $l['id'] . ')) { 
              error = true;
            }';

      if($osC_Language->getID() == $l['id']) {
        echo 'name = this.txtName' . $l['id'] . '.getValue();';
      }
    }
    ?>
    
    if (error == false) {
      var type = this.cboTypes.getValue();
      var required = (this.rdbRequiredYes.getValue() == true) ? true : false;

      this.owner.onChange(this.row, type, required, name, data); 
      this.close();
    }
  }
});