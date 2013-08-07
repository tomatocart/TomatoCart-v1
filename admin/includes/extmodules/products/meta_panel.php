<?php
/*
  $Id: meta_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.MetaPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_meta'); ?>';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
  
  Toc.products.MetaPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.MetaPanel, Ext.TabPanel, {
  buildForm: function() {
    var panels = [];
    this.txtProductUrl = [];
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
      
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 8px\',
          defaults: {
            anchor: \'98%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_page_title') . '\', name: \'products_page_title[' . $l['id'] . ']\'},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_meta_keywords') . '\', name: \'products_meta_keywords[' . $l['id'] . ']\'},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_meta_description') . '\', name: \'products_meta_description[' . $l['id'] . ']\', height: 200},
            this.txtProductUrl[' . $l['id'] . '] = new Ext.form.TextField({fieldLabel: \'' . $osC_Language->get('field_friendly_url') . '\', name: \'products_friendly_url[' . $l['id'] . ']\'})
          ]
        });
        
        panels.push(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    return panels;
  }
});