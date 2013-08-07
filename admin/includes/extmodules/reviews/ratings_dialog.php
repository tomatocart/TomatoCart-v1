<?php
/*
  $Id: ratings_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.reviews.RatingsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'ratings-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_rating'); ?>';
  config.layout = 'fit';
  config.width = 440;
  config.modal = true;
  config.iconCls = 'icon-ratings-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ]; 
  
  Toc.reviews.RatingsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reviews.RatingsDialog, Ext.Window, {
  
  show: function(id) {
    var ratingsId = id || null;
    
    this.frmRatings.form.reset();  
    this.frmRatings.form.baseParams['ratings_id'] = ratingsId;
    
    if (ratingsId > 0) {
      this.frmRatings.load({
        url: Toc.CONF.CONN_URL,
        params:{
          module: 'reviews',
          action: 'load_ratings'
        },
        success: function(form, action) {
          Toc.reviews.RatingsDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this       
      });
    } else {
      Toc.reviews.RatingsDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.frmRatings = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'reviews',
        action: 'save_ratings'
      }, 
      autoHeight: true,
      defaults: {
          anchor: '96%'
      },
      layoutConfig: {
        labelSeparator: ''
      }
    });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: "ratings_text[' . $l['id'] . ']",';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_rating_name') . '", ';
          
        echo 'labelWidth: 70,';
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        echo 'this.frmRatings.add(txtLang' . $l['id'] . ');';
        $i++;
      }     
    ?>
    
    var pnlPublish = {
      layout: 'column',
      border: false,
      items: [
        {
          width: 200,
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          items: [
            {
              xtype: 'radio', 
              name: 'status', 
              fieldLabel: '<?php echo $osC_Language->get('field_rating_status'); ?>', 
              inputValue: '1', 
              boxLabel: '<?php echo $osC_Language->get('field_status_enabled'); ?>', 
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
              name: 'status', 
              inputValue: '0', 
              boxLabel: '<?php echo $osC_Language->get('field_status_disabled'); ?>', 
              width: 150
            }
          ]
        }
      ]
    };
    this.frmRatings.add(pnlPublish);
    
    return this.frmRatings;
  },

  submitForm : function() {
    this.frmRatings.form.submit({
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