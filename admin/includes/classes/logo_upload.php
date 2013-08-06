<?php
/*
  $Id: logo_upload.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Logo_Upload {

  function upload(){
    $logo_image = new upload('logo_image');

    if ( $logo_image->exists() ) {
      self::deleteLogo('originals');

      $img_type = substr($_FILES['logo_image']['name'], ( strrpos($_FILES['logo_image']['name'], '.') + 1 ));
      $original = DIR_FS_CATALOG . DIR_WS_IMAGES . 'logo_originals.' . $img_type;

      $logo_image->set_destination(realpath(DIR_FS_CATALOG . 'images/'));

      if ( $logo_image->parse() && $logo_image->save() ) {
        copy(DIR_FS_CATALOG . 'images/' . $logo_image->filename, $original);
        @unlink(DIR_FS_CATALOG . 'images/' . $logo_image->filename);

        $osC_DirectoryListing = new osC_DirectoryListing('../templates');
        $osC_DirectoryListing->setIncludeDirectories(true);
        $osC_DirectoryListing->setIncludeFiles(false);
        $osC_DirectoryListing->setExcludeEntries('system');

        $templates = $osC_DirectoryListing->getFiles();

        foreach ($templates as $template) {
          $code = $template['name'];
          if( file_exists('../templates/' . $code . '/template.php') ){
            include('../templates/' . $code . '/template.php');
            $class = 'osC_Template_' . $code;

            self::deleteLogo($code);

            if ( class_exists($class) ) {
              $module = new $class();

              $logo_height = $module->getLogoHeight();
              $logo_width = $module->getLogoWidth();

              $dest_image = DIR_FS_CATALOG . DIR_WS_IMAGES . 'logo_' . $code . '.' . $img_type;

              osc_gd_resize($original, $dest_image, $logo_width, $logo_height);
            }
          }
        }
        return true;
      }
    }

    return false;
  }

  function deleteLogo($code) {
    $osC_DirectoryListing = new osC_DirectoryListing('../' . DIR_WS_IMAGES);
    $osC_DirectoryListing->setIncludeDirectories(false);
    $files = $osC_DirectoryListing->getFiles();

    $logo = 'logo_' . $code;

    foreach ( $files as $file ) {
      $filename = explode(".", $file['name']);

      if($filename[0] == $logo){
        $image_dir  = DIR_FS_CATALOG . 'images/';
        @unlink($image_dir . $file['name']);
      }
    }
  }

  function getOriginalLogo() {
    $osC_DirectoryListing = new osC_DirectoryListing('../' . DIR_WS_IMAGES);
    $osC_DirectoryListing->setIncludeDirectories(false);
    $files = $osC_DirectoryListing->getFiles();

    foreach ( $files as $file ) {
      $filename = explode(".", $file['name']);

      if($filename[0] == 'logo_originals'){
        return '../' . DIR_WS_IMAGES . 'logo_originals.' . $filename[1];
      }
    }

    return false;
  }
}
?>
