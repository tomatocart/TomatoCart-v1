<?php
/*
  $Id: language.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('../admin/includes/classes/language.php');

  class osC_LanguageInstall extends osC_Language_Admin {

/* Private variables */
    var $_languages = array();

/* Class constructor */

    function osC_LanguageInstall() {
      $osC_DirectoryListing = new osC_DirectoryListing('../includes/languages');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $osC_DirectoryListing->setCheckExtension('xml');

      foreach ($osC_DirectoryListing->getFiles() as $file) {
        $osC_XML = new osC_XML(file_get_contents('../includes/languages/' . $file['name']));
        $lang = $osC_XML->toArray();

        $this->_languages[$lang['language']['data']['code']] = array('name' => $lang['language']['data']['title'],
                                                                     'code' => $lang['language']['data']['code'],
                                                                     'charset' => $lang['language']['data']['character_set']);
      }

      unset($lang);

      $language = (isset($_GET['language']) && !empty($_GET['language']) ? $_GET['language'] : '');

      $this->set($language);

      $this->loadIniFile();
      $this->loadIniFile(basename($_SERVER['SCRIPT_FILENAME']));
    }
  }
?>
