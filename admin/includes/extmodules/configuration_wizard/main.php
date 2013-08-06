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

  echo 'Ext.namespace("Toc.configuration_wizard");';
  include('store_information_card.php');
  include('email_options_card.php');
  include('shipping_and_packaging_card.php');
  include('configuration_wizard_dialog.php');
?>

Ext.override(TocDesktop.ConfigurationWizardWindow, {

  createWindow: function() {
  
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('configuration_wizard-win');

    if (!win) {
      win = desktop.createWindow({owner: this}, Toc.configuration_wizard.ConfigurationWizardDialog);
    }
    
    win.show();
  }
});