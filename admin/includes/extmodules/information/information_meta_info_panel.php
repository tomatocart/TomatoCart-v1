<?php
/*
  $Id: information_meta_info_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.information.MetaInfoPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_meta'); ?>';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
  
  Toc.information.MetaInfoPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.information.MetaInfoPanel, Ext.TabPanel, {
  buildForm: function() {
    var panels = [];
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          defaults: {
            anchor: \'98%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_page_title') . '\', name: \'page_title[' . $l['id'] . ']\'},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_meta_keywords') . '\', name: \'meta_keywords[' . $l['id'] . ']\'},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_meta_description') . '\', name: \'meta_description[' . $l['id'] . ']\'},
            {
              xtype: \'textfield\', 
              fieldLabel: \'' . $osC_Language->get('field_article_url') . '\', 
              labelStyle: \'background: url(../images/worldflags/"' . $l['country_iso'] . '.png) no-repeat right center !important;\',
              name: \'articles_url[' . $l['id'] . ']\'
            }
          ]
        });
        
        panels.push(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    return panels;
  }
});