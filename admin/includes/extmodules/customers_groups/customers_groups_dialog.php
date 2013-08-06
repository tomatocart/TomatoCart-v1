<?php
/*
  $Id: customers_groups_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers_groups.CustomersGroupsDialog = function(config) {
  config = config || {};
  
  config.id = 'customers_groups-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_customer_group'); ?>';
  config.width = 480;
  config.modal = true;
  config.iconCls = 'icon-customers_groups-win';
  config.layout = 'fit';
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.customers_groups.CustomersGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers_groups.CustomersGroupsDialog, Ext.Window, {
  
  show: function (id) {
    var groupsId = id || null;
    
    this.frmCustomersGroups.form.reset(); 
    this.frmCustomersGroups.baseParams['groups_id'] = groupsId;
     
    if (groupsId > 0) {
      this.frmCustomersGroups.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'customers_groups',
          action: 'load_customers_groups'
        },
        success: function() {
          Toc.customers_groups.CustomersGroupsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.customers_groups.CustomersGroupsDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmCustomersGroups = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'customers_groups',
        action: 'save_customers_groups'
      }, 
      defaults: {
        anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      autoHeight: true,
      labelWidth: 120,
      items: [
        {
          xtype: 'numberfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_group_discount'); ?>  (%)', 
          name: 'customers_groups_discount',
          minValue: 0,
          maxValue: 100,
          value: 0, 
          allowBlank: false
        },
        {
          xtype: 'checkbox', 
          fieldLabel: '<?php echo $osC_Language->get('field_set_as_default'); ?>', 
          name: 'is_default',
          anchor: '',
          inputValue: 1 
        }
      ]
    });
    
    <?php
      $i = 1;
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'this.lang' . $l['id'] . ' = new Ext.form.TextField({name: "customers_groups_name[' . $l['id'] . ']",';
        
        if ($i != 1) 
          echo ' fieldLabel: "&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_group_name') . '", ';
          
        echo 'labelWidth: 50,';
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        echo 'this.frmCustomersGroups.insert(' . $i . ', this.lang' . $l['id'] . ');';
        
        $i++;
      }
    ?>
      
    return this.frmCustomersGroups;
  },

  submitForm : function() {
    this.frmCustomersGroups.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
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