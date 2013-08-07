<?php
/*
  $Id: manufacturers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Manufacturers_Admin {
    function getData($id, $language_id = null) {
      global $osC_Database, $osC_Language;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qmanufacturers = $osC_Database->query('select m.*, mi.* from :table_manufacturers m, :table_manufacturers_info mi where m.manufacturers_id = :manufacturers_id and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languages_id');
      $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturers->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qmanufacturers->bindInt(':manufacturers_id', $id);
      $Qmanufacturers->bindInt(':languages_id', $language_id);
      $Qmanufacturers->execute();

      $data = $Qmanufacturers->toArray();

      $Qclicks = $osC_Database->query('select sum(url_clicked) as total from :table_manufacturers_info where manufacturers_id = :manufacturers_id');
      $Qclicks->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qclicks->bindInt(':manufacturers_id', $id);
      $Qclicks->execute();

      $data['url_clicks'] = $Qclicks->valueInt('total');

      $Qproducts = $osC_Database->query('select count(*) as products_count from :table_products where manufacturers_id = :manufacturers_id');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindInt(':manufacturers_id', $id);
      $Qproducts->execute();

      $data['products_count'] = $Qproducts->valueInt('products_count');

      $Qclicks->freeResult();
      $Qproducts->freeResult();
      $Qmanufacturers->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qmanufacturer = $osC_Database->query('update :table_manufacturers set manufacturers_name = :manufacturers_name, last_modified = now() where manufacturers_id = :manufacturers_id');
        $Qmanufacturer->bindInt(':manufacturers_id', $id);
      } else {
        $Qmanufacturer = $osC_Database->query('insert into :table_manufacturers (manufacturers_name, date_added) values (:manufacturers_name, now())');
      }

      $Qmanufacturer->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturer->bindValue(':manufacturers_name', $data['name']);
      $Qmanufacturer->setLogging($_SESSION['module'], $id);
      $Qmanufacturer->execute();

      if ( !$osC_Database->isError() ) {
        if ( is_numeric($id) ) {
          $manufacturers_id = $id;
        } else {
          $manufacturers_id = $osC_Database->nextID();
        }

        $image = new upload('manufacturers_image', realpath('../' . DIR_WS_IMAGES . 'manufacturers'));

        if ( $image->exists() ) {
          if ( $image->parse() && $image->save() ) {
            $Qimage = $osC_Database->query('update :table_manufacturers set manufacturers_image = :manufacturers_image where manufacturers_id = :manufacturers_id');
            $Qimage->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
            $Qimage->bindValue(':manufacturers_image', $image->filename);
            $Qimage->bindInt(':manufacturers_id', $manufacturers_id);
            $Qimage->setLogging($_SESSION['module'], $manufacturers_id);
            $Qimage->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
            }
          }
        }
      } else {
        $error = true;
      }

      if ( $error === false ) {
        foreach ( $osC_Language->getAll() as $l ) {
          if ( is_numeric($id) ) {
            $Qurl = $osC_Database->query('update :table_manufacturers_info set manufacturers_friendly_url = :manufacturers_friendly_url, manufacturers_url = :manufacturers_url, manufacturers_page_title = :manufacturers_page_title, manufacturers_meta_keywords = :manufacturers_meta_keywords, manufacturers_meta_description = :manufacturers_meta_description where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
          } else {
            $Qurl = $osC_Database->query('insert into :table_manufacturers_info (manufacturers_id, languages_id, manufacturers_url, manufacturers_friendly_url, manufacturers_page_title, manufacturers_meta_keywords, manufacturers_meta_description) values (:manufacturers_id, :languages_id, :manufacturers_url, :manufacturers_friendly_url, :manufacturers_page_title, :manufacturers_meta_keywords, :manufacturers_meta_description)');
          }

          $Qurl->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
          $Qurl->bindInt(':manufacturers_id', $manufacturers_id);
          $Qurl->bindInt(':languages_id', $l['id']);
          $Qurl->bindValue(':manufacturers_url', $data['url'][$l['id']]);
          $Qurl->bindValue(':manufacturers_friendly_url', $data['friendly_url'][$l['id']]);
          $Qurl->bindValue(':manufacturers_page_title', $data['page_title'][$l['id']]);
          $Qurl->bindValue(':manufacturers_meta_keywords', $data['meta_keywords'][$l['id']]);
          $Qurl->bindValue(':manufacturers_meta_description', $data['meta_description'][$l['id']]);
          $Qurl->setLogging($_SESSION['module'], $manufacturers_id);
          $Qurl->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('box-manufacturers');
        osC_Cache::clear('sefu-manufacturers');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id, $delete_image = false, $delete_products = false) {
      global $osC_Database;

      if ( $delete_image === true ) {
        $Qimage = $osC_Database->query('select manufacturers_image from :table_manufacturers where manufacturers_id = :manufacturers_id');
        $Qimage->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
        $Qimage->bindInt(':manufacturers_id', $id);
        $Qimage->execute();

        if ( $Qimage->numberOfRows() && !osc_empty($Qimage->value('manufacturers_image')) ) {
          if ( file_exists(realpath('../' . DIR_WS_IMAGES . 'manufacturers/' . $Qimage->value('manufacturers_image'))) ) {
            @unlink(realpath('../' . DIR_WS_IMAGES . 'manufacturers/' . $Qimage->value('manufacturers_image')));
          }
        }
      }

      $Qm = $osC_Database->query('delete from :table_manufacturers where manufacturers_id = :manufacturers_id');
      $Qm->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qm->bindInt(':manufacturers_id', $id);
      $Qm->setLogging($_SESSION['module'], $id);
      $Qm->execute();

      $Qmi = $osC_Database->query('delete from :table_manufacturers_info where manufacturers_id = :manufacturers_id');
      $Qmi->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qmi->bindInt(':manufacturers_id', $id);
      $Qmi->setLogging($_SESSION['module'], $id);
      $Qmi->execute();

      if ( $delete_products === true ) {
        $Qproducts = $osC_Database->query('select products_id from :table_products where manufacturers_id = :manufacturers_id');
        $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
        $Qproducts->bindInt(':manufacturers_id', $id);
        $Qproducts->execute();

        while ( $Qproducts->next() ) {
          osC_Products_Admin::delete($Qproducts->valueInt('products_id'));
        }
      } else {
        $Qupdate = $osC_Database->query('update :table_products set manufacturers_id = null where manufacturers_id = :manufacturers_id');
        $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
        $Qupdate->bindInt(':manufacturers_id', $id);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();
      }

      osC_Cache::clear('box-manufacturers');
      osC_Cache::clear('sefu-manufacturers');

      return true;
    }

    function getManufacturersData(){
      global $osC_Database;
      
      $Qmanufacturers = $osC_Database->query('select * from :table_manufacturers');
      $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturers->execute();

      return $Qmanufacturers;
    }
  }
?>
