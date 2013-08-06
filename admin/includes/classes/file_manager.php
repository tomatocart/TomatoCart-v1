<?php
/*
  $Id: file_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_FileManager_Admin {
    function createDirectory($name, $path) {
      if ( is_writeable($path) ) {
        $new_directory = $path . '/' . basename($name);

        if ( !is_dir($new_directory) ) {
          if ( mkdir($new_directory, 0777) ) {
            return true;
          }
        }
      }

      return false;
    }

    function saveFile($filename, $contents, $directory) {
      if ( $fp = fopen($directory . '/' . $filename, 'w+') ) {
        fputs($fp, $contents);
        fclose($fp);

        return true;
      }

      return false;
    }

    function storeFileUpload($file, $directory) {
      if ( is_writeable($directory) ) {
        $upload = new upload($file, $directory);

        if ( $upload->exists() && $upload->parse() && $upload->save() ) {
          return true;
        }
      }

      return false;
    }

    function delete($entry, $directory) {
      $target = $directory . '/' . basename($entry);

      if ( is_writeable($target) ) {
        osc_remove($target);

        return true;
      }

      return false;
    }
  
  }
  
?>
