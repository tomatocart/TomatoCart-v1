<?php
/*
  $Id: coupons_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.coupons.CouponsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'coupons-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_coupon'); ?>';
  config.layout = 'fit';
  config.width = 700;  
  config.height = 450;
  config.modal = true;
  config.autoScroll = true;
  config.iconCls = 'icon-coupons-win';
  config.items = this.buildForm();
  
  // clear Ie scroll bar bug  
  config.bodyStyle = 'position: relative;';
    
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
  
  this.addEvents({'saveSuccess': true});
  
  Toc.coupons.CouponsDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.coupons.CouponsDialog, Ext.Window, {
  
  show: function(couponsId) {
    this.couponsId = couponsId || null;
    
    this.frmCoupons.form.reset();  
    this.frmCoupons.form.baseParams['coupons_id'] = this.couponsId;
    
    this.onRdbRestrictionNoneChecked(null, true);
    
    if (this.couponsId > 0) {
      this.frmCoupons.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_coupons'
        },
        success: function(form, action) {
          if (action.result.data.coupons_restrictions != <?php echo COUPONS_RESTRICTION_NONE; ?>) {
            var restrictions = null;
            if (action.result.data.coupons_restrictions == <?php echo COUPONS_RESTRICTION_CATEGOREIS; ?>) {
              restrictions = action.result.data.categories;
            } else if (action.result.data.coupons_restrictions == <?php echo COUPONS_RESTRICTION_PRODUCTS; ?>) {
              restrictions = action.result.data.products;
            }

            Ext.each(restrictions, function(restriction) {
              var store = this.grdRestriction.getStore();
              
              if (store.find('id', restriction['id']) == -1) {
                var record = Ext.data.Record.create([
                  {name: 'id', type: 'string'},
                  {name: 'name', type: 'string'}
                ]);
                
                var v = new record({id: restriction['id'], name: restriction['name']});
                store.add(v);
              }
            }, this);
          }
          
          Toc.coupons.CouponsDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this       
      });
    } else {
      Toc.coupons.CouponsDialog.superclass.show.call(this);
    }
  },    
  
  getGeneralPanel: function() {
    this.fsGeneral = new Ext.Panel({
      title: '<?php echo $osC_Language->get('fieldset_heading_general'); ?>',
      layout: 'form',
      border: false,
      autoHeight: true,
      layoutConfig: {
        labelSeparator: ' '
      },  
      labelWidth: 150
    });
    
    <?php
        $i = 0;
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'var couponName' . $l['id'] . ' = new Ext.form.TextField({name: "coupons_name[' . $l['id'] . ']",';
          
          if ($i == 0)
            echo 'fieldLabel:"' . $osC_Language->get('field_coupons_name') . '",';
          else
            echo 'fieldLabel: "&nbsp;",';  
          
          echo 'labelStyle: \'background: url(../images/worldflags/' . $l['country_iso'] . '.png) no-repeat right center !important;\', width: "98%"});';
          echo 'this.fsGeneral.add(couponName' . $l['id'] . ');';
          
          $i++;
        }
        
        $i = 0;
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'var couponDesc' . $l['id'] . ' = new Ext.form.TextField({name: "coupons_description[' . $l['id'] . ']",';
          
          if ($i == 0)
            echo 'fieldLabel:"' . $osC_Language->get('field_coupons_description') . '",';
          else
            echo 'fieldLabel: "&nbsp;",';  
          
          echo 'labelStyle: \'background: url(../images/worldflags/' . $l['country_iso'] . '.png) no-repeat right center !important;\', width: "98%"});';
          echo 'this.fsGeneral.add(couponDesc' . $l['id'] . ');';
          
          $i++;
        }       
    ?>
    
    return this.fsGeneral;
  },
  
  onRdbTypePercentageChecked: function (checkbox, checked) {
    var lblAmount = this.txtAmount.getEl().up('div.x-form-item').first();
                  
    if (checked) {
      this.rdbTaxNo.setDisabled(true);
      lblAmount.update('<?php echo str_replace(':', '', $osC_Language->get('field_coupons_amount')) . '(%)' . ':'; ?>');
    }else {
      this.rdbTaxNo.enable();
      lblAmount.update('<?php echo $osC_Language->get('field_coupons_amount'); ?>');
    }
  },
  
  onRdbTypeFreeShippingChecked: function (checkbox, checked) {
    var rdbGroup = [this.rdbTaxYes, this.rdbTaxNo, this.rdbShippingYes, this.rdbShippingNo, this.txtAmount];
                
    if (checked) {
      Ext.each(rdbGroup, function(radio) {
        radio.setDisabled(true);
      });
    }else {
      Ext.each(rdbGroup, function(radio) {
        radio.setDisabled(false);
      });
    }
  },
  
  getDataPanel: function() {      
    var pnlData = new Ext.Panel({
      title: '<?php echo $osC_Language->get('fieldset_heading_data'); ?>',
      layout: 'form',
      autoHeight: true,
      layoutConfig: {
        labelSeparator: ' '
      },
      labelWidth: 120,
      items: [
        {
          layout: 'column',
          border: false,                        
          items: [
            {
              layout: 'form',
              width: 200,
              labelSeparator: ' ',
              border: false,            
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_status',
                  fieldLabel: '<?php echo $osC_Language->get('field_coupons_status'); ?>',
                  boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>',
                  inputValue: '1',
                  checked: true
                }
              ]
            },
            {
              layout: 'form',
              width: 80,     
              border: false,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_status',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>',
                  inputValue: '0'                                    
                }
              ]
            }
          ]            
        },            
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              width: 200,
              labelSeparator: ' ',
              border: false,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_type',
                  fieldLabel: '<?php echo $osC_Language->get('field_coupons_type'); ?>',
                  boxLabel: '<?php echo $osC_Language->get('coupon_type_amount'); ?>',
                  inputValue: '0',
                  checked: true
                }                
              ]
            },
            {
              layout: 'form',                  
              width: 100,
              border: false,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_type',                 
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('coupon_type_percentage'); ?>',
                  inputValue: '1',
                  listeners: {
                    check: {
                      fn: this.onRdbTypePercentageChecked,
                      scope: this
                    }
                  }
                }
              ]
            },
            {
              layout: 'form',
              width: 80,
              border: false,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_type',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('coupon_type_freeship'); ?>',
                  inputValue: '2',
                  listeners: {
                    check: {
                      fn: this.onRdbTypeFreeShippingChecked,
                      scope: this
                    }
                  }
                }
              ]              
            }
          ]
        },
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              width: 200,
              labelSeparator: ' ',
              border: false,
              items: [
                this.rdbTaxYes = new Ext.form.Radio({
                  name: 'coupons_include_tax', 
                  fieldLabel: '<?php echo $osC_Language->get('field_include_tax'); ?>', 
                  boxLabel: '<?php echo $osC_Language->get('yes'); ?>', 
                  inputValue: '1',
                  checked: true
                })
              ]                                         
            },
            {
              layout: 'form',
              width: 60,
              border: false,
              items: [
                this.rdbTaxNo = new Ext.form.Radio({
                  name: 'coupons_include_tax',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('no'); ?>',
                  inputValue: '0'
                })
              ]                            
            }            
          ]
        },
        
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              width: 200,
              border: false,
              labelSeparator: ' ',
              items: [
                this.rdbShippingYes = new Ext.form.Radio({
                  name: 'coupons_include_shipping',
                  fieldLabel: '<?php echo $osC_Language->get('field_include_shipping');?>',
                  boxLabel: '<?php echo $osC_Language->get('yes'); ?>',
                  inputValue: '1',
                  checked: true
                })
              ]                         
            },
            {
              layout: 'form',
              width: 90,
              border: false,
              items: [
                this.rdbShippingNo = new Ext.form.Radio({
                  name: 'coupons_include_shipping',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('no'); ?>',
                  inputValue: '0'
                })
              ]                          
            }
          ]
        },        
        {
          layout: 'column',
          border: false,
          width: '100%',
          items: [
            {
              layout: 'form',
              border: false,
              labelSeparator: ' ',
              width: '100%',
              items: [
               this.txtAmount = new Ext.form.TextField({
                 name: 'coupons_amount',
                 width: '98.2%',
                 fieldLabel: '<?php echo $osC_Language->get('field_coupons_amount'); ?>'                  
               })
              ]
            }
          ]
        },
        
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              border: false,
              labelSeparator: ' ',
              width: '50%',
              items: [
                 {
                  xtype: 'textfield',
                  width: '95%',
                  name: 'coupons_code',
                  fieldLabel: '<?php echo $osC_Language->get('field_coupons_code'); ?>'
                }
              ]
            },            
            {
              layout: 'form',
              border: false,
              labelSeparator: ' ',
              width: '50%',
              items: [
                {
                  xtype: 'textfield',
                  width: '95%',                  
                  name: 'coupons_minimum_order',
                  fieldLabel: '<?php echo $osC_Language->get('field_coupons_minimum_order'); ?>'
                }
              ]
            }
          ]
        },        
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              border: false,
              labelSeparator: ' ',
              width: '50%',
              items: [
                {
                  xtype: 'textfield',
                  name: 'uses_per_coupon',
                  width: '95%',
                  fieldLabel: '<?php echo $osC_Language->get('field_uses_per_coupon'); ?>'
                }
              ]
            },
            {
              layout: 'form',
              border: false,
              labelSeparator: ' ',
              width: '50%',
              items: [
                {
                  xtype: 'textfield',
                  name: 'uses_per_customer',
                  width: '95%',
                  fieldLabel: '<?php echo $osC_Language->get('field_uses_per_customer');?>'
                }    
              ]
            }            
          ]
        },
        {
          layout: 'column',
          border: false,         
          items: [
            {
              layout: 'form',
              border: false,
              width: '50%',
              labelSeparator: ' ',
              items: [
                 {
                  xtype: 'datefield',
                  name: 'start_date',
                  editable: false,
                  width: 193,
                  format: 'Y-m-d',
                  fieldLabel: '<?php echo $osC_Language->get('field_start_date');?>'
                }
              ]
            },
            {
              layout: 'form',
              border: false,
              width: '50%',
              labelSeparator: ' ',
              items: [
                {
                  xtype: 'datefield',
                  name: 'expires_date',
                  editable: false,
                  width: 193,
                  format: 'Y-m-d',
                  fieldLabel: '<?php echo $osC_Language->get('field_expires_date');?>'
                }
              ]
            }
          ]
        } 
      ]   
    });
    
    return pnlData;
  },
  
  onRdbRestrictionNoneChecked: function(checkbox, checked) {
    if (checked) {
      this.grdRestriction.getStore().removeAll();
      this.grdRestriction.getColumnModel().setColumnHeader(1, '&nbsp;');
      
      this.restrictionType = <?php echo COUPONS_RESTRICTION_NONE; ?>;
      
      this.btnRestrictionGridAdd.disable();
      this.btnRestrictionGridDelete.disable();
    }
  },
  
  onRdbRestrictionCategoriesChecked: function(checkbox, checked) {
    if (checked) {
      this.grdRestriction.getStore().removeAll();
      this.grdRestriction.getColumnModel().setColumnHeader(1, '<?php echo $osC_Language->get("table_heading_categories"); ?>');
      
      this.restrictionType = <?php echo COUPONS_RESTRICTION_CATEGOREIS; ?>;
      
      this.btnRestrictionGridAdd.enable();
      this.btnRestrictionGridDelete.enable();
    }
  },
  
  onRdbRestrictionProductsChecked: function(checkbox, checked) {
    if (checked) {
      this.grdRestriction.getStore().removeAll();
      this.grdRestriction.getColumnModel().setColumnHeader(1, '<?php echo $osC_Language->get("table_heading_products"); ?>');
      
      this.restrictionType = <?php echo COUPONS_RESTRICTION_PRODUCTS; ?>;
      
      this.btnRestrictionGridAdd.enable();
      this.btnRestrictionGridDelete.enable();
    }
  },
  
  getRestrictionPanel: function() {
    var pnlRestriction = new Ext.Panel({
      title: '<?php echo $osC_Language->get('fieldset_heading_restriction'); ?>',
      layout: 'form',
      autoHeight: true,
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            {
              layout: 'form',
              border: false,
              width: 185,
              labelSeparator: ' ',
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_restrictions',
                  fieldLabel: '<?php echo $osC_Language->get('field_coupons_restriction'); ?>',
                  boxLabel: '<?php echo $osC_Language->get('heading_coupons_to_all'); ?>',
                  inputValue: '<?php echo COUPONS_RESTRICTION_NONE; ?>',
                  checked: true,
                  listeners: {
                    check: {
                      fn: this.onRdbRestrictionNoneChecked,
                      scope: this
                    }
                  }
                }
              ]
            },
            {
              layout: 'form',
              border: false,
              width: 100,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_restrictions',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('heading_coupons_to_categories'); ?>',
                  inputValue: '<?php echo COUPONS_RESTRICTION_CATEGOREIS; ?>',
                  listeners: {
                    check: {
                      fn: this.onRdbRestrictionCategoriesChecked,
                      scope: this
                    }
                  }                                   
                }
              ]
            },
            {
              layout: 'form',
              border: false,
              width: 90,
              items: [
                {
                  xtype: 'radio',
                  name: 'coupons_restrictions',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('heading_coupons_to_products'); ?>',
                  inputValue: '<?php echo COUPONS_RESTRICTION_PRODUCTS; ?>',
                  listeners: {
                    check: {
                      fn: this.onRdbRestrictionProductsChecked,
                      scope: this
                    }
                  }                                   
                }                
              ]
            }
          ]
        },
        this.grdRestriction = this.getRestrictionGrid()
      ]
    });
    
    return pnlRestriction;
  },
  
  getRestrictionGrid: function() {
    var rowActions = new Ext.ux.grid.RowActions({
      actions: [
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onRestrictionGridRowAction, this); 
  
    var data = [];
    var sm = new Ext.grid.CheckboxSelectionModel();
    var grdRestriction = new Ext.grid.GridPanel({
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      width: '96%',
      height: 200,
      autoScroll: true,
      style: 'margin: 1% 1%;border: 1px solid #BBBBBB;',
      ds: new Ext.data.SimpleStore({
        data: data,
        id: 'id',
        fields: [
          'id',
          'name'
        ]
      }),
      plugins: rowActions,
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {header: '<?php echo $osC_Language->get("table_heading_categories"); ?>', dataIndex: 'name', width: 585},
        rowActions 
      ]),
      tbar: [
        this.btnRestrictionGridAdd = new Ext.Button({
          text: TocLanguage.btnAdd,
          iconCls: 'add',
          handler: this.onRestrictionGridAdd,
          scope: this
        }),
        this.btnRestrictionGridDelete = new Ext.Button({
          text: TocLanguage.btnDelete,
          iconCls: 'remove',
          handler: this.onRestrictionGridBatchDelete,
          scope: this
        })
      ]    
    });
    
    return grdRestriction;
  },
  
  onRestrictionGridAdd: function() {
    if (this.restrictionType == <?php echo COUPONS_RESTRICTION_CATEGOREIS; ?>) {
      var dlg = this.owner.createCategoriesDialog();

      dlg.on('save', function(records) {
        Ext.each(records, function(record) {
          var categories_id = record.get('categories_id');
          var categories_name = record.get('categories_name');
          var store = this.grdRestriction.getStore();
          
          if (store.find('id', categories_id) == -1) {
            var record = Ext.data.Record.create([
              {name: 'id', type: 'string'},
              {name: 'name', type: 'string'}
            ]);
            
            var v = new record({id: categories_id, name: categories_name});
            store.add(v);
          }
        }, this);
      }, this);
      
      dlg.show();
    }else if (this.restrictionType == <?php echo COUPONS_RESTRICTION_PRODUCTS; ?>) {
      var dlg = this.owner.createProductsDialog();
      
      dlg.on('save', function(records) {
        Ext.each(records, function(record) {
          var products_id = record.get('products_id');
          var products_name = record.get('products_name');
          var store = this.grdRestriction.getStore();
          
          if (store.find('id', products_id) == -1) {
            var record = Ext.data.Record.create([
              {name: 'id', type: 'string'},
              {name: 'name', type: 'string'}
            ]);
            
            var v = new record({id: products_id, name: products_name});
            store.add(v);
          }
        }, this);
      }, this);
      
      dlg.show();
    } 
  },
  
  onRestrictionGridBatchDelete: function() {
    var records = this.grdRestriction.getSelectionModel().getSelections();
    var store = this.grdRestriction.getStore();
    
    Ext.each(records, function(record) {
      store.remove(record);
    });
  },
  
  onRestrictionGridRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.grdRestriction.getStore().removeAt(row);
        break;
    }
  },
  
  getTabPanel: function() {
    var pnlTab = new Ext.TabPanel({
      border: false,
      deferredRender: false,
      plain: true,
      activeTab: 0,
      items: [this.getDataPanel(), this.getRestrictionPanel()]
    });
    
    return pnlTab;
  },
  
  buildForm: function() {
    this.frmCoupons = new Ext.form.FormPanel({       
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'coupons',
        action : 'save_coupons'
      }, 
      border: false,
      autoHeight: true,
      autoWidth: true,
      items: [this.getGeneralPanel(), this.getTabPanel()]
    });        
       
    return this.frmCoupons;
  },       
  
  submitForm : function() {
    var ids = [];
    this.grdRestriction.getStore().each(function(record) {
      ids.push(record.get('id'));
    });
        
    this.frmCoupons.form.baseParams['coupons_restrictions'] = this.restrictionType;
    
    if (this.restrictionType == <?php echo COUPONS_RESTRICTION_CATEGOREIS; ?>) {
      this.frmCoupons.form.baseParams['categoriesIds'] = ids.join(',');
    }else if (this.restrictionType == <?php echo COUPONS_RESTRICTION_PRODUCTS; ?>) {
      this.frmCoupons.form.baseParams['productsIds'] = ids.join(',');
    }

    this.frmCoupons.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
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