<?php
/*
  $Id: coupons.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Coupons_Admin {
    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qcoupons = $osC_Database->query('select * from :table_coupons c left join :table_coupons_description cd on c.coupons_id=cd.coupons_id where c.coupons_id = :coupons_id and cd.language_id=:language_id ');
      $Qcoupons->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
      $Qcoupons->bindInt(':language_id', $osC_Language->getID());
      $Qcoupons->bindInt(':coupons_id', $id);
      $Qcoupons->execute();

      $result = $Qcoupons->toArray();
      $Qcoupons->freeResult();

      return $result;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;
      $osC_Database->startTransaction();

      if (is_numeric($id)) {
        $Qcoupons = $osC_Database->query('update :table_coupons set coupons_status=:coupons_status, coupons_type =:coupons_type, coupons_amount = :coupons_amount, coupons_code= :coupons_code, coupons_amount = :coupons_amount , coupons_include_tax = :coupons_include_tax, coupons_include_shipping = :coupons_include_shipping, coupons_minimum_order=:coupons_minimum_order, uses_per_coupon=:uses_per_coupon , uses_per_customer=:uses_per_customer,start_date=:start_date, expires_date=:expires_date, coupons_date_modified=:coupons_date_modified  where coupons_id = :coupons_id');
        $Qcoupons->bindInt(':coupons_id', $id);
      } else {
        $Qcoupons = $osC_Database->query('insert into :table_coupons (coupons_status, coupons_type,  coupons_code, coupons_amount, coupons_include_tax, coupons_include_shipping, coupons_minimum_order, uses_per_coupon, uses_per_customer, start_date,expires_date, coupons_date_created,coupons_date_modified) values (:coupons_status, :coupons_type, :coupons_code, :coupons_amount, :coupons_include_tax, :coupons_include_shipping, :coupons_minimum_order, :uses_per_coupon, :uses_per_customer, :start_date, :expires_date, :coupons_date_created, :coupons_date_modified)');
        $Qcoupons->bindRaw(':coupons_date_created', 'now()');
      }

      $Qcoupons->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupons->bindValue(':coupons_status', $data['coupons_status']);
      $Qcoupons->bindValue(':coupons_code', $data['coupons_code']);
      $Qcoupons->bindValue(':coupons_amount', $data['coupons_amount']);
      $Qcoupons->bindValue(':coupons_include_tax', $data['coupons_include_tax']);
      $Qcoupons->bindValue(':coupons_include_shipping', $data['coupons_include_shipping']);
      $Qcoupons->bindValue(':coupons_minimum_order', $data['coupons_minimum_order']);
      $Qcoupons->bindInt(':uses_per_coupon', $data['uses_per_coupon']);
      $Qcoupons->bindInt(':uses_per_customer', $data['uses_per_customer']);
      $Qcoupons->bindValue(':start_date', $data['start_date']);
      $Qcoupons->bindValue(':expires_date', $data['expires_date']);
      $Qcoupons->bindInt(':coupons_type', $data['coupons_type']);
      $Qcoupons->bindValue(':coupons_amount', $data['coupons_amount']);
      $Qcoupons->bindRaw(':coupons_date_modified', 'now()');
      $Qcoupons->setLogging($_SESSION['module'], $id);
      $Qcoupons->execute();

      if ($osC_Database->isError()) {
        $error = true;
      } else {
        if (is_numeric($id)) {
          $coupons_id = $id;
        } else {
          $coupons_id = $osC_Database->nextID();
        }
      }

      if ($error === false) {
        foreach ($osC_Language->getAll() as $l) {
          if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_coupons_description set coupons_name = :coupons_name, coupons_description = :coupons_description where coupons_id = :coupons_id and language_id = :language_id');
          } else {
            $Qcd = $osC_Database->query('insert into :table_coupons_description (coupons_id, language_id, coupons_name, coupons_description) values (:coupons_id, :language_id, :coupons_name, :coupons_description)');
          }

          $Qcd->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
          $Qcd->bindInt(':coupons_id', $coupons_id);
          $Qcd->bindInt(':language_id', $l['id']);
          $Qcd->bindValue(':coupons_name', $data['coupons_name'][$l['id']]);
          $Qcd->bindValue(':coupons_description', $data['coupons_description'][$l['id']]);
          $Qcd->setLogging($_SESSION['module'], $coupons_id);
          $Qcd->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }

      if ($error === false) {
        if (is_numeric($id)) {
          $Qpdel = $osC_Database->query('delete from :table_coupons_to_products where coupons_id = :coupons_id');
          $Qpdel->bindTable(':table_coupons_to_products', TABLE_COUPONS_TO_PRODUCTS);
          $Qpdel->bindInt(':coupons_id', $id);
          $Qpdel->setLogging($_SESSION['module'], $id);
          $Qpdel->execute();

          if (!$osC_Database->isError()) {
            $Qcdel = $osC_Database->query('delete from :table_coupons_to_categories where coupons_id = :coupons_id');
            $Qcdel->bindTable(':table_coupons_to_categories', TABLE_COUPONS_TO_CATEGORIES);
            $Qcdel->bindInt(':coupons_id', $id);
            $Qcdel->setLogging($_SESSION['module'], $id);
            $Qcdel->execute();
          }

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }

        if ($error === false) {
          if (isset($data['categories_id_array']) && (!empty($data['categories_id_array']))) {
            foreach ($data['categories_id_array'] as $categories_id) {
              $Qc2c = $osC_Database->query('insert into :table_coupons_to_categories (coupons_id, categories_id) values (:coupons_id, :categories_id )');
              $Qc2c->bindTable(':table_coupons_to_categories', TABLE_COUPONS_TO_CATEGORIES);
              $Qc2c->bindInt(':coupons_id', $coupons_id);
              $Qc2c->bindInt(':categories_id', $categories_id);
              $Qc2c->setLogging($_SESSION['module'], $coupons_id);
              $Qc2c->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }

        if ($error === false) {
          if ( isset($data['products_id_array']) && ( !empty($data['products_id_array']) ) ) {
            foreach($data['products_id_array'] as $products_id) {
              $Qctop = $osC_Database->query('insert into :table_coupons_to_products (coupons_id, products_id) values (:coupons_id, :products_id )');
              $Qctop->bindTable(':table_coupons_to_products', TABLE_COUPONS_TO_PRODUCTS);
              $Qctop->bindInt(':coupons_id', $coupons_id);
              $Qctop->bindInt(':products_id', $products_id);
              $Qctop->setLogging($_SESSION['module'], $coupons_id);
              $Qctop->execute();

              if ($osC_Database->isError()) {

                $error = true;
                break;
              }
            }
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qcoupon = $osC_Database->query('delete from :table_coupons where coupons_id = :coupons_id');
      $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupon->bindInt(':coupons_id', $id);
      $Qcoupon->setLogging($_SESSION['module'], $id);
      $Qcoupon->execute();

      if (!$osC_Database->isError()) {
        $Qcd = $osC_Database->query('delete from :table_coupons_description where coupons_id = :coupons_id');
        $Qcd->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
        $Qcd->bindInt(':coupons_id', $id);
        $Qcd->setLogging($_SESSION['module'], $id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qc2c = $osC_Database->query('delete from :table_coupons_to_categories where coupons_id = :coupons_id');
        $Qc2c->bindTable(':table_coupons_to_categories', TABLE_COUPONS_TO_CATEGORIES);
        $Qc2c->bindInt(':coupons_id', $id);
        $Qc2c->setLogging($_SESSION['module'], $id);
        $Qc2c->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qc2p = $osC_Database->query('delete from :table_coupons_to_products where coupons_id = :coupons_id');
        $Qc2p->bindTable(':table_coupons_to_products', TABLE_COUPONS_TO_PRODUCTS);
        $Qc2p->bindInt(':coupons_id', $id);
        $Qc2p->setLogging($_SESSION['module'], $id);
        $Qc2p->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function setStatus($id, $status) {
      global $osC_Database;

      $Qstatus = $osC_Database->query('update :table_coupons set coupons_status = :coupons_status where coupons_id = :coupons_id');
      $Qstatus->bindTable(':table_coupons', TABLE_COUPONS);
      $Qstatus->bindInt(':coupons_status', ($status == 1) ? 1 : 0);
      $Qstatus->bindInt(':coupons_id', $id);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();

      if ($osC_Database->isError()) {
        return false;
      }

      return true;
    }

    function createCouponCode($length = 12) {
      global $osC_Database;

      srand((double) microtime() * 1000000);
      $rand_str = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true)) . md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

      $coupon_code = '';
      $length = $length - 1;
      $found = true;
      while ($found == true) {
        $random_start = rand(0, (128-$length));
        $coupon_code = strtoupper(substr($rand_str, $random_start, $length));

        $Qcoupon = $osC_Database->query('select * from :table_coupons where coupon_code = :coupon_code');
        $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
        $Qcoupon->bindValue(':coupon_code', $coupon_code);
        $Qcoupon->execute();

        if ($Qcoupon->numberOfRows() == 0) {
          $found = false;
        }
      }

      return 'C' . $coupon_code;
    }
  }
?>
