<?php
/*
  $Id: log_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.LogDialog = function(config) {

  config = config || {};
  
  config.id = 'log-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'fit';
  config.width = 600;
  config.height = 350;
  config.items = this.buildGrid();  
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];
  
  Toc.newsletters.LogDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.newsletters.LogDialog, Ext.Window, {
  show: function(newslettersId) {
    this.grdLog.getStore().baseParams['newsletters_id'] = newslettersId;
    this.grdLog.getStore().load();
     
    Toc.newsletters.LogDialog.superclass.show.call(this);
  },
  
  buildGrid: function() {
  
    var dsLog = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'newsletters',
        action: 'list_log'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'email_address',
        'sent',
        'date_sent'
      ])
    });
      
    this.grdLog = new Ext.grid.GridPanel({
      store: dsLog,  
      cm: new Ext.grid.ColumnModel([
        {id: 'email_address', header: '<?php echo $osC_Language->get('table_heading_email_addresses'); ?>', dataIndex: 'email_address'},
        {header: '<?php echo $osC_Language->get('table_heading_sent'); ?>', dataIndex: 'sent', width: 100, align: 'center'},
        {header: '<?php  echo $osC_Language->get('table_heading_date_sent'); ?>', dataIndex: 'date_sent', width: 150, align: 'center'}
      ]),
      autoExpandColumn: 'email_address',
      border: false,
      bbar: new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsLog,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText : TocLanguage.beforePageText,
        firstText: TocLanguage.firstText,
        lastText: TocLanguage.lastText,
        nextText: TocLanguage.nextText,
        prevText: TocLanguage.prevText,
        afterPageText: TocLanguage.afterPageText,
        refreshText: TocLanguage.refreshText,
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg,
        prevStepText: TocLanguage.prevStepText,
        nextStepText: TocLanguage.nextStepText
      })
    });
    
    return this.grdLog;
  }
});