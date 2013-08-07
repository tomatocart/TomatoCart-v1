<?php
/*
  $Id: departments_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.departments.DepartMentDialog = function(config) {
  config = config || {};
  
  config.id = 'departments-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_deapertment'); ?>';
  config.modal = true;
  config.width = 450;
  config.height = 290;
  config.iconCls = 'icon-departments-win';
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
  
  Toc.departments.DepartMentDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.departments.DepartMentDialog, Ext.Window, {
  show: function(id) {
    var departmentsId = id || null;
    
    this.frmDeparment.form.baseParams['id'] = departmentsId;
    
    if (departmentsId > 0) {
    
      this.frmDeparment.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'departments',
          action: 'load_department'
        },
        success: function(form, action) {
          Toc.departments.DepartMentDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.departments.DepartMentDialog.superclass.show.call(this);
    }
  },
  
  getDepartmentDescriptionPanel: function() {
    this.tabLanguage = new Ext.TabPanel({
      activeTab: 0,
      enableTabScroll: true,
      deferredRender: false,
      border: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'var pnlLang' . $l['code'] . ' = new Ext.Panel({
          labelWidth: 100,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          autoHeight: true,
          labelSeparator: \' \',
          defaults: {
            anchor: \'96%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_departments_title') . '\', name: \'departments_title[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_departments_description') . '\', name: \'departments_description[' . $l['id'] . ']\', height: 120}
          ]
        });
        
        this.tabLanguage.add(pnlLang' . $l['code'] . ');
        ';
      }
    ?>
    
    return this.tabLanguage;
  },
  
  buildForm: function() {
    this.frmDeparment = new Ext.form.FormPanel({
      border: false,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'departments',
        action: 'save_department'
      }, 
      labelWidth: 100,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {xtype: 'textfield', vtype:'email', fieldLabel: '<?php echo $osC_Language->get('field_departments_email'); ?>', name: 'departments_email_address', allowBlank: false, anchor: '96%'},
        this.getDepartmentDescriptionPanel()
      ]
    });
    return this.frmDeparment;
  },
  
  submitForm: function() {
    this.frmDeparment.form.submit({
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