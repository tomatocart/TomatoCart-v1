<?php
/*
  $Id: translations_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.languages.TranslationsEditDialog = function(config) {
  config = config || {};
  
  config.id = 'translations-win';
  config.layout = 'border';
  config.border = false;
  config.height = 400;
  config.width = 850;
  config.modal = true;
  config.iconCls = 'icon-languages-win';

  config.grdTranslations = new Toc.languages.TranslationsEditGrid({languagesId: config.languagesId, languagesName:config.languagesName, owner: config.owner});
  config.pnlModulesTree = new Toc.languages.ModulesTreePanel({languagesId: config.languagesId, languagesName:config.languagesName, grdTranslations: config.grdTranslations});
  
  config.items = [config.pnlModulesTree, config.grdTranslations];
  
  Toc.languages.TranslationsEditDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.languages.TranslationsEditDialog, Ext.Window);