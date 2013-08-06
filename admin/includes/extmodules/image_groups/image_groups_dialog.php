<?php
/*
  $Id: image_groups_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.image_groups.ImageGroupsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'image_groups-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_image_group'); ?>';
  config.layout = 'fit';
  config.width = 450;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-image_groups-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
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
  
  Toc.image_groups.ImageGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.image_groups.ImageGroupsDialog, Ext.Window, {
  
  show: function(id) {
    imageGroupsId = id || null;      
    
    this.frmImageGroup.form.reset();  
    this.frmImageGroup.form.baseParams['image_groups_id'] = imageGroupsId;

    if (imageGroupsId > 0) {
      this.frmImageGroup.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_image_group',
          image_groups_id: imageGroupsId
        },
        success: function(form, action) {
          if(action.result.data.is_default) {
            Ext.getCmp('default_image_group').disable();
          }
            
          Toc.image_groups.ImageGroupsDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {
      Toc.image_groups.ImageGroupsDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmImageGroup = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'image_groups',
        action: 'save_image_group'
      }, 
      autoHeight: true,
      defaults: { 
        anchor: '97%' 
      },
      layoutConfig: { 
        labelSeparator: '' 
      },
      labelWidth: 150
    });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: "title[' . $l['id'] . ']",';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_title') . '", ';
          
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        
        echo 'this.frmImageGroup.add(txtLang' . $l['id'] . ');';
        $i++;
      }     
    ?>

    this.frmImageGroup.add({xtype: 'textfield', name: 'code', allowBlank: false, fieldLabel: '<?php echo $osC_Language->get('field_code'); ?>'});
    this.frmImageGroup.add({xtype: 'numberfield', name: 'size_width', allowBlank: false, fieldLabel: '<?php echo $osC_Language->get('field_width'); ?>'});
    this.frmImageGroup.add({xtype: 'numberfield', name: 'size_height', allowBlank: false, fieldLabel: '<?php echo $osC_Language->get('field_height'); ?>'});
    this.frmImageGroup.add({xtype: 'checkbox', name: 'force_size', fieldLabel: '<?php echo $osC_Language->get('field_force_size'); ?>', anchor: ''});
    this.frmImageGroup.add({xtype: 'checkbox', name: 'is_default', id: 'default_image_group', fieldLabel: '<?php echo $osC_Language->get('field_set_as_default'); ?>', anchor: ''});
    
    return this.frmImageGroup;
  },

  submitForm: function() {
    this.frmImageGroup.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});