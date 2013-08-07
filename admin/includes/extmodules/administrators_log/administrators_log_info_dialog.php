<?php
/*
  $Id: administrators_log_info_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.administrators_log.AdministratorsLogInfoDialog = function(config) {
  config = config || {}; 
  
  config.id = 'administrators_log_info-dialog';
  config.layout = 'border';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 600;
  config.height = 420; 
  config.modal = true;
  config.iconCls = 'icon-administrators_log-win';
  config.items = this.buildForm(config.administrators_log_id, config.logo_info_title, config.date);
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  Toc.administrators_log.AdministratorsLogInfoDialog.superclass.constructor.call(this, config);
}
		

Ext.extend(Toc.administrators_log.AdministratorsLogInfoDialog, Ext.Window, {
  
  buildForm: function(administrators_log_id, logo_info_title, date) {
    var items = [
      this.getInfoPanel(logo_info_title, date), 
      this.getAdministratorsLogoGrid(administrators_log_id)
    ];
    
    return items;
  },
  
  getInfoPanel: function(title, date) {
    var pnlInfo = new Ext.Panel({
      title: title,
      region: 'north',
      height: 60,
      border: false,
      items: [{xtype:'statictextfield', value: '<p><b><?php echo $osC_Language->get('field_date'); ?></b>&nbsp;&nbsp;&nbsp;' + date + '</p>'}]
    });
    
    return pnlInfo;
  },
    
  getAdministratorsLogoGrid: function (administrators_log_id) {
    var grdAdministratorsLog = new Ext.grid.GridPanel({
      region: 'center',
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'administrators_log',
          action: 'get_administrators_log_info',
          administrators_log_id: administrators_log_id
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          }, [
            'fields',
            'old_value',
            'new_value'
        ]),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {id:'administrators_log_modules', header:'<?php echo $osC_Language->get("table_heading_fields"); ?>', dataIndex:'fields'},
        {header: '<?php echo $osC_Language->get("table_heading_value_old"); ?>', dataIndex:'old_value', width: 140},
        {header: '<?php echo $osC_Language->get("table_heading_value_new"); ?>', dataIndex:'new_value', width: 140}
      ]),
      autoExpandColumn: 'administrators_log_modules'
    });
    
    return grdAdministratorsLog;
  }
});
