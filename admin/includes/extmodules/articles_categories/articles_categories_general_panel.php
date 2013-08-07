<?php
/*
  $Id: articles_categories_general_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.articles_categories.GeneralPanel = function(config) {
  config = config || {};    
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.layout = 'form';
  config.layoutConfig = {labelSeparator: ''};
  config.defaults = {anchor: '97%'};
  config.labelWidth = 160;
  config.style = 'padding: 8px';
  config.items = this.buildForm();
    
  Toc.articles_categories.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.articles_categories.GeneralPanel, Ext.Panel, {

  buildForm: function() {
    var items = [];
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: "articles_categories_name[' . $l['id'] . ']",';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_name') . '", ';
          
        echo 'labelWidth: 70,';
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        echo 'items.push(txtLang' . $l['id'] . ');';
        $i++;
      }     
    ?>
    
    var pnlPublish = {
      layout: 'column',
      border: false,
      items: [
        {
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          items: [
            {
              xtype: 'radio', 
              name: 'articles_categories_status', 
              fieldLabel: '<?php echo $osC_Language->get('field_publish'); ?>', 
              inputValue: '1', 
              boxLabel: '<?php echo $osC_Language->get('field_publish_yes'); ?>', 
              checked: true,
              anchor: ''
            }
          ]
        },
        {
          layout: 'form',
          border: false,
          items: [
            {
              xtype: 'radio', 
              hideLabel: true, 
              name: 'articles_categories_status', 
              inputValue: '0',
              boxLabel: '<?php echo $osC_Language->get('field_publish_no'); ?>'
            }
          ]
        }
      ]
    };
    items.push(pnlPublish);
        
    items.push({xtype: 'numberfield', id: 'articles_categories_order', name: 'articles_categories_order', fieldLabel: '<?php echo $osC_Language->get('field_articles_order'); ?>', allowBlank: false});
    
    return items;
  } 
});