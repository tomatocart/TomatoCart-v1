<?php
/*
  $Id: general_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.GeneralPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
  
  Toc.products.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.GeneralPanel, Ext.TabPanel, {
  buildForm: function() {
    var panels = [];

    <?php
      list($defaultLanguageCode) = split("_", $osC_Language->getCode());
      
      foreach ($osC_Language->getAll() as $l) {
      
        if(USE_WYSIWYG_TINYMCE_EDITOR == 1) {
          $editor = '
            {
              xtype: "tinymce",
              fieldLabel: \'' . $osC_Language->get('field_description') . '\',
              name: \'products_description[' . $l['id'] . ']\',
              relative_urls : false,
              remove_script_host : true,
              document_base_url : "/",
              convert_urls : false, 
              height: 250,
              tinymceSettings: {
                theme : "advanced",
                language: "' . $defaultLanguageCode . '", 
                relative_urls: false, 
                remove_script_host: false,
                plugins: "safari,advimage,preview,media,contextmenu,paste,directionality",
                theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,formatselect,fontselect,fontsizeselect",
                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : false,
//                content_css : "../templates/' . DEFAULT_TEMPLATE . '/stylesheet.css",
                extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
              }
            }';
        } else {
          $editor = '{xtype: \'htmleditor\', fieldLabel: \'' . $osC_Language->get('field_description') . '\', name: \'products_description[' . $l['id'] . ']\', height: 230}';
        }
           
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
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_name') . '\', name: \'products_name[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_tags') . '\', name: \'products_tags[' . $l['id'] . ']\'},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_short_description') . '\', name: \'products_short_description[' . $l['id'] . ']\', height: \'50\'},
            ' . $editor . ',  
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_url') . '\', name: \'products_url[' . $l['id'] . ']\'}
          ]
        });
        
        panels.push(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    return panels;
  }
});