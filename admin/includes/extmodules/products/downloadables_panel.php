<?php
/*
  $Id: downloadables_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.DownloadablesPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_downloadables'); ?>';
  config.layout = 'form';
  config.labelSeparator = ' ';
  config.style = 'padding: 10px';
  config.labelWidth = 180;
  
  config.items =this.buildForm();
  
  this.addEvents({'fileupload': true});
  this.on('fileupload', this.changeState, this);
  
  Toc.products.DownloadablesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.DownloadablesPanel, Ext.Panel, {
  buildForm: function() {
    this.file = new Ext.form.FileUploadField({
      fieldLabel: '<?php echo $osC_Language->get('field_file'); ?>', 
      name: 'downloadable_file', 
      width: 290, 
      allowBlank: false,
      buttonText: '...'
    });

    this.sampleFile = new Ext.form.FileUploadField({
      fieldLabel: '<?php echo $osC_Language->get('field_sample_file'); ?>', 
      name: 'sample_downloadable_file', 
      width: 290, 
      buttonText: '...'
    }); 

    this.txtNumOfDownloads = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_number_of_downloads'); ?>', 
      name: 'number_of_downloads', 
      allowBlank: false,
      width: 250
    }); 

    this.txtNumOfAccessibleDays = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_number_of_accessible_days'); ?>', 
      name: 'number_of_accessible_days', 
      allowBlank: false,
      width: 250
    });

    return [this.file, 
            {xtype: 'panel', name: 'products_file', id: 'products_file_link_panel', border: false, html: ''},
            this.sampleFile, 
            {xtype: 'panel', name: 'products_sample_file', id: 'products_sample_file_link_panel', border: false}, 
            this.txtNumOfDownloads, 
            this.txtNumOfAccessibleDays];
  },
  
  changeState: function(status) {
    if (status == true) {
      this.file.setValue(' ');
      this.file.disable();
    } else {
      this.file.enable();
    }
  },
  
  loadForm: function(data) {
    htmFile = '<a href="' + data.cache_filename_url + '" target="_blank" style="padding-left:190px;padding-bottom: 15px">' + data.filename + '</a>';
    htmSampleFile = '<a href="' + data.cache_sample_filename_url + '" target="_blank" style="padding-left:190px;padding-bottom: 15px">' + data.sample_filename + '</a>';
    
    this.findById('products_file_link_panel').body.update(htmFile);
    this.findById('products_sample_file_link_panel').body.update(htmSampleFile);
            
    this.txtNumOfDownloads.setValue(data.number_of_downloads);
    this.txtNumOfAccessibleDays.setValue(data.number_of_accessible_days);
  }
});