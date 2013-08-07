<?php
/*
  $Id: reviews_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.reviews.ReviewsEditDialog = function (config) {
  config = config || {};
  
  config.id = 'reviews-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_special"); ?>';
  config.layout = 'fit';
  config.width = 525;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-reviews-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
        this.disable();
      }, 
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.reviews.ReviewsEditDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.reviews.ReviewsEditDialog, Ext.Window, {
  show: function (id) {
    var reviewsId = id || null;
    
    this.frmReviews.form.reset();
    this.frmReviews.form.baseParams['reviews_id'] = reviewsId;
    
    if (reviewsId > 0) {
      this.frmReviews.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_reviews'
        },
        success: function (form, action) {
          Toc.reviews.ReviewsEditDialog.superclass.show.call(this);
          
          if ( Ext.isEmpty(action.result.data.ratings) ) {
            this.pnlAverageRating = new Ext.Panel({
              layout: 'table',
              defaultType: 'radio', 
              border: false,
              style: 'padding-left: 8px;font-size:12px;',
              items: [
                {xtype: 'label', text: '<?php echo $osC_Language->get("field_detailed_rating"); ?>'},
                {xtype: 'label', text: '<?php echo $osC_Language->get("rating_bad"); ?>', style: 'padding-right: 20px'}, 
                {name: 'detailed_rating', inputValue: '1', checked: action.result.data.detailed_rating == 1},
                {name: 'detailed_rating', inputValue: '2', checked: action.result.data.detailed_rating == 2},
                {name: 'detailed_rating', inputValue: '3', checked: action.result.data.detailed_rating == 3},
                {name: 'detailed_rating', inputValue: '4', checked: action.result.data.detailed_rating == 4},
                {name: 'detailed_rating', inputValue: '5', checked: action.result.data.detailed_rating == 5},
                {xtype: 'label', text: '<?php echo $osC_Language->get("rating_good"); ?>', style: 'padding-right: 100px;padding-left: 20px'}
              ]
            });
            this.frmReviews.add(this.pnlAverageRating);   
          } else {
            var items = [];
            for (var i = 0; i < action.result.data.ratings.length; i++){
              var n = action.result.data.ratings[i].customers_ratings_id;
              var name = "ratings_value" + n;
              
              items.push({xtype: 'statictextfield', value: action.result.data.ratings[i].name, style: 'padding-right: 30px'});
              items.push({xtype: 'statictextfield', value: '<?php echo $osC_Language->get("rating_bad"); ?>', style: 'padding-right: 20px'}); 
              items.push({name: name, inputValue: '1', checked: action.result.data.ratings[i].value == 1, style: 'padding-left: 10px'});
              items.push({name: name, inputValue: '2', checked: action.result.data.ratings[i].value == 2, style: 'padding-left: 10px'});
              items.push({name: name, inputValue: '3', checked: action.result.data.ratings[i].value == 3, style: 'padding-left: 10px'});
              items.push({name: name, inputValue: '4', checked: action.result.data.ratings[i].value == 4, style: 'padding-left: 10px'});
              items.push({name: name, inputValue: '5', checked: action.result.data.ratings[i].value == 5, style: 'padding-left: 10px'});
              items.push({xtype: 'statictextfield', value: '<?php echo $osC_Language->get("rating_good"); ?>', style: 'padding-right: 100px;padding-left: 20px'});
            }
            
            var pnlDetailedRatings = new Ext.Panel({
              layout:'table',
              layoutConfig:{columns:8},
              border: false,
              defaultType: 'radio',
              style: 'margin-left: 120px;font-size: 12px',
              items: items
            });
            
            this.frmReviews.add(pnlDetailedRatings);
          }
          
          this.frmReviews.add(this.getPnlStatus(action.result.data.reviews_status));
          this.frmReviews.add(this.txtRating);
          this.frmReviews.doLayout();
          this.frmReviews.form.setValues(action.result.data);
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.reviews.ReviewsEditDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function () {
    this.frmReviews = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reviews',
        action: 'save_reviews'
      },
      border: false,
      style: 'padding: 8px;',
      labelWidth: 100,
      autoHeight: true,
      defaults: { 
        anchor: '97%' 
      },
      layoutConfig: { 
        labelSeparator: ' ' 
      },
      items: [
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_product"); ?>', name: 'products_name'},
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_author"); ?>', name: 'customers_name'},
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_summary_rating"); ?>', name: 'reviews_rating'}
      ]
    });
    this.txtRating = {xtype: 'textarea', fieldLabel: '<?php echo $osC_Language->get("field_review"); ?>', name: 'reviews_text', height: 150, allowBlank: false};
    
    return this.frmReviews;
  },
  
  getPnlStatus: function(status) {
    return new Ext.Panel({
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
              name: 'reviews_status', 
              fieldLabel: '<?php echo $osC_Language->get('field_review_status'); ?>', 
              inputValue: '1', 
              boxLabel: '<?php echo $osC_Language->get('field_status_enabled'); ?>', 
              checked: true,
              anchor: '',
              checked: status == 1
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
              name: 'reviews_status', 
              inputValue: '0', 
              boxLabel: '<?php echo $osC_Language->get('field_status_disabled'); ?>', 
              width: 150,
              checked: status == 0
            }
          ]
        }
      ]
    });
  },
  
  submitForm: function () {
    this.frmReviews.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
});