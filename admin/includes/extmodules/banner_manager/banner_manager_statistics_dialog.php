<?php
/*
  $Id: banner_manager_statistics_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.banner_manager.BannerManagerStatisticsDialog = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('banner_manager_statistics_dialog'); ?>';
  config.id = 'banner_manager_statistics-dialog-win';
  config.layout = 'fit';
  config.width = 640;
  config.height = 500;
  config.modal = true;
  config.iconCls = 'icon-banner_manager-win';
  config.items = this.buildForm(config.banners_id);
  
  config.tbar = [ 
    '->',
    this.cboType,
    ' ',
    this.cboMonth,
    ' ',
    this.cboYear
  ];
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: this.close,
      scope:this
    }
  ];
    
  this.addEvents({'saveSuccess': true});      

  Toc.banner_manager.BannerManagerStatisticsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.banner_manager.BannerManagerStatisticsDialog, Ext.Window, {
    
  show: function() {
    var month = new Date().getMonth();
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'banner_manager',
        action: 'loadMonth',
        'month': month
      },
      callback: function(options, success, response) {
        this.cboMonth.setValue(month);
        this.cboMonth.setRawValue((Ext.decode(response.responseText)).data.month);
      },
      scope: this
    });
    
    Toc.banner_manager.BannerManagerDialog.superclass.show.call(this);
  },
    
  buildForm: function(bannerId) {
    this.pnlGraphs = new Toc.banner_manager.GraphsPanel({bannerId: bannerId});
    this.pnlTable = new Toc.banner_manager.TablePanel({bannerId: bannerId});
    this.bannerId = bannerId;
    
    this.cboType = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'banner_manager',
          action: 'list_type',
          banners_id: bannerId
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'id', 
            'text'
          ]
        }),
        autoLoad: true
      }),
      valueField: 'id',
      displayField: 'text',
      value: '<?php echo $osC_Language->get('operation_heading_type_value'); ?>',
      width: 100,
      mode: 'remote',
      emptyText: '<?php echo $osC_Language->get('operation_heading_type'); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });
    
    this.cboMonth = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'banner_manager',
          action: 'list_month'
        },
        autoLoad: true,
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'id', 
            'text'
          ]
        })
      }),
      valueField: 'id',
      displayField: 'text',
      width: 100,
      mode: 'remote',
      emptyText: '<?php echo $osC_Language->get('operation_heading_month'); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });

    this.cboYear = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'banner_manager',
          action: 'list_year'
        },
        autoLoad: true,
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          fields: [
            'id', 
            'text'
          ]
        })
      }),
      valueField: 'id',
      displayField: 'text',
      value: new  Date().getFullYear(),
      width: 100,
      mode: 'remote',
      emptyText: '<?php echo $osC_Language->get('operation_heading_year'); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });
    
    this.tabBannerManagerStatistics = new Ext.TabPanel({
      activeTab: 0,
      defaults: {autoScroll: true},
      items: [
        this.pnlGraphs,
        this.pnlTable
      ]
    }); 
    
    return this.tabBannerManagerStatistics;
  },
  
  onSearch: function(){

    if( this.cboType.getValue() == 'daily' ) {
      this.cboMonth.enable();
      this.cboYear.enable();
      
      this.cboMonth.show();
      this.cboYear.show();
    } 
    
    if( this.cboType.getValue() == 'monthly' ){
      this.cboMonth.disable();
      this.cboYear.enable();
      
      this.cboMonth.hide();
      this.cboYear.show();
    } 
     
    if( this.cboType.getValue() == 'yearly' ) {
      this.cboMonth.disable();
      this.cboYear.disable();
      
      this.cboMonth.hide();
      this.cboYear.hide();
    }

    this.pnlGraphs.changeGraphs(this.bannerId, this.cboType.getValue(), this.cboMonth.getValue(), this.cboYear.getValue());
    this.pnlTable.changeTables(this.bannerId, this.cboType.getValue(), this.cboMonth.getValue(), this.cboYear.getValue());
  }

});