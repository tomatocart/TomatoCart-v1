<?php
/*
  $Id: languages_export_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.LanguagesExportDialog = function(config) {

  config = config || {};
  
  config.id = 'languages-export-dialog-win';
  config.width = 640;
  config.modal = true;
  config.iconCls = 'icon-languages-win';
  config.items = this.buildForm(); 
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_export') ?>',
      handler: function() {
        this.exportLanguage();
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
  
  Toc.languages.LanguagesExportDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.LanguagesExportDialog, Ext.Window, {
  
  show: function (languagesId) {
    this.languagesId = languagesId || null;
    
    if (this.languagesId > 0) {
      this.frmExport.form.baseParams['languages_id'] = languagesId;
      this.dsGroups.baseParams['languages_id'] = languagesId;
      this.dsGroups.load();
    }
    
    Toc.languages.LanguagesExportDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.dsGroups = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_groups'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'text']
      })                                                                                    
    });
    
    this.lstGroups = new Ext.ux.Multiselect({
      name: 'export', 
      store: this.dsGroups,
      width: 580,
      height: 250,
      hideLabel: true,
      displayField: 'text',
      hiddenName: 'export_id',
      valueField: 'id',
      legend: '<?php echo $osC_Language->get('table_heading_definition_groups'); ?>', 
      isFormField: true,
      style: 'margin-bottom: 8px'
    });    
    
    this.chkIncludeData = new Ext.form.Checkbox({
      name: 'include_data', 
      fieldLabel: '<?php echo $osC_Language->get('field_export_with_data'); ?>', 
      inputValue: 'on', 
      checked: true
    });
    
    this.frmExport = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages',
        action: 'export'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        anchor: '97%'
      },
      labelWidth: 200,
      items: [ 
        {html: '<p class="form-info">&nbsp;<?php echo $osC_Language->get('introduction_export_language'); ?></p>', border: false},
        this.lstGroups,
        this.chkIncludeData
      ]
    });
    
    return this.frmExport;
  },

  exportLanguage: function() {
    var languagesId = this.languagesId;
    var groups = this.lstGroups.getValue();
    var data = this.chkIncludeData.getRawValue();
    var params = "height=600px, width=640px, top= 50px, left=165px, staus=yes, toolbar=no, menubar=no, location=no, scrollbars=yes";
    
    window.open('<?php echo osc_href_link_admin(FILENAME_JSON); ?>' + '?module=languages&action=export&languages_id=' + languagesId + '&export=' + groups + '&include_data=' + data + '&token=' + token, '<?php echo $osC_Language->get('button_export') ?>', params);
  }
});