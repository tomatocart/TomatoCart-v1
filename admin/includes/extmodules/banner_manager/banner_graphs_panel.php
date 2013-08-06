<?php
/*
  $Id: banner_graphs_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.banner_manager.GraphsPanel = function(config) {
  config = config || {};
    
  config.title = '<?php echo $osC_Language->get('panel_graphs_title'); ?>';
  this.buildForm(config.bannerId);
  
  Toc.banner_manager.GraphsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.banner_manager.GraphsPanel, Ext.Panel, {
  
  changeGraphs: function(bannerId, type, month, year){
    Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'banner_manager',
        action: 'get_image',
        'banners_id': bannerId,
        'type': type,
        'month': month,
        'year': year
      },
      callback: function(options, success, response) {
        this.body.update((Ext.decode(response.responseText)).image);          
      },
      scope: this
    }); 
  },
  
  buildForm: function(bannersId) {
    Ext.Ajax.request({
    waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'banner_manager',
        action: 'get_image',
        'banners_id': bannersId
      },
      callback: function(options, success, response) {
	        this.body.update((Ext.decode(response.responseText)).image);      
      },
      scope: this
    });
  }
});