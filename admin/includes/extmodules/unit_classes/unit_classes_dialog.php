<?php
/*
  $Id: unit_classes_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.unit_classes.UnitClassesDialog = function (config) {
  config = config || {};
  
  config.id = 'unit_classes-dialog-win';
  config.layout = 'fit';
  config.width = 400;
  config.height = 200;
  config.modal = true;
  config.iconCls = 'icon-unit_classes-win';
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
  
  Toc.unit_classes.UnitClassesDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.unit_classes.UnitClassesDialog, Ext.Window, {
  
  show: function (unit_class_id) {
    var unit_class_id = unit_class_id || null;

    this.frmUnitClasses.form.reset();
    this.frmUnitClasses.form.baseParams['unit_class_id'] = unit_class_id;
    
    if (unit_class_id > 0) {
      this.frmUnitClasses.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_unit_class'
        },
        success: function(form, action) {
          if (!action.result.data.is_default) {    
            this.frmUnitClasses.add({xtype: 'checkbox', name: 'default', id:'default_unit_classess', fieldLabel: '<?php echo $osC_Language->get('field_is_default_unit'); ?>', anchor:''});
          }
          
          Toc.unit_classes.UnitClassesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this       
      });
    } else {   
      this.frmUnitClasses.add({xtype: 'checkbox', name: 'default', id:'default_unit_classess', fieldLabel: '<?php echo $osC_Language->get('field_is_default_unit'); ?>', anchor:''});    
      
      Toc.unit_classes.UnitClassesDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function () {
    this.frmUnitClasses = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'unit_classes',
        action: 'save_unit_class'
      },
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150
      });
   <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: \'unit_class_title[' . $l['id'] . ']\',';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_unit_class_name') . '", ';
        echo "allowBlank: false,";
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        echo 'this.frmUnitClasses.add(txtLang' . $l['id'] . ');';
        $i++;
      }
    ?>
    
    return this.frmUnitClasses; 
  },
  
  submitForm: function () {
    this.frmUnitClasses.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback)
        }
      },
      scope: this
    });
  }
});