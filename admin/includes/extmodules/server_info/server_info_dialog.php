<?php
/*
  $Id: server_info_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $osC_ObjectInfo = new osC_ObjectInfo(osc_get_system_information());
?>
Toc.server_info.ServerInfoDialog = function(config) {

  config = config || {};
  
  config.id = 'server_info-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 800;
  config.height = 400;
  config.iconCls = 'icon-server_info-win';
  config.layout = 'fit';
  config.items = this.buildForm();
   
  Toc.server_info.ServerInfoDialog.superclass.constructor.call(this,config); 
};

Ext.extend(Toc.server_info.ServerInfoDialog, Ext.Window, {

   buildForm: function() {
    var frmServerInfo = new Ext.Panel({
      border: false,
      layout: 'form',
      style: 'padding: 10px',
      defaults: {anchor: '98%'},
      layoutConfig: {labelSeparator: ''},
      labelWidth: 150,
      items: [
        {
          border: false,
          html: '<p class="form-info"><b><?php echo PROJECT_VERSION; ?></b></p>'
        },   
        {
          layout: 'column', 
          border: false,
          items: [
            {
              xtype: 'panel',
              layout: 'form', 
              border: false,
              layoutConfig: {labelSeparator: ''},
              columnWidth: .49, 
              labelWidth: 150,
              items: [
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_server_host'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('host') . ' (' . $osC_ObjectInfo->get('ip') . ')'; ?>"
                },
               
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_server_operating_system'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('system') . ' ' . $osC_ObjectInfo->get('kernel'); ?>"
                },
        
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_server_date'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('date'); ?>"
                }
              ]
            },
            {
              xtype: 'panel',
              layout: 'form', 
              border: false,
              layoutConfig: {labelSeparator: ''},
              columnWidth: .49, 
              labelWidth: 150,
              items: [
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_database_host'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('db_server') . ' (' . $osC_ObjectInfo->get('db_ip') . ')'; ?>"
                },
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_database_version'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('db_version'); ?>"
                },
                {
                  xtype: 'statictextfield', 
                  fieldLabel: '<?php echo $osC_Language->get('field_database_date'); ?>', 
                  value: "<?php echo $osC_ObjectInfo->get('db_date'); ?>"
                }  
              ]
            }
          ]
        },
        {
          xtype: 'statictextfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_server_up_time'); ?>', 
          value: "<?php echo $osC_ObjectInfo->get('uptime'); ?>"
        },
        {
          xtype: 'statictextfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_database_up_time'); ?>', 
          value: "<?php echo $osC_ObjectInfo->get('db_uptime'); ?>"
        },
        {
          xtype: 'statictextfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_http_server'); ?>', 
          value: "<?php echo $osC_ObjectInfo->get('http_server'); ?>",
          group: true
        },
        {
          xtype: 'statictextfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_php_version'); ?>', 
          value: "<?php echo 'PHP: ' . $osC_ObjectInfo->get('php') . ' / Zend: ' . $osC_ObjectInfo->get('zend'); ?>"
        }
      ]
    });
    
    return frmServerInfo; 
  }
});