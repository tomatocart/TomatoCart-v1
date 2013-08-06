<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.languages");';
  
  include('languages_add_dialog.php');
  include('languages_upload_dialog.php');
  include('languages_edit_dialog.php');
  include('translations_dialog.php');
  include('modules_tree_panel.php');
  include('translations_edit_grid.php');
  include('translation_edit_dialog.php');
  include('translation_add_dialog.php');
  include('languages_export_dialog.php');
  include('languages_grid.php');
?>

Ext.override(TocDesktop.LanguagesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('languages-win');
     
    if (!win) {
      grd = new Toc.languages.LanguagesGrid({owner: this});

      win = desktop.createWindow({
        id: 'languages-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-languages-win',
        layout: 'fit',
        items: grd
      });
    }
       
    win.show();
  },
  
  createLanguagesAddDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('languages-add-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.languages.LanguagesAddDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
    
  createLanguagesUploadDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('languages-upload-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.languages.LanguagesUploadDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createLanguagesEditDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('languages-edit-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.languages.LanguagesEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createTranslationsDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('translations-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.languages.TranslationsEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createLanguagesExportDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('languages-export-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.languages.LanguagesExportDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createTranslationEditDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('translation-edit-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.languages.TranslationEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createTranslationAddDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('translation-add-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.languages.TranslationAddDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  }
});
