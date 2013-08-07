<?php
/*
  $Id: homepage_info_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.homepage_info.HomepageInfoPanel = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_homepage_text_title'); ?>';
  config.border = false;
  config.layout = 'fit';

  config.items = this.buildForm();

  Toc.homepage_info.HomepageInfoPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.homepage_info.HomepageInfoPanel, Ext.Panel, {
  buildForm: function () {
    var tabHomepageInfo = new Ext.TabPanel({
      activeTab: 0,
      border: false,
      deferredRender: false
    });
     
    <?php
      list($defaultLanguageCode,) = split("_", $osC_Language->getCode());
      
      foreach ($osC_Language->getAll() as $l) {
        $code = strtoupper($l['code']);
        if(USE_WYSIWYG_TINYMCE_EDITOR == 1) {  
          $editor = '{
            xtype: \'tinymce\',
            fieldLabel: \'' . $osC_Language->get('field_homepage_text') . '\',
            name: \'index_text[' . $l['id'] . ']\',
            document_base_url : \'' . DIR_WS_HTTP_CATALOG . '\',
            tinymceSettings: {
              theme : "advanced",
              language: "' . $defaultLanguageCode . '", 
              convert_urls : false,
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
              extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
            }
          }';
        } else {
          $editor = '{xtype: \'htmleditor\', height: 130, fieldLabel: \'' . $osC_Language->get('field_homepage_text') . '\', name: \'index_text[' . $l['id'] . ']\'}';
        }
                  
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          defaults: {
            anchor: \'98% 98%\'
          },
          items: [
            ' . $editor . '
            ]
        });
        
        tabHomepageInfo.add(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    return tabHomepageInfo;
  }  
});