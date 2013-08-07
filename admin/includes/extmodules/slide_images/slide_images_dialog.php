 <?php
/*
  $Id: slide_images_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.slideImages.SlideImagesDialog = function(config) {
  
  config = config || {};
  
  config.id = 'slide_images_dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title_new_slide_image'); ?>';
  config.layout = 'fit';
  config.modal = true;
  config.width = 600;
  config.height = 500;
  config.iconCls = 'icon-slide_images-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];
    
  Toc.slideImages.SlideImagesDialog.superclass.constructor.call(this, config);

  this.addEvents({'save' : true});  
}

Ext.extend(Toc.slideImages.SlideImagesDialog, Ext.Window, {
  show: function (id) {
    var slideImagesId = id || null;
    
    this.pnlSlideImages.form.reset();
    this.pnlSlideImages.form.baseParams['image_id'] = slideImagesId;
    
    if (slideImagesId > 0) {
      this.pnlSlideImages.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'slide_images',
          action: 'load_slide_images'
        },
        success: function(form, action) {
          <?php 
            foreach ($osC_Language->getAll() as $l) {
              echo " 
                if (action.result.data.slide_image" . $l['id'] . ") {
                  var image = action.result.data.slide_image" . $l['id'] . ";
                  this.pnlSlideImages.findById('uploaded_img" . $l['id'] . "').body.update(image);
                }";
             
            }
          ?>
          
          Toc.slideImages.SlideImagesDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this   
      }); 
    }
    else
      Toc.slideImages.SlideImagesDialog.superclass.show.call(this);
  },
  
  getDataPanel: function() {
    var pnlData = new Ext.Panel({ 
      region: 'north',
      title: '<?php echo $osC_Language->get('heading_title_data'); ?>',
      border: false,
      labelWidth: 108,
      autoHeight: true,
      layout: 'form',
      defaults: {
        anchor: '98%'
      },
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            {
              width: 200,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '&nbsp;&nbsp;<?php echo $osC_Language->get('field_publish'); ?>', boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>' , name: 'status', xtype:'radio', inputValue: '1'}
              ]
            },
            {
              width: 80,
              layout: 'form',
              border: false,
              items: [
                {hideLabel: true, boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', xtype:'radio', name: 'status', inputValue: '0'}
              ]
            }
          ]
        },                           
        { 
          labelSeparator: ' ',
          xtype: 'numberfield', 
          fieldLabel: '&nbsp;&nbsp;<?php echo $osC_Language->get('field_order'); ?>', 
          name: 'sort_order',
          width: 402
        }
      ]
    });
    
    return pnlData;
  },
  
  getTabPanel: function() {
    var tabImages = new Ext.TabPanel({
       region: 'center',
       defaults:{
         hideMode: 'offsets'
       },
       activeTab: 0,
       deferredRender: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'this.' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          defaults: {
            anchor: \'98%\'
          },
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 8px\',
          items: [
            {layout: \'column\', width: 500, border: false, items: [{layout: \'form\', labelSeparator: \' \', border: false, items: [{xtype: \'fileuploadfield\', width: \'300\', fieldLabel: \'' . $osC_Language->get('field_slide_image') . '\', name: \'image' . $l['id'] . '\'}]},{layout: \'form\', border: false, items: [{xtype: \'panel\', border: false, html:\'<span style= "padding: 5px 0px 0px 10px; display: block;"><b>'.$osC_Language->get('maximum_file_upload_size').'</b></span>\'}]}]},
            {xtype: \'panel\', border: false, width: 400, name: \'uploaded_img'.$l['id'].'\', id: \'uploaded_img'.$l['id'].'\', html:\'\'},
            {xtype: \'textarea\', id: \''.$l['code'].'\', fieldLabel: \'' . $osC_Language->get('field_description') . '\', width: 400, height: 150, name: \'description[' . $l['id'] . ']\'},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_image_url') . '\', width: 400, name: \'image_url[' . $l['id'] . ']\'}
          ]
        });
        
        tabImages.add(this.' . $l['code'] . ');
        ';
      }
    ?>
    
    return tabImages;
  },

  buildForm: function() {
    this.pnlSlideImages = new Ext.FormPanel({
      layout: 'border',
      width: 600,
      height: 350,
      border: false,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'slide_images',
        action: 'save_slide_images'
      }, 
      fileUpload: true,
      items: [this.getDataPanel(), this. getTabPanel()]
    });
    
    return this.pnlSlideImages;  
  },
  
  submitForm : function() {
    this.pnlSlideImages.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback); 
        this.close();
      },
      failure: function(form, action) {
        if (action.failureType != 'client') {         
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);      
        }         
      },
      scope: this
    });   
  }
});