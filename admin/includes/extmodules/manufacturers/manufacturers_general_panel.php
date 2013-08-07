<?php
/*
  $Id: manufacturers_general_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.manufacturers.GeneralPanel = function(config) {
  config = config || {};    
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.layout = 'form';
  config.layoutConfig = {labelSeparator: ''};
  config.defaults = {anchor: '97%'};
  config.labelWidth = 160;
  config.style = 'padding: 8px';
  config.items = this.buildForm();
    
  Toc.manufacturers.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.manufacturers.GeneralPanel, Ext.Panel, {

  buildForm: function() {
    var items = [];
    
    items.push({xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', name: 'manufacturers_name', allowBlank: false});
    items.push({xtype: 'panel', name: 'manufactuerer_image_panel', id: 'manufactuerer_image_panel', border: false, html: ''});
    items.push({xtype: 'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', name: 'manufacturers_image'});
    
    <?php
        $i = 1;
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'this.lang' . $l['id'] . ' = new Ext.form.TextField({name: "manufacturers_url[' . $l['id'] . ']",';
          
          if ($i == 1)
            echo 'fieldLabel:"' . $osC_Language->get('field_url') . '",';
          else
            echo 'fieldLabel: "&nbsp;",';  
          
          echo 'labelWidth: 50,';
          echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;', ";
          echo "value: 'http://', ";
          echo 'width: 300});';
          echo 'items.push(this.lang' . $l['id'] . ');';
          
          $i++;
        }
    ?>
    
    return items;
  } 
});