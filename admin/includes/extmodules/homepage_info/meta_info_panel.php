<?php
/*
  $Id: meta_info_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.homepage_info.MetaInfoPanel = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_meta_info_title'); ?>';
  config.border = false;
  config.layout = 'fit';
  
  config.items = this.buildForm();

  Toc.homepage_info.MetaInfoPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.homepage_info.MetaInfoPanel, Ext.Panel, {
  buildForm: function () {
    var tabMetaInfo = new Ext.TabPanel({
      activeTab: 0,
      border: false,
      deferredRender: false
    });
     
    <?php
      foreach ($osC_Language->getAll() as $l) {
        $code = strtoupper($l['code']);
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          defaults: {
            anchor: \'96%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_page_title') . '\', name: \'HOME_PAGE_TITLE[' . $code . ']\'},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_meta_keywords') . '\', name: \'HOME_META_KEYWORD[' . $code . ']\'},
            {xtype: \'textarea\', height: 130, fieldLabel: \'' . $osC_Language->get('field_meta_description') . '\', name: \'HOME_META_DESCRIPTION[' . $code . ']\'}            
            ]
        });
        
        tabMetaInfo.add(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    
    return tabMetaInfo;
  }  
});