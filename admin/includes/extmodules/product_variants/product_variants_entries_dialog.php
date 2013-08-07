<?php
/*
  $Id: product_variants_entries_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.product_variants.ProductVariantsEntriesDialog = function(config) {

  config = config || {};
  
  config.id = 'product_variants_entries-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_group_entry");?>';
  config.width = 440;
  config.modal = true;
  config.iconCls = 'icon-product_variants-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      }, 
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      }, 
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.product_variants.ProductVariantsEntriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.product_variants.ProductVariantsEntriesDialog, Ext.Window, {

  show: function (id, valuesId) {
    this.variantsGroupsId = id || null;
    var variantsValuesId = valuesId || null;
    
    this.frmEntry.form.reset();  
    this.frmEntry.form.baseParams['products_variants_groups_id'] = this.variantsGroupsId;
    this.frmEntry.form.baseParams['products_variants_values_id'] = variantsValuesId;
    
    if (variantsValuesId > 0) {
      this.frmEntry.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'product_variants',
          action: 'load_product_variants_entry',
          products_variants_values_id: variantsValuesId
        },
        success: function (form, action) {
          Toc.product_variants.ProductVariantsEntriesDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.product_variants.ProductVariantsEntriesDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmEntry = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'product_variants',
        action: 'save_product_variants_entry'
      }, 
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      }
    });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var lang' . $l['id'] . ' = new Ext.form.TextField({name: "products_variants_values_name[' . $l['id'] . ']",';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"&nbsp;' . $osC_Language->get('field_group_entry_name') . '", ';
          
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important',";
        echo 'allowBlank: false});';
        
        echo 'this.frmEntry.add(lang' . $l['id'] . ');';
        $i++;
      }     
    ?>
    
    this.frmEntry.add(new Ext.form.NumberField({fieldLabel: '&nbsp;<?php echo $osC_Language->get('field_order'); ?>', name: 'sort_order', value: 0}));
    
    return this.frmEntry;
  },

  submitForm: function() {
    this.frmEntry.form.submit({
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