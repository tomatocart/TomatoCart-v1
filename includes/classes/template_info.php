<?php
/*
  $Id: template_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The osC_Template class defines or adds elements to the page output such as the page title, page content, and javascript blocks
 */

  class osC_TemplateInfo {

/**
 * Holds the template id
 *
 * @var string
 * @access private
 */

    var $_id;

/**
 * Holds the template title
 *
 * @var string
 * @access private
 */

    var $_title;

/**
 * Holds the template code
 *
 * @var string
 * @access private
 */

   var $_code;

/**
 * Holds the template author name
 *
 * @var string
 * @access private
 */

   var $_author_name;

/**
 * Holds the template author www
 *
 * @var string
 * @access private
 */

   var $_author_www;

/**
 * Holds the template markup verion
 *
 * @var string
 * @access private
 */

   var $_markup_version;

/**
 * Indicates whether the template is css based or not
 *
 * @var string
 * @access private
 */

   var $_css_based;

/**
 * Holds the template medium
 *
 * @var string
 * @access private
 */

   var $_medium;

/**
 * Holds the template groups
 *
 * @var string
 * @access private
 */

   var $_groups = array();

/**
 * Holds the template groups
 *
 * @var string
 * @access private
 */

   var $_keys;

/**
 * Holds the template logo width
 *
 * @var string
 * @access private
 */

    var $_logo_width;

/**
 * Holds the template logo height
 *
 * @var int
 * @access private
 */

    var $_logo_height;


    function getID() {
      global $osC_Database;

      if (isset($this->_id) === false) {
        $Qtemplate = $osC_Database->query('select id from :table_templates where code = :code');
        $Qtemplate->bindTable(':table_templates', TABLE_TEMPLATES);
        $Qtemplate->bindvalue(':code', $this->_code);
        $Qtemplate->execute();

        $this->_id = $Qtemplate->valueInt('id');
      }

      return $this->_id;
    }

    function getTitle() {
      return $this->_title;
    }

    function getCode() {
      return $this->_code;
    }

    function getLogoHeight() {
      return $this->_logo_height;
    }

    function getLogoWidth(){
      return $this->_logo_width;
    }

    function getAuthorName() {
      return $this->_author_name;
    }

    function getAuthorAddress() {
      return $this->_author_www;
    }

    function getMarkup() {
      return $this->_markup_version;
    }

    function isCSSBased() {
      return ($this->_css_based == '1');
    }

    function getMedium() {
      return $this->_medium;
    }

    function getGroups($group) {
      return $this->_groups[$group];
    }

    function resizeLogo() {
      $osC_DirectoryListing = new osC_DirectoryListing('../images');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $files = $osC_DirectoryListing->getFiles(true);

      foreach ( $files as $file ) {
        $filename = explode(".", $file['name']);
        if($filename[0] == 'logo_originals'){
          $img_type = $filename[sizeof($filename)-1];

          $original_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $file['name'];
          $dest_image = DIR_FS_CATALOG . DIR_WS_IMAGES . 'logo_' . $this->getCode() . '.' . $img_type;

          osc_gd_resize($original_image, $dest_image, $this->getLogoWidth(), $this->getLogoHeight());
        }
      }
    }

    function deleteLogo() {
      $osC_DirectoryListing = new osC_DirectoryListing('../images');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $files = $osC_DirectoryListing->getFiles(true);

      $logo = 'logo_' . $this->getCode();

      foreach ( $files as $file ) {
        $filename = explode(".", $file['name']);

        if($filename[0] == $logo)
          @unlink(DIR_FS_CATALOG . 'images/' . $file['name']);
      }
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array();
      }

      return $this->_keys;
    }

    function hasKeys() {
      static $has_keys;

      if (isset($has_keys) === false) {
        $has_keys = (sizeof($this->getKeys()) > 0) ? true : false;
      }

      return $has_keys;
    }

    function isInstalled() {
      global $osC_Database;

      static $is_installed;

      if (isset($is_installed) === false) {
        $Qcheck = $osC_Database->query('select id from :table_templates where code = :code');
        $Qcheck->bindTable(':table_templates', TABLE_TEMPLATES);
        $Qcheck->bindValue(':code', $this->_code);
        $Qcheck->execute();

        $is_installed = ($Qcheck->numberOfRows()) ? true : false;
      }

      return $is_installed;
    }

    function isActive() {
      return true;
    }

    function remove() {
      global $osC_Database;

      $Qdel = $osC_Database->query('delete from :table_templates_boxes_to_pages where templates_id = :templates_id');
      $Qdel->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qdel->bindValue(':templates_id', $this->getID());
      $Qdel->execute();

      $Qdel = $osC_Database->query('delete from :table_templates where id = :id');
      $Qdel->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qdel->bindValue(':id', $this->getID());
      $Qdel->execute();

      if ($this->hasKeys()) {
        $Qdel = $osC_Database->query('delete from :table_configuration where configuration_key in (":configuration_key")');
        $Qdel->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qdel->bindRaw(':configuration_key', implode('", "', $this->getKeys()));
        $Qdel->execute();
      }

      $this->deleteLogo();
    }
  }
?>
