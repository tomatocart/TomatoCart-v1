<?php
/*
  $Id: products_attachments.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Product_Attachments_Admin {
    function getData($id) {
      global $osC_Database;

      $Qattach = $osC_Database->query('select pa.*, pad.* from :table_products_attachments pa inner join :table_products_attachments_description pad on pa.attachments_id = pad.attachments_id and pa.attachments_id = :id');
      $Qattach->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
      $Qattach->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
      $Qattach->bindInt(':id', $id);
      $Qattach->execute();

      $data = $Qattach->toArray();
      
      $Qad = $osC_Database->query('select attachments_name, attachments_description, languages_id from :table_products_attachments_description where attachments_id = :attachments_id');
      $Qad->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
      $Qad->bindInt(':attachments_id', $id);
      $Qad->execute();
      
      while ($Qad->next()) {
        $data['attachments_name[' . $Qad->valueInt('languages_id') . ']'] = $Qad->value('attachments_name');
        $data['attachments_description[' . $Qad->valueInt('languages_id') . ']'] = $Qad->value('attachments_description');
      }

      $Qattach->freeResult();

      return $data;
    }
  
    function save($id, $data) {
      global $osC_Database, $osC_Language;
      
      $error = false;
      if ($data['attachments_file']) {
        $file = new upload($data['attachments_file']);
          
        if ($file->exists()) {
          //remove old attachment file
          if (is_numeric($id)) {
            $Qfile = $osC_Database->query('select cache_filename from :table_products_attachments where attachments_id = :id');
            $Qfile->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
            $Qfile->bindInt(':id', $id);
            $Qfile->execute();
          
            if ($Qfile->numberOfRows() == 1) {
              @unlink(DIR_FS_CACHE . '/products_attachments/' . $Qfile->value('cache_filename'));
            }
          }
        
          $file->set_destination(realpath(DIR_FS_CACHE . '/products_attachments'));

          if ( $file->parse() && $file->save() ) {
            $filename = $file->filename;
            $cache_filename = md5($filename . time());
            
            @rename(DIR_FS_CACHE . 'products_attachments/' . $file->filename, DIR_FS_CACHE . '/products_attachments/' . $cache_filename);
            
            if (is_numeric($id)) {
              $Qattachment = $osC_Database->query('update :table_products_attachments set filename  = :filename , cache_filename = :cache_filename where attachments_id = :id');
              $Qattachment->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
              $Qattachment->bindInt(':id', $id);
            } else {
              $Qattachment = $osC_Database->query('insert into :table_products_attachments (filename, cache_filename) values (:filename, :cache_filename)');
              $Qattachment->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
            }
            
            $Qattachment->bindValue(':filename', $filename);
            $Qattachment->bindValue(':cache_filename', $cache_filename);
            $Qattachment->setLogging($_SESSION['module'], $id);
            $Qattachment->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        } 
      }

      if ( $error === false ) {
        $attachments_id = is_numeric($id) ? $id : $osC_Database->nextID();
        
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qad = $osC_Database->query('update :table_products_attachments_description set attachments_name = :attachments_name, attachments_description  = :attachments_description where attachments_id = :id and languages_id = :language_id');
          } else {
            $Qad = $osC_Database->query('insert into :table_products_attachments_description (attachments_id, languages_id, attachments_name, attachments_description) values (:id, :language_id, :attachments_name, :attachments_description)');
          }

          $Qad->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
          $Qad->bindInt(':id', $attachments_id);
          $Qad->bindInt(':language_id', $l['id']);
          $Qad->bindValue(':attachments_name', $data['attachments_name'][$l['id']]);
          $Qad->bindValue(':attachments_description', $data['attachments_description'][$l['id']]);
          $Qad->setLogging($_SESSION['module'], $attachments_id);
          $Qad->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }        
      } else {
        $error = true;
      }
      
      if ( $error === false ) {
        $osC_Database->commitTransaction();
        
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function delete($id, $filename) {
      global $osC_Database, $osC_Image;
      
      $error = false;

      $osC_Database->startTransaction();
      
      $Qad = $osC_Database->query('delete from :table_products_attachments_description where attachments_id = :attachments_id');
      $Qad->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
      $Qad->bindInt(':attachments_id', $id);
      $Qad->setLogging($_SESSION['module'], $id);
      $Qad->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $Qa = $osC_Database->query('delete from :table_products_attachments where attachments_id = :attachments_id');
        $Qa->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
        $Qa->bindInt(':attachments_id', $id);
        $Qa->setLogging($_SESSION['module'], $id);
        $Qa->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }

        if ( $error === false ) {
          $osC_Database->commitTransaction();

          @unlink(DIR_FS_CACHE . 'products_attachments/' . $filename);
          
          return true;
        }
      }
      $osC_Database->rollbackTransaction();
      
      return false;
    }
  }
?>