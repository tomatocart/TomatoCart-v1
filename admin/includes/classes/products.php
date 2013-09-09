<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include('../includes/classes/products.php');

  class osC_Products_Admin extends osC_Products {

    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qproducts = $osC_Database->query('select p.*, pd.*, ptoc.*  from :table_products p left join  :table_products_description pd on p.products_id = pd.products_id left join :table_products_to_categories ptoc on ptoc.products_id = p.products_id  where p.products_id = :products_id and pd.language_id = :language_id');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qproducts->bindInt(':products_id', $id);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      $data = $Qproducts->toArray();

      $Qproducts->freeResult();

      return $data;
    }

    function getAttributes($attributes_groups_id, $products_id = null) {
      global $osC_Database, $osC_Language;

      $Qattributes = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id and status = 1 order by sort_order');
      $Qattributes->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qattributes->bindInt(':products_attributes_groups_id', $attributes_groups_id);
      $Qattributes->bindInt(':language_id', $osC_Language->getID());
      $Qattributes->execute();

      $attributes = array();
      while ($Qattributes->next()) {
        $attribute = $Qattributes->toArray();
        $attribute['choosed_value'] = '';

        if (is_numeric($products_id)) {
          $Qvalue = $osC_Database->query('select value from :table_products_attributes where products_id = :products_id and language_id = :language_id and products_attributes_values_id = :products_attributes_values_id ');
          $Qvalue->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qvalue->bindInt(':products_id', $products_id);
          $Qvalue->bindInt(':products_attributes_values_id', $attribute['products_attributes_values_id']);
          $Qvalue->bindInt(':language_id', $osC_Language->getID());
          $Qvalue->execute();

          if ($Qvalue->numberOfRows() > 0) {
            $attribute['choosed_value'] = $Qvalue->value('value');
          }
        }
        $attributes[] = $attribute;
      }

      for($i = 0; $i < sizeof($attributes); $i++) {
        if ($attributes[$i]['module'] == 'text_field') {
          $attributes[$i]['lang_values'] = array();
          foreach ($osC_Language->getAll() as $l) {
            $choosed_value = '';

            if (is_numeric($products_id)) {
              $Qvalue = $osC_Database->query('select value from :table_products_attributes where products_id = :products_id and language_id = :language_id and products_attributes_values_id =:products_attributes_values_id');
              $Qvalue->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
              $Qvalue->bindInt(':products_id', $products_id);
              $Qvalue->bindInt(':language_id', $l['id']);
              $Qvalue->bindInt(':products_attributes_values_id', $attributes[$i]['products_attributes_values_id']);
              $Qvalue->execute();

              if ($Qvalue->numberOfRows() > 0) {
                $choosed_value = $Qvalue->value('value');
              }
            }
              
            $attributes[$i]['lang_values'][$l['id']] = $choosed_value;
          }
        }
      }
      
      return $attributes;
    }


    function save($id = null, $data) {
      global $osC_Database, $osC_Language, $osC_Image, $osC_Session;

      $error = false;

      $osC_Database->startTransaction();

          //products
      if (is_numeric($id)) {
        $Qproduct = $osC_Database->query('update :table_products set products_type = :products_type, products_sku = :products_sku, products_model = :products_model, products_price = :products_price, products_quantity = :products_quantity, products_moq = :products_moq, products_max_order_quantity = :products_max_order_quantity, order_increment = :order_increment, quantity_unit_class = :quantity_unit_class, products_date_available = :products_date_available, products_weight = :products_weight, products_weight_class = :products_weight_class, products_status = :products_status, products_tax_class_id = :products_tax_class_id, manufacturers_id = :manufacturers_id, quantity_discount_groups_id = :quantity_discount_groups_id, products_last_modified = now(), products_attributes_groups_id = :products_attributes_groups_id where products_id = :products_id');
        $Qproduct->bindInt(':products_id', $id);
      } else {
        $Qproduct = $osC_Database->query('insert into :table_products (products_type, products_sku, products_model, products_price, products_quantity, products_moq, products_max_order_quantity, order_increment, quantity_unit_class, products_date_available, products_weight, products_weight_class, products_status, products_tax_class_id, manufacturers_id, products_date_added, quantity_discount_groups_id, products_attributes_groups_id) values (:products_type, :products_sku, :products_model, :products_price, :products_quantity, :products_moq, :products_max_order_quantity, :order_increment, :quantity_unit_class, :products_date_available, :products_weight, :products_weight_class, :products_status, :products_tax_class_id, :manufacturers_id, :products_date_added, :quantity_discount_groups_id, :products_attributes_groups_id)');
        $Qproduct->bindRaw(':products_date_added', 'now()');
      }

      $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproduct->bindInt(':products_type', $data['products_type']);
      $Qproduct->bindValue(':products_sku', $data['products_sku']);
      $Qproduct->bindValue(':products_model', $data['products_model']);
      $Qproduct->bindValue(':products_price', $data['price']);
      $Qproduct->bindInt(':products_quantity', $data['quantity']);
      $Qproduct->bindInt(':products_moq', $data['products_moq']);
      $Qproduct->bindInt(':products_max_order_quantity', $data['products_max_order_quantity']);
      $Qproduct->bindInt(':order_increment', $data['order_increment']);
      $Qproduct->bindInt(':quantity_unit_class', $data['quantity_unit_class']);

      if (date('Y-m-d') < $data['date_available']) {
        $Qproduct->bindValue(':products_date_available', $data['date_available']);
      } else {
        $Qproduct->bindRaw(':products_date_available', 'null');
      }
      
      $Qproduct->bindValue(':products_weight', $data['weight']);
      $Qproduct->bindInt(':products_weight_class', $data['weight_class']);
      $Qproduct->bindInt(':products_status', $data['status']);
      $Qproduct->bindInt(':products_tax_class_id', $data['tax_class_id']);
      $Qproduct->bindInt(':manufacturers_id', $data['manufacturers_id']);
      $Qproduct->bindInt(':quantity_discount_groups_id', $data['quantity_discount_groups_id']);
      
      if (empty($data['products_attributes_groups_id'])) {
        $Qproduct->bindRaw(':products_attributes_groups_id', 'null');
      } else {
        $Qproduct->bindInt(':products_attributes_groups_id', $data['products_attributes_groups_id']);
      }
      
      $Qproduct->setLogging($_SESSION['module'], $id);
      $Qproduct->execute();

      if ($osC_Database->isError()) {
        $error = true;
      } else {
        if (is_numeric($id)) {
          $products_id = $id;
        } else {
          $products_id = $osC_Database->nextID();
        }
        
//products_to_categories
        $Qcategories = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id');
        $Qcategories->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qcategories->bindInt(':products_id', $products_id);
        $Qcategories->setLogging($_SESSION['module'], $products_id);
        $Qcategories->execute();

        if ($osC_Database->isError()) {
          $error = true;
        } else {
          if ( isset($data['categories']) && !empty($data['categories']) ) {
            foreach ($data['categories'] as $category_id) {
              $Qp2c = $osC_Database->query('insert into :table_products_to_categories (products_id, categories_id) values (:products_id, :categories_id)');
              $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
              $Qp2c->bindInt(':products_id', $products_id);
              $Qp2c->bindInt(':categories_id', $category_id);
              $Qp2c->setLogging($_SESSION['module'], $products_id);
              $Qp2c->execute();

              if ( $osC_Database->isError() ) {
                $error = true;
                break;
              }
            }
          }        
        }
      }
      
      if ( $error === false && is_numeric($id) ) {
        $Qdelete = $osC_Database->query('delete from :table_products_attachments_to_products where products_id = :products_id');
        $Qdelete->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
        $Qdelete->bindInt(':products_id', $products_id);
        $Qdelete->setLogging($_SESSION['module'], $products_id);
        $Qdelete->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
        
      if ( $error === false && sizeof($data['attachments']) > 0 ) {
        foreach ($data['attachments'] as $attachments_id) {
          $Qp2a = $osC_Database->query('insert into :table_products_attachments_to_products (products_id, attachments_id) values (:products_id, :attachments_id)');
          $Qp2a->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
          $Qp2a->bindInt(':products_id', $products_id);
          $Qp2a->bindInt(':attachments_id', $attachments_id);
          $Qp2a->setLogging($_SESSION['module'], $products_id);
          $Qp2a->execute();
  
          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }      
        }
      }

      //accessories
      if ($error === false) {
        if (is_numeric($id)) {
          $Qdelete = $osC_Database->query('delete from :table_products_accessories where products_id = :products_id');
          $Qdelete->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
          $Qdelete->bindInt(':products_id', $products_id);
          $Qdelete->setLogging($_SESSION['module'], $products_id);
          $Qdelete->execute();
          
          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }

        if ( sizeof($data['accessories_ids']) > 0 ) {
          foreach ($data['accessories_ids'] as $accessories_id) {
            $Qinsert = $osC_Database->query('insert into :table_products_accessories (products_id, accessories_id) values (:products_id, :accessories_id)');
            $Qinsert->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
            $Qinsert->bindInt(':products_id', $products_id);
            $Qinsert->bindInt(':accessories_id', $accessories_id);
            $Qinsert->setLogging($_SESSION['module'], $products_id);
            $Qinsert->execute();
    
            if ( $osC_Database->isError() ) {
              $error = true;
              break;
            }      
          }
        }
      }
      
      //downloadable products & gift certificates
      if ($data['products_type'] == PRODUCT_TYPE_DOWNLOADABLE) {
        if (is_numeric($id)) {
          $Qdownloadables = $osC_Database->query('update :table_products_downloadables set number_of_downloads = :number_of_downloads, number_of_accessible_days = :number_of_accessible_days where products_id = :products_id');
        } else {
          $Qdownloadables = $osC_Database->query('insert into :table_products_downloadables (products_id, number_of_downloads, number_of_accessible_days) values (:products_id, :number_of_downloads, :number_of_accessible_days)');
        }
              
        $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
        $Qdownloadables->bindInt(':products_id', $products_id);
        $Qdownloadables->bindInt(':number_of_downloads', $data['number_of_downloads']);
        $Qdownloadables->bindInt(':number_of_accessible_days', $data['number_of_accessible_days']);
        $Qdownloadables->setLogging($_SESSION['module'], $products_id);
        $Qdownloadables->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        } else {
          $filename = null;
          $cache_filename = null;
          $file = new upload('downloadable_file');
          
          if ($file->exists()) {
            $file->set_destination(realpath('../download'));
  
            if ($file->parse() && $file->save()) {
              $filename = $file->filename;
              $cache_filename = md5($filename . time());
              rename(DIR_FS_DOWNLOAD . $filename, DIR_FS_DOWNLOAD . $cache_filename);
            }
          }
          
          if (!is_null($filename)) {
            if (is_numeric($id)) {
              $Qfile = $osC_Database->query('select cache_filename from :table_products_downloadables where products_id = :products_id');
              $Qfile->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qfile->bindInt(':products_id', $products_id);
              $Qfile->execute(); 
              
              if ($Qfile->numberOfRows() > 0) {
                $file = $Qfile->value('cache_filename');
                unlink(DIR_FS_DOWNLOAD . $file);
              }
            }
          
            $Qupdate = $osC_Database->query('update :table_products_downloadables set filename = :filename, cache_filename = :cache_filename where products_id = :products_id');
            $Qupdate->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
            $Qupdate->bindInt(':products_id', $products_id);
            $Qupdate->bindValue(':filename', $filename);
            $Qupdate->bindValue(':cache_filename', $cache_filename);
            $Qupdate->setLogging($_SESSION['module'], $products_id);
            $Qupdate->execute();   
          
            if ($osC_Database->isError()) {
              $error = true;
            } 
          }   
          
          if ($error === false) {
            $sample_filename = null;
            $cache_sample_filename = null;
            $sample_file = new upload('sample_downloadable_file');
            
            if ($sample_file->exists()) {
              $sample_file->set_destination(realpath('../download'));
    
              if ($sample_file->parse() && $sample_file->save()) {
                $sample_filename = $sample_file->filename;
                $cache_sample_filename = md5($sample_filename . time());
                @rename(DIR_FS_DOWNLOAD . $sample_filename, DIR_FS_DOWNLOAD . $cache_sample_filename);
              }
            }
            
            if (!is_null($sample_filename) && ($error === false)) {
              if (is_numeric($id)) {
                $Qfile = $osC_Database->query('select cache_sample_filename from :table_products_downloadables where products_id = :products_id');
                $Qfile->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
                $Qfile->bindInt(':products_id', $products_id);
                $Qfile->execute(); 
                
                if ($Qfile->numberOfRows() > 0) {
                  $file = $Qfile->value('cache_sample_filename');
                  unlink(DIR_FS_DOWNLOAD . $file);
                }
              }
            
              $Qfiles = $osC_Database->query('update :table_products_downloadables set sample_filename = :sample_filename, cache_sample_filename = :cache_sample_filename where products_id = :products_id');
              $Qfiles->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qfiles->bindInt(':products_id', $products_id);
              $Qfiles->bindValue(':sample_filename', $sample_filename);
              $Qfiles->bindValue(':cache_sample_filename', $cache_sample_filename);
              $Qfiles->setLogging($_SESSION['module'], $products_id);
              $Qfiles->execute();   
            
              if ($osC_Database->isError()) {
                $error = true;
              } 
            }               
          }
        }
      } else if ($data['products_type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
        if (is_numeric($id)) {
          $Qcertificates = $osC_Database->query('update :table_products_gift_certificates set gift_certificates_type = :gift_certificates_type, gift_certificates_amount_type = :gift_certificates_amount_type, open_amount_max_value = :open_amount_max_value, open_amount_min_value = :open_amount_min_value where products_id = :products_id');
        } else {
          $Qcertificates = $osC_Database->query('insert into :table_products_gift_certificates (products_id, gift_certificates_type, gift_certificates_amount_type, open_amount_max_value, open_amount_min_value) values (:products_id, :gift_certificates_type, :gift_certificates_amount_type, :open_amount_max_value, :open_amount_min_value)');
        }
                
        $Qcertificates->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
        $Qcertificates->bindInt(':products_id', $products_id);
        $Qcertificates->bindInt(':gift_certificates_type', $data['gift_certificates_type']);
        $Qcertificates->bindInt(':gift_certificates_amount_type', $data['gift_certificates_amount_type']);
        $Qcertificates->bindValue(':open_amount_max_value', $data['open_amount_max_value']);
        $Qcertificates->bindValue(':open_amount_min_value', $data['open_amount_min_value']);
        $Qcertificates->setLogging($_SESSION['module'], $products_id);
        $Qcertificates->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      }      

      //products_description
      if ($error === false) {
        foreach ($osC_Language->getAll() as $l) {
          if (is_numeric($id)) {
            $Qpd = $osC_Database->query('update :table_products_description set products_name = :products_name, products_short_description = :products_short_description, products_description = :products_description, products_tags = :products_tags, products_url = :products_url, products_friendly_url = :products_friendly_url, products_page_title = :products_page_title, products_meta_keywords = :products_meta_keywords, products_meta_description = :products_meta_description where products_id = :products_id and language_id = :language_id');
          } else {
            $Qpd = $osC_Database->query('insert into :table_products_description (products_id, language_id, products_name, products_short_description, products_description, products_tags, products_url, products_friendly_url, products_page_title, products_meta_keywords, products_meta_description) values (:products_id, :language_id, :products_name, :products_short_description, :products_description, :products_tags, :products_url, :products_friendly_url, :products_page_title, :products_meta_keywords, :products_meta_description)');
          }

          $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qpd->bindInt(':products_id', $products_id);
          $Qpd->bindInt(':language_id', $l['id']);
          $Qpd->bindValue(':products_name', $data['products_name'][$l['id']]);
          $Qpd->bindValue(':products_short_description', $data['products_short_description'][$l['id']]);
          $Qpd->bindValue(':products_description', $data['products_description'][$l['id']]);
          $Qpd->bindValue(':products_tags', $data['products_tags'][$l['id']]);
          $Qpd->bindValue(':products_url', $data['products_url'][$l['id']]);
          $Qpd->bindValue(':products_friendly_url', $data['products_friendly_url'][$l['id']]);
          $Qpd->bindValue(':products_page_title', $data['products_page_title'][$l['id']]);
          $Qpd->bindValue(':products_meta_keywords', $data['products_meta_keywords'][$l['id']]);
          $Qpd->bindValue(':products_meta_description', $data['products_meta_description'][$l['id']]);
          $Qpd->setLogging($_SESSION['module'], $products_id);
          $Qpd->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }

       //BEGIN: products images
      if ($error === false) {
        $images = array();
        $image_path = '../images/products/_upload/' . $osC_Session->getID() . '/';
        
        $osC_DirectoryListing = new osC_DirectoryListing($image_path, true);
        $osC_DirectoryListing->setIncludeDirectories(false);
        foreach (($osC_DirectoryListing->getFiles()) as $file) {
          @copy($image_path . $file['name'], '../images/products/originals/' . $file['name']);
          @unlink($image_path . $file['name']);
          
          $images[$file['name']] = -1;
        }
        osc_remove($image_path);        

        $default_flag = 1;

        foreach (array_keys($images) as $image) {
          $Qimage = $osC_Database->query('insert into :table_products_images (products_id, default_flag, sort_order, date_added) values (:products_id, :default_flag, :sort_order, :date_added)');
          $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qimage->bindInt(':products_id', $products_id);
          $Qimage->bindInt(':default_flag', $default_flag);
          $Qimage->bindInt(':sort_order', 0);
          $Qimage->bindRaw(':date_added', 'now()');
          $Qimage->execute();

          if ($osC_Database->isError()) {
            $error = true;
          } else {
            $image_id = $osC_Database->nextID();
            $images[$image] = $image_id;
            
            $new_image_name =  $products_id . '_' . $image_id . '_' . $image;
            @rename('../images/products/originals/' . $image, '../images/products/originals/' . $new_image_name);
              
            $Qupdate = $osC_Database->query('update :table_products_images set image = :image where id = :id');
            $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qupdate->bindValue(':image', $new_image_name);
            $Qupdate->bindInt(':id', $image_id);
            $Qupdate->setLogging($_SESSION['module'], $products_id);
            $Qupdate->execute();  
          
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($new_image_name, $group['id'], 'products');
              }
            }
          }

          $default_flag = 0;
        }
      }
      //END: products images
      
      //BEGIN: products variants
      if ( $error === false ) {
        //if edit product, delete variant first
        if (is_numeric($id)) {
          $Qvariants = $osC_Database->query('select * from :table_products_variants where products_id = :products_id order by products_variants_id');
          $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qvariants->bindInt(':products_id', $_REQUEST['products_id']);
          $Qvariants->execute();
          
          $records = array();
          while ($Qvariants->next()) {
            $Qentries = $osC_Database->query('select products_variants_id, products_variants_groups_id, products_variants_values_id from :table_products_variants_entries where products_variants_id = :products_variants_id order by products_variants_groups_id, products_variants_values_id');
            $Qentries->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
            $Qentries->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
            $Qentries->execute();
            
            $variants_values = array();            
            while ($Qentries->next()) {
              $variants_values[] = $Qentries->valueInt('products_variants_groups_id') . '_' . $Qentries->valueInt('products_variants_values_id');
            }
            
            $variant = implode('-', $variants_values);
            
            if (!isset($data['products_variants_id'][$variant]) ) {
              //remove cache file
              $cache_filename = $Qvariants->value('cache_filename');
              if (!empty($cache_filename) && file_exists(DIR_FS_DOWNLOAD . $cache_filename)) {
                osc_remove(DIR_FS_DOWNLOAD . $cache_filename);
              }
                  
              //delete variants
              $Qdelete = $osC_Database->query('delete from :table_products_variants where products_variants_id = :products_variants_id');
              $Qdelete->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
              $Qdelete->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
              $Qdelete->execute();
              
              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
              
              //delete variants entries
              if ($error === false) {
                $Qdelete = $osC_Database->query('delete from :table_products_variants_entries where products_variants_id = :products_variants_id');
                $Qdelete->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
                $Qdelete->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
                $Qdelete->execute();
                
                if ($osC_Database->isError()) {
                  $error = true;
                  break;
                }
              }
            }
          }
        }
        
        $products_quantity = 0;
        
        //insert or update variant
        if (isset($data['products_variants_id']) && is_array($data['products_variants_id'])) {
          foreach ($data['products_variants_id'] as $key => $variants_id) {
            if ($variants_id > 0) {
              $Qpv = $osC_Database->query('update :table_products_variants set products_price = :products_price, products_sku = :products_sku, products_model = :products_model, products_quantity = :products_quantity, products_weight = :products_weight, products_status = :products_status, products_images_id = :products_images_id, is_default = :is_default  where products_variants_id = :products_variants_id');
              $Qpv->bindInt(':products_variants_id', $variants_id);
            } else {
              $Qpv = $osC_Database->query('insert into :table_products_variants (products_id, products_price, products_sku, products_model, products_quantity, products_weight, products_status, is_default, products_images_id) values (:products_id, :products_price, :products_sku, :products_model, :products_quantity, :products_weight, :products_status, :is_default, :products_images_id)');
              $Qpv->bindInt(':products_id', $products_id);
            }
            
            $Qpv->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $Qpv->bindInt(':is_default', $data['variants_default'][$key]);
            $Qpv->bindValue(':products_price', $data['variants_price'][$key]);
            $Qpv->bindValue(':products_sku', $data['variants_sku'][$key]);
            $Qpv->bindValue(':products_model', $data['variants_model'][$key]);
            $Qpv->bindValue(':products_quantity', $data['variants_quantity'][$key]);
            $Qpv->bindValue(':products_weight', $data['variants_weight'][$key]);
            $Qpv->bindValue(':products_status', $data['variants_status'][$key]);
            
            $products_images_id = is_numeric($data['variants_image'][$key]) ? $data['variants_image'][$key] : $images[$data['variants_image'][$key]];
            $Qpv->bindInt(':products_images_id', $products_images_id);
            
            $Qpv->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
              break;
            } else {
              if ( is_numeric($variants_id) && ($variants_id > 0) ) {
                $products_variants_id = $variants_id;
              } else {
                $products_variants_id = $osC_Database->nextID();
              }
                    
              //downloadable file
              if ($data['products_type'] == PRODUCT_TYPE_DOWNLOADABLE) {
                $variants_file = new upload('products_variants_download_' . $key);
                
                if ($variants_file->exists()) {
                  //remove old file
                  if ( is_numeric($variants_id) && ($variants_id > 0) ) {
                    $Qfile = $osC_Database->query('select cache_filename from :table_products_variants where products_variants_id = :products_variants_id');
                    $Qfile->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
                    $Qfile->bindInt(':products_variants_id', $variants_id);
                    $Qfile->execute();
                    
                    $cache_filename = $Qfile->value('cache_filename');
                    if (!empty($cache_filename)) {
                      osc_remove(DIR_FS_DOWNLOAD . $cache_filename);
                    }
                  }
  
                  $variants_file->set_destination(realpath('../download'));
                  if ($variants_file->parse() && $variants_file->save()) {
                    $variants_filename = $variants_file->filename;
                    $cache_variants_filename = md5($variants_filename . time());
                    
                    @rename(DIR_FS_DOWNLOAD . $variants_filename, DIR_FS_DOWNLOAD . $cache_variants_filename);
                    
                    $Qupdate = $osC_Database->query('update :table_products_variants set filename = :filename, cache_filename = :cache_filename where products_variants_id = :products_variants_id');
                    $Qupdate->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
                    $Qupdate->bindInt(':products_variants_id', $products_variants_id);
                    $Qupdate->bindValue(':filename', $variants_filename);
                    $Qupdate->bindValue(':cache_filename', $cache_variants_filename);
                    $Qupdate->execute();  
                    
                    if ($osC_Database->isError()) {
                      $error = true;
                      break;
                    }
                  }
                }
              }
              
              $products_quantity += $data['variants_quantity'][$key];
            }
            
            //variant entries
            if ( ($error === false) && ($variants_id == '-1') ) {
              $assigned_variants = explode('-', $key);
    
              for($i = 0; $i < sizeof($assigned_variants); $i++) {
                $assigned_variant = explode('_', $assigned_variants[$i]);
    
                $Qpve = $osC_Database->query('insert into :table_products_variants_entries (products_variants_id, products_variants_groups_id, products_variants_values_id) values (:products_variants_id, :products_variants_groups_id, :products_variants_values_id)');
                $Qpve->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
                $Qpve->bindInt(':products_variants_id', $products_variants_id);
                $Qpve->bindInt(':products_variants_groups_id', $assigned_variant[0]);
                $Qpve->bindInt(':products_variants_values_id', $assigned_variant[1]);
                $Qpve->setLogging($_SESSION['module'], $products_id);
                $Qpve->execute();
      
                if ($osC_Database->isError()) {
                  $error = true;
                  break;
                }
              }
            }
          }
          
          if ( $error === false ) {
            $osC_Database->simpleQuery('update ' . TABLE_PRODUCTS . ' set products_quantity = ' . $products_quantity . ' where products_id =' . $products_id);
    
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
      }
      //END: products variants
      
      //BEGIN: xsell products
      if ($error === false) {
        if (is_numeric($id)) {
          $Qdelete = $osC_Database->query('delete from :table_products_xsell where products_id = :products_id');
          $Qdelete->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
          $Qdelete->bindInt(':products_id', $id);
          $Qdelete->setLogging($_SESSION['module'], $id);
          $Qdelete->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          if ( isset($data['xsell_id_array']) && !empty($data['xsell_id_array']) ) {
            foreach ($data['xsell_id_array'] as $xsell_products_id) {
              $Qxsell = $osC_Database->query('insert into :table_products_xsell (products_id, xsell_products_id) values (:products_id , :xsell_products_id )');
              $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
              $Qxsell->bindInt(':products_id', $products_id);
              $Qxsell->bindInt(':xsell_products_id', $xsell_products_id);
              $Qxsell->setLogging($_SESSION['module'], $products_id);
              $Qxsell->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }
      //END: xsell products

      //BEGIN: products attributes
      if ($error === false) {
        if (is_numeric($id)) {
          $Qdelete = $osC_Database->query('delete from :table_products_attributes where products_id = :products_id ');
          $Qdelete->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qdelete->bindInt(':products_id', $id);
          $Qdelete->setLogging($_SESSION['module'], $id);
          $Qdelete->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          if (!empty($data['products_attributes'])) {
            foreach ($data['products_attributes'] as $attribute) {
              $Qef = $osC_Database->query('insert into :table_products_attributes (products_id, products_attributes_values_id, language_id, value) values (:products_id , :products_attributes_values_id, :language_id, :value)');
              $Qef->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
              $Qef->bindInt(':products_id', $products_id);
              $Qef->bindInt(':products_attributes_values_id', $attribute['id']);
              $Qef->bindInt(':language_id', $attribute['language_id']);
              $Qef->bindValue(':value', $attribute['value']);
              $Qef->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }
      //END: products attributes
      
      //BEGIN: customization fields
      if ($error === false) {
        if ( is_numeric($id) && isset($data['customization_fields']) ) {
          $ids = array();
          foreach ($data['customization_fields'] as $customization) {
            if ($customization['customizations_fields_id'] > 0) {
              $ids[] = $customization['customizations_fields_id'];
            }
          }
          
          $Qcheck = $osC_Database->query('select customization_fields_id from :table_customization_fields where products_id = :products_id');
          $Qcheck->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
          $Qcheck->bindInt(':products_id', $products_id);
          
          if ( sizeof($ids) > 0 ) {
            $Qcheck->appendQuery('and customization_fields_id not in (:customization_fields_id)');
            $Qcheck->bindRaw(':customization_fields_id', implode(', ', $ids));
          }
          
          $Qcheck->execute();
          
          //delete customization fields
          if ($Qcheck->numberOfRows() > 0) {
            $batch = array();
            
            while($Qcheck->next()) {
              $batch[] = $Qcheck->valueInt('customization_fields_id');
            }
            
            $Qdelete = $osC_Database->query('delete from :table_customization_fields where customization_fields_id in (:customization_fields_id)');
            $Qdelete->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
            $Qdelete->bindRaw(':customization_fields_id', implode(', ', $batch));
            $Qdelete->setLogging($_SESSION['module'], $products_id);
            $Qdelete->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
              break;
            }
            
            if ($error === false) {
              $Qdelete = $osC_Database->query('delete from :table_customization_fields_description where customization_fields_id in (:customization_fields_id)');
              $Qdelete->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
              $Qdelete->bindRaw(':customization_fields_id', implode(', ', $batch));
              $Qdelete->setLogging($_SESSION['module'], $products_id);
              $Qdelete->execute();
  
              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }
      
      if ($error === false) {
        if (isset($data['customization_fields']) && !empty($data['customization_fields']) ) {
          foreach ( $data['customization_fields'] as $field ) {
            if ($field['customizations_fields_id'] > 0) {
              $Qfield = $osC_Database->query('update :table_customization_fields set type = :type, is_required = :is_required where customization_fields_id = :customization_fields_id');
              $Qfield->bindInt(':customization_fields_id', $field['customizations_fields_id']);
            } else {
              $Qfield = $osC_Database->query('insert into :table_customization_fields (products_id, type, is_required) values (:products_id, :type, :is_required)');
            }
            $Qfield->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
            $Qfield->bindInt(':products_id', $products_id);
            $Qfield->bindInt(':type', $field['customizations_type']);
            $Qfield->bindInt(':is_required', $field['customizations_is_required']);
            $Qfield->execute();
              
            if ($osC_Database->isError()) {
              $error = true;
              break;
            } else {
              $fields_id = ($field['customizations_fields_id'] > 0) ? $field['customizations_fields_id'] : $osC_Database->nextID();
              
              $lan = get_object_vars($field['customizations_name_data']);
              foreach ($osC_Language->getAll() as $l) {
                if ($field['customizations_fields_id'] > 0) {
                  $Qdescription = $osC_Database->query('update :table_customization_fields_description set name = :name where customization_fields_id = :customization_fields_id and languages_id = :languages_id');
                  
                } else {
                  $Qdescription = $osC_Database->query('insert into :table_customization_fields_description (customization_fields_id, languages_id, name) values (:customization_fields_id, :languages_id, :name)');
                }
                
                $Qdescription->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
                $Qdescription->bindInt(':customization_fields_id', $fields_id);
                $Qdescription->bindInt(':languages_id', $l['id']);
                $Qdescription->bindValue(':name', $lan['name' . $l['id']]);
                $Qdescription->setLogging($_SESSION['module'], $products_id);
                $Qdescription->execute();
                
                if ($osC_Database->isError()) {
                  $error = true;
                  break;
                }
              }
            }
          }
        }
      }
      //END: customization fields
      
      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        
        if (is_numeric($id)) {
          osC_Cache::clear('product-' . $id);
        }
        
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');
        osC_Cache::clear('feature-products');
        osC_Cache::clear('upcoming_products');

        return $products_id;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function move($old_categories_id, $target_categories_id, $id) {
      global $osC_Database;
      
      if ($old_categories_id > 0) {
        $Qdelete = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id and categories_id = :categories_id');
        $Qdelete->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qdelete->bindInt(':products_id', $id);
        $Qdelete->bindInt(':categories_id', $old_categories_id);
        $Qdelete->execute();
        
        if ($osC_Database->isError()) {
          return false;
        }
      }
      
      $Qcheck = $osC_Database->query('select * from :table_products_to_categories where products_id = :products_id and categories_id = :categories_id');
      $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qcheck->bindInt(':products_id', $id);
      $Qcheck->bindInt(':categories_id', $target_categories_id);
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() < 1) {
        $Qinsert = $osC_Database->query('insert into :table_products_to_categories (products_id, categories_id) values (:products_id, :categories_id)');
        $Qinsert->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qinsert->bindInt(':products_id', $id);
        $Qinsert->bindInt(':categories_id', $target_categories_id);
        $Qinsert->execute();
        
        if ($osC_Database->isError()) {
          return false;
        }
      }
      
      osC_Cache::clear('product-' . $id);
      
      return true;
    }
    
    function delete($id, $categories = null) {
      global $osC_Database, $osC_Image;

      $delete_product = true;
      $error = false;

      $osC_Database->startTransaction();

      if (is_array($categories) && !empty($categories)) {
        $Qpc = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id and categories_id in :categories_id');
        $Qpc->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qpc->bindInt(':products_id', $id);
        $Qpc->bindRaw(':categories_id', '("' . implode('", "', $categories) . '")');
        $Qpc->setLogging($_SESSION['module'], $id);
        $Qpc->execute();

        if (!$osC_Database->isError()) {
          $Qcheck = $osC_Database->query('select products_id from :table_products_to_categories where products_id = :products_id limit 1');
          $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qcheck->bindInt(':products_id', $id);
          $Qcheck->execute();

          if ($Qcheck->numberOfRows() > 0) {
            $delete_product = false;
          }
        } else {
          $error = true;
        }
      }

      if (($error === false) && ($delete_product === true)) {
        //reviews
        $Qr = $osC_Database->query('delete from :table_reviews where products_id = :products_id');
        $Qr->bindTable(':table_reviews', TABLE_REVIEWS);
        $Qr->bindInt(':products_id', $id);
        $Qr->setLogging($_SESSION['module'], $id);
        $Qr->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }

        //customers basket
        if ($error === false) {
          $Qcb = $osC_Database->query('delete from :table_customers_basket where products_id = :products_id or products_id like :products_id');
          $Qcb->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
          $Qcb->bindInt(':products_id', $id);
          $Qcb->bindValue(':products_id', (int)$id . '#%');
          $Qcb->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //categories
        if ($error === false) {
          $Qp2c = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id');
          $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qp2c->bindInt(':products_id', $id);
          $Qp2c->setLogging($_SESSION['module'], $id);
          $Qp2c->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
      
        //attachments
        if ($error === false) {
          $Qp2a = $osC_Database->query('delete from :table_products_attachments_to_products where products_id = :products_id');
          $Qp2a->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
          $Qp2a->bindInt(':products_id', $id);
          $Qp2a->setLogging($_SESSION['module'], $id);
          $Qp2a->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //specials
        if ($error === false) {
          $Qs = $osC_Database->query('delete from :table_specials where products_id = :products_id');
          $Qs->bindTable(':table_specials', TABLE_SPECIALS);
          $Qs->bindInt(':products_id', $id);
          $Qs->setLogging($_SESSION['module'], $id);
          $Qs->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //xsell
        if ($error === false) {
          $Qxsell = $osC_Database->query('delete from :table_products_xsell where products_id = :products_id');
          $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
          $Qxsell->bindInt(':products_id', $id);
          $Qxsell->setLogging($_SESSION['module'], $id);
          $Qxsell->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //attributes
        if ($error === false) {
          $Qattributes = $osC_Database->query('delete from :table_products_attributes where products_id = :products_id ');
          $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qattributes->bindInt(':products_id', $id);
          $Qattributes->setLogging($_SESSION['module'], $id);
          $Qattributes->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        //variants entries
        if ( $error === false ) {
          $Qdpve = $osC_Database->query('delete from :table_products_variants_entries where products_variants_id in ( select products_variants_id from :table_products_variants where products_id = :products_id )');
          $Qdpve->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
          $Qdpve->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qdpve->bindInt(':products_id', $id);
          $Qdpve->setLogging($_SESSION['module'], $id);
          $Qdpve->execute();
          
          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
        
        //variants
        if ($error === false) {
          $Qfiles = $osC_Database->query('select cache_filename from :table_products_variants where products_id = :products_id and cache_filename is not null');
          $Qfiles->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qfiles->bindInt(':products_id', $id);
          $Qfiles->execute();

          while ($Qfiles->next()) {
            @unlink(DIR_FS_DOWNLOAD . $Qfiles->value('cache_filename'));
          }
        
          $Qvariants = $osC_Database->query('delete from :table_products_variants where products_id = :products_id ');
          $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qvariants->bindInt(':products_id', $id);
          $Qvariants->setLogging($_SESSION['module'], $id);
          $Qvariants->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        //products description
        if ($error === false) {
          $Qpd = $osC_Database->query('delete from :table_products_description where products_id = :products_id');
          $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qpd->bindInt(':products_id', $id);
          $Qpd->setLogging($_SESSION['module'], $id);
          $Qpd->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //downloadables
        if ($error === false) {
          $Qfiles = $osC_Database->query('select cache_filename, cache_sample_filename from :table_products_downloadables where products_id = :products_id');
          $Qfiles->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
          $Qfiles->bindInt(':products_id', $id);
          $Qfiles->execute(); 
          
          if ($Qfiles->numberOfRows() > 0) {
            $cache_filename = $Qfiles->value('cache_filename');
            $cache_sample_filename = $Qfiles->value('cache_sample_filename');
            
            @unlink(DIR_FS_DOWNLOAD . $cache_filename);
            @unlink(DIR_FS_DOWNLOAD . $cache_sample_filename);
                    
            $Qdownloadables = $osC_Database->query('delete from :table_products_downloadables where products_id = :products_id');
            $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
            $Qdownloadables->bindInt(':products_id', $id);
            $Qdownloadables->setLogging($_SESSION['module'], $id);
            $Qdownloadables->execute();
  
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
        
        //gift certificates
        if ($error === false) {
          $Qgc = $osC_Database->query('delete from :table_products_gift_certificates where products_id = :products_id');
          $Qgc->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
          $Qgc->bindInt(':products_id', $id);
          $Qgc->setLogging($_SESSION['module'], $id);
          $Qgc->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        //product accessories
        if ($error == false) {
          $Qaccessories = $osC_Database->query('delete from :table_products_accessories where products_id = :products_id');
          $Qaccessories->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
          $Qaccessories->bindInt(':products_id', $id);
          $Qaccessories->setLogging($_SESSION['module'], $id);
          $Qaccessories->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }
        }        
          
        //customization_fields
        if ($error === false) {
          $Qcfd = $osC_Database->query('delete from :table_customization_fields_description where customization_fields_id in (select customization_fields_id from :table_customization_fields where products_id = :products_id)');
          $Qcfd->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
          $Qcfd->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
          $Qcfd->bindInt(':products_id', $id);
          $Qcfd->setLogging($_SESSION['module'], $id);
          $Qcfd->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }
          
          if ($error === false) {
            $Qcf = $osC_Database->query('delete from :table_customization_fields where products_id = :products_id');
            $Qcf->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
            $Qcf->bindRaw(':products_id', $id);
            $Qcf->setLogging($_SESSION['module'], $id);
            $Qcf->execute();

            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
      
        //products
        if ($error === false) {
          $Qp = $osC_Database->query('delete from :table_products where products_id = :products_id');
          $Qp->bindTable(':table_products', TABLE_PRODUCTS);
          $Qp->bindInt(':products_id', $id);
          $Qp->setLogging($_SESSION['module'], $id);
          $Qp->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        //images
        if ($error === false) {
          $Qim = $osC_Database->query('select id from :table_products_images where products_id = :products_id');
          $Qim->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qim->bindInt(':products_id', $id);
          $Qim->setLogging($_SESSION['module'], $id);
          $Qim->execute();

          while ($Qim->next()) {
            $osC_Image->delete($Qim->valueInt('id'));
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('product-' . $id);
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function copy($id, $data) {
      global $osC_Database, $osC_Language, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

      //product
      $Qproduct = $osC_Database->query('select * from :table_products where products_id = :products_id');
      $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproduct->bindInt(':products_id', $id);
      $Qproduct->execute();
      
      $Qinsert = $osC_Database->query('insert into :table_products (products_type, products_sku, products_model, products_price, products_quantity, products_moq, products_max_order_quantity, order_increment, quantity_unit_class, products_date_available, products_weight, products_weight_class, products_status, products_tax_class_id, manufacturers_id, products_date_added, quantity_discount_groups_id, products_attributes_groups_id) values (:products_type, :products_sku, :products_model, :products_price, :products_quantity, :products_moq, :products_max_order_quantity, :order_increment, :quantity_unit_class, :products_date_available, :products_weight, :products_weight_class, :products_status, :products_tax_class_id, :manufacturers_id, :products_date_added, :quantity_discount_groups_id, :products_attributes_groups_id)');
      $Qinsert->bindTable(':table_products', TABLE_PRODUCTS);
      $Qinsert->bindInt(':products_type', $Qproduct->valueInt('products_type'));
      $Qinsert->bindValue(':products_sku', $Qproduct->value('products_sku'));
      $Qinsert->bindValue(':products_model', $Qproduct->value('products_model'));
      $Qinsert->bindValue(':products_price', $Qproduct->valueDecimal('products_price'));
      $Qinsert->bindInt(':products_quantity', $Qproduct->valueInt('products_quantity'));
      $Qinsert->bindInt(':products_moq', $Qproduct->value('products_moq'));
      $Qinsert->bindRaw(':products_date_added', 'now()');
      $Qinsert->bindInt(':products_max_order_quantity', $Qproduct->valueInt('products_max_order_quantity'));
      $Qinsert->bindInt(':order_increment', $Qproduct->valueInt('order_increment'));
      $Qinsert->bindInt(':quantity_unit_class', $Qproduct->value('quantity_unit_class'));

      if (date('Y-m-d') < $Qproduct->value('products_date_available')) {
        $Qinsert->bindValue(':products_date_available', $Qproduct->value('products_date_available'));
      } else {
        $Qinsert->bindRaw(':products_date_available', 'null');
      }
      
      $Qinsert->bindValue(':products_weight', $Qproduct->valueDecimal('products_weight'));
      $Qinsert->bindInt(':products_weight_class', $Qproduct->valueInt('products_weight_class'));
      $Qinsert->bindInt(':products_status', $Qproduct->valueInt('products_status'));
      $Qinsert->bindInt(':products_tax_class_id', $Qproduct->valueInt('products_tax_class_id'));
      $Qinsert->bindInt(':manufacturers_id', $Qproduct->valueInt('manufacturers_id'));
      $Qinsert->bindInt(':quantity_discount_groups_id', $Qproduct->valueInt('quantity_discount_groups_id'));
      $Qinsert->bindInt(':products_attributes_groups_id', $Qproduct->valueInt('products_attributes_groups_id'));
      $Qinsert->execute();

      if ($osC_Database->isError()) {
        $error = true;
      } else {
        $products_id = $osC_Database->nextID();
      }

      //products_to_categories
      if ($error === false) {
        $Qcategories = $osC_Database->query('select * from :table_products_to_categories where products_id = :products_id');
        $Qcategories->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qcategories->bindInt(':products_id', $id);
        $Qcategories->execute();

        while( $Qcategories->next() ) {
          $Qp2c = $osC_Database->query('insert into :table_products_to_categories (products_id, categories_id) values (:products_id, :categories_id)');
          $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qp2c->bindInt(':products_id', $products_id);
          $Qp2c->bindInt(':categories_id', $Qcategories->valueInt('categories_id'));
          $Qp2c->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }
      
      //products_description
      if ($error === false) {
        $Qpd = $osC_Database->query('select * from :table_products_description where products_id = :products_id');
        $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qpd->bindInt(':products_id', $id);
        $Qpd->execute();
        
        while ( $Qpd->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_products_description (products_id, language_id, products_name, products_short_description, products_description, products_tags, products_url, products_friendly_url, products_page_title, products_meta_keywords, products_meta_description) values (:products_id, :language_id, :products_name, :products_short_description, :products_description, :products_tags, :products_url, :products_friendly_url, :products_page_title, :products_meta_keywords, :products_meta_description)');
          $Qinsert->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':language_id', $Qpd->valueInt('language_id'));
          $Qinsert->bindValue(':products_name', $Qpd->value('products_name'));
          $Qinsert->bindValue(':products_short_description', $Qpd->value('products_short_description'));
          $Qinsert->bindValue(':products_description', $Qpd->value('products_description'));
          $Qinsert->bindValue(':products_tags', $Qpd->value('products_tags'));
          $Qinsert->bindValue(':products_url', $Qpd->value('products_url'));
          $Qinsert->bindValue(':products_friendly_url', $Qpd->value('products_friendly_url'));
          $Qinsert->bindValue(':products_page_title', $Qpd->value('products_page_title'));
          $Qinsert->bindValue(':products_meta_keywords', $Qpd->value('products_meta_keywords'));
          $Qinsert->bindValue(':products_meta_description', $Qpd->value('products_meta_description'));
          $Qinsert->execute();
  
          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }  
      }
      
      //downloadable products & gift certificates
      if ($error === false) {
        if ($Qproduct->valueInt('products_type') == PRODUCT_TYPE_DOWNLOADABLE) {
          $Qdownloadable = $osC_Database->query('select * from :table_products_downloadables where products_id = :products_id');
          $Qdownloadable->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
          $Qdownloadable->bindInt(':products_id', $id);
          $Qdownloadable->execute();
          
          if ($Qdownloadable->numberOfRows() > 0) {
            $filename = $Qdownloadable->value('filename');
            $cache_filename = $Qdownloadable->value('cache_filename');
            $sample_filename = $Qdownloadable->value('sample_filename');
            $cache_sample_filename = $Qdownloadable->value('cache_sample_filename');
            
            $new_cache_file = md5($cache_filename . time());
            $new_cache_sample_file = empty($cache_sample_filename) ? '' :  md5($cache_sample_filename . time());
            
            if ( file_exists(DIR_FS_DOWNLOAD . $cache_filename) ) {
              @copy(DIR_FS_DOWNLOAD . $cache_filename, DIR_FS_DOWNLOAD . $new_cache_file);
            }
            
            if ( file_exists(DIR_FS_DOWNLOAD . $cache_sample_filename) ) {
              @copy(DIR_FS_DOWNLOAD . $cache_sample_filename, DIR_FS_DOWNLOAD . $new_cache_sample_file);
            }
            
            $Qinsert = $osC_Database->query('insert into :table_products_downloadables (products_id, filename, cache_filename, sample_filename, cache_sample_filename, number_of_downloads, number_of_accessible_days) values (:products_id, :filename, :cache_filename, :sample_filename, :cache_sample_filename, :number_of_downloads, :number_of_accessible_days)');
            $Qinsert->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
            $Qinsert->bindInt(':products_id', $products_id);
            $Qinsert->bindValue(':filename', $Qdownloadable->value('filename'));
            $Qinsert->bindValue(':cache_filename', basename($new_cache_file));
            $Qinsert->bindValue(':sample_filename', $Qdownloadable->value('sample_filename'));
            $Qinsert->bindValue(':cache_sample_filename', basename($new_cache_sample_file));
            $Qinsert->bindInt(':number_of_downloads', $Qdownloadable->valueInt('number_of_downloads'));
            $Qinsert->bindInt(':number_of_accessible_days', $Qdownloadable->valueInt('number_of_accessible_days'));
            $Qinsert->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
        //gift certificate
        else if ($Qproduct->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select * from :table_products_gift_certificates where products_id = :products_id');
          $Qcertificate->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':products_id', $id);
          $Qcertificate->execute();
          
          if ($Qcertificate->numberOfRows() > 0) {
            $Qinsert = $osC_Database->query('insert into :table_products_gift_certificates (products_id, gift_certificates_type, gift_certificates_amount_type, open_amount_max_value, open_amount_min_value) values (:products_id, :gift_certificates_type, :gift_certificates_amount_type, :open_amount_max_value, :open_amount_min_value)');
            $Qinsert->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
            $Qinsert->bindInt(':products_id', $products_id);
            $Qinsert->bindInt(':gift_certificates_type', $Qcertificate->valueInt('gift_certificates_type'));
            $Qinsert->bindInt(':gift_certificates_amount_type', $Qcertificate->valueInt('gift_certificates_amount_type'));
            $Qinsert->bindValue(':open_amount_max_value', $Qcertificate->valueDecimal('open_amount_max_value'));
            $Qinsert->bindValue(':open_amount_min_value', $Qcertificate->valueDecimal('open_amount_min_value'));
            $Qinsert->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }      
      }      
          
      //products_variants
      if ( $error == false && $data['copy_variants'] == 1 ) {
        $Qvariants = $osC_Database->query('select * from :table_products_variants where products_id = :products_id');
        $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
        $Qvariants->bindInt(':products_id', $id);
        $Qvariants->execute();
        
        while ( $Qvariants->next() ) {
          $filename = $Qvariants->value('filename');
          $cache_filename = $Qvariants->value('cache_filename');
          
          if ( !empty($cache_filename) ) {
            $cache_filename = md5($cache_filename . time());
            
            if (file_exists(DIR_FS_DOWNLOAD . $Qvariants->value('cache_filename'))) {
              @copy(DIR_FS_DOWNLOAD . $Qvariants->value('cache_filename'), DIR_FS_DOWNLOAD . $cache_filename);
            }
          }
        
          $Qinsert = $osC_Database->query('insert into :table_products_variants (products_id, is_default, products_images_id, products_price, products_sku, products_model, products_quantity, products_weight, products_status, filename, cache_filename) values (:products_id, :is_default, :products_images_id, :products_price, :products_sku, :products_model, :products_quantity, :products_weight, :products_status, :filename, :cache_filename)');
          $Qinsert->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':is_default', $Qvariants->valueInt('is_default'));
          $Qinsert->bindInt(':products_images_id', $Qvariants->valueInt('products_images_id'));
          $Qinsert->bindValue(':products_price', $Qvariants->valueDecimal('products_price'));
          $Qinsert->bindValue(':products_sku', $Qvariants->value('products_sku'));
          $Qinsert->bindValue(':products_model', $Qvariants->value('products_model'));
          $Qinsert->bindValue(':products_quantity', $Qvariants->valueInt('products_quantity'));
          $Qinsert->bindValue(':products_weight', $Qvariants->value('products_weight'));
          $Qinsert->bindInt(':products_status', $Qvariants->valueInt('products_status'));
          $Qinsert->bindValue(':filename', $filename);
          $Qinsert->bindValue(':cache_filename', $cache_filename);
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          } else {
            $products_variants_id = $osC_Database->nextID();
            
            $Qentries = $osC_Database->query('select * from :table_products_variants_entries where products_variants_id = :products_variants_id');
            $Qentries->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
            $Qentries->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
            $Qentries->execute();
            
            while ( $Qentries->next() ) {
              $Qinsert = $osC_Database->query('insert into :table_products_variants_entries (products_variants_id, products_variants_groups_id, products_variants_values_id) values (:products_variants_id, :products_variants_groups_id, :products_variants_values_id)');
              $Qinsert->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
              $Qinsert->bindInt(':products_variants_id', $products_variants_id);
              $Qinsert->bindInt(':products_variants_groups_id', $Qentries->valueInt('products_variants_groups_id'));
              $Qinsert->bindInt(':products_variants_values_id', $Qentries->valueInt('products_variants_values_id'));
              $Qinsert->execute();
              
              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }
      
     // products_images
      if ( $error == false && $data['copy_images'] == 1 ) {
        $Qimages = $osC_Database->query('select * from :table_products_images where products_id = :products_id');
        $Qimages->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qimages->bindInt(':products_id', $id);
        $Qimages->execute();

        while ( $Qimages -> next() ){
          $image = $Qimages->value('image');
          
          $Qinsert = $osC_Database->query('insert into :table_products_images (products_id, image, default_flag, sort_order, date_added) values (:products_id, :image, :default_flag, :sort_order, :date_added)');
          $Qinsert->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindValue(':image', $image);
          $Qinsert->bindInt(':default_flag', $Qimages->valueInt('default_flag'));
          $Qinsert->bindInt(':sort_order', 0);
          $Qinsert->bindRaw(':date_added', 'now()');
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          } else {
            $images_id = $osC_Database->nextID();
            $pos = strlen($Qimages->value('products_id') . '_' . $Qimages->value('id') . '_');
            
            $new_image = $products_id . '_' . $images_id . '_' . substr($image, $pos);
            @copy('../images/products/originals/' . $image, '../images/products/originals/' . $new_image);
            
            //update image name
            $Qupdate = $osC_Database->query('update :table_products_images set image = :image where id = :id');
            $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qupdate->bindValue(':image', $new_image);
            $Qupdate->bindInt(':id', $images_id);
            $Qupdate->execute();

            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($new_image, $group['id'], 'products');
              }
            }
            
            //update products variants images id
            $Qupdate = $osC_Database->query('update :table_products_variants set products_images_id = :new_products_images_id where products_id = :products_id and products_images_id = :old_products_images_id');
            $Qupdate->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $Qupdate->bindInt(':old_products_images_id', $Qimages->valueInt('id'));
            $Qupdate->bindInt(':new_products_images_id', $images_id);
            $Qupdate->bindInt(':products_id', $products_id);
            $Qupdate->execute();
          }
        }
      }
          
      //products_attributes
      if ( $error == false && $data['copy_attributes'] == 1 ) {
        $Qattributes = $osC_Database->query('select * from :table_products_attributes where products_id = :products_id');
        $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
        $Qattributes->bindInt(':products_id', $id);
        $Qattributes->execute();
        
        while ( $Qattributes->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_products_attributes (products_id, products_attributes_values_id, language_id, value) values (:products_id, :products_attributes_values_id, :language_id, :value)');
          $Qinsert->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':products_attributes_values_id', $Qattributes->valueInt('products_attributes_values_id'));
          $Qinsert->bindInt(':language_id', $Qattributes->valueInt('language_id'));
          $Qinsert->bindValue(':value', $Qattributes->value('value'));
          $Qinsert->execute();
  
          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      //product accessories
      if ( $error == false && $data['copy_accessories'] == 1 ) {
        $Qaccessories = $osC_Database->query('select * from :table_products_accessories where products_id = :products_id');
        $Qaccessories->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
        $Qaccessories->bindInt(':products_id', $id);
        $Qaccessories->execute();
        
        while ( $Qaccessories->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_products_accessories (products_id, accessories_id) values (:products_id , :accessories_id )');
          $Qinsert->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':accessories_id', $Qaccessories->valueInt('accessories_id'));
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      //product customization fields
      if ( $error == false && $data['copy_customization_fields'] == 1 ) {
        $Qfields = $osC_Database->query('select customization_fields_id, type, is_required from :table_customization_fields where products_id = :products_id');
        $Qfields->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
        $Qfields->bindInt(':products_id', $id);
        $Qfields->execute();

        while ( $Qfields->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_customization_fields (products_id, type, is_required) values (:products_id , :type, :is_required)');
          $Qinsert->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':type', $Qfields->valueInt('type'));
          $Qinsert->bindInt(':is_required', $Qfields->valueInt('is_required'));
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          } else {
            $customization_fields_id = $osC_Database->nextID();
            
            $Qnames = $osC_Database->query('select * from :table_customization_fields_description where customization_fields_id = :customization_fields_id');
            $Qnames->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
            $Qnames->bindInt(':customization_fields_id', $Qfields->valueInt('customization_fields_id'));
            $Qnames->execute();
          
            while ($Qnames->next()) {
              $Qinsert = $osC_Database->query('insert into :table_customization_fields_description (customization_fields_id, languages_id, name) values (:customization_fields_id, :languages_id , :name)');
              $Qinsert->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
              $Qinsert->bindInt(':customization_fields_id', $customization_fields_id);
              $Qinsert->bindInt(':languages_id', $Qnames->valueInt('languages_id'));
              $Qinsert->bindValue(':name', $Qnames->value('name'));
              $Qinsert->execute();
              
              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }
      
      //xsell products
      if ( $error == false && $data['copy_xsell'] == 1 ) {
        $Qxsell = $osC_Database->query('select * from :table_products_xsell where products_id = :products_id');
        $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
        $Qxsell->bindInt(':products_id', $id);
        $Qxsell->execute();
        
        while ( $Qxsell->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_products_xsell (products_id, xsell_products_id) values (:products_id , :xsell_products_id )');
          $Qinsert->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':xsell_products_id', $Qxsell->valueInt('xsell_products_id'));
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      //product attachments
      if ( $error == false && $data['copy_attachments'] == 1 ) {
        $Qattachments = $osC_Database->query('select attachments_id from :table_products_attachments_to_products where products_id = :products_id');
        $Qattachments->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
        $Qattachments->bindInt(':products_id', $id);
        $Qattachments->execute();

        while ( $Qattachments->next() ) {
          $Qinsert = $osC_Database->query('insert into :table_products_attachments_to_products (products_id, attachments_id) values (:products_id , :attachments_id )');
          $Qinsert->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
          $Qinsert->bindInt(':products_id', $products_id);
          $Qinsert->bindInt(':attachments_id', $Qattachments->valueInt('attachments_id'));
          $Qinsert->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');
        osC_Cache::clear('feature-products');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function setDateAvailable($id, $data) {
      global $osC_Database;

      $Qproduct = $osC_Database->query('update :table_products set products_date_available = :products_date_available, products_last_modified = now() where products_id = :products_id');
      $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);

      if (date('Y-m-d') < $data['date_available']) {
        $Qproduct->bindValue(':products_date_available', $data['date_available']);
      } else {
        $Qproduct->bindRaw(':products_date_available', 'null');
      }

      $Qproduct->bindInt(':products_id', $id);
      $Qproduct->setLogging($_SESSION['module'], $id);
      $Qproduct->execute();

      if (!$osC_Database->isError()) {
        return true;
      }

      return false;
    }
    
    function setFrontPage($id, $flag) {
      global $osC_Database;
      
      if($flag == 1) {
        $Qcheck = $osC_Database->query('select products_id from :table_products_frontpage where products_id = :products_id');
        $Qcheck->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
        $Qcheck->bindInt(':products_id', $id);
        $Qcheck->execute();
        
        if ($Qcheck->numberOfRows() > 0) {
          return true;
        }

        $Qorder = $osC_Database->query('select max(sort_order) as sort_order from :table_products_frontpage');
        $Qorder->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
        $Qorder->execute();

        $sort_order = $Qorder->valueInt('sort_order') + 1;
        
        $Qstatus = $osC_Database->query('insert into :table_products_frontpage (products_id, sort_order) values (:products_id, :sort_order)');
        $Qstatus->bindInt(':sort_order', $sort_order);
      } else {
        $Qstatus = $osC_Database->query('delete from :table_products_frontpage where products_id = :products_id');
      }
      
      $Qstatus->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qstatus->bindInt(':products_id', $id);
      $Qstatus->execute();
      
      if(!$osC_Database->isError()) {
      	osC_Cache::clear('new_products');
        osC_Cache::clear('feature-products');
        
        return true;
      }

      return false;
    }
    
    function setStatus($id, $flag) {
      global $osC_Database;
      
      $error = false;
    
      //customers basket
      if ($flag == 0) {
        $Qcb = $osC_Database->query('delete from :table_customers_basket where products_id = :products_id or products_id like :products_id');
        $Qcb->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
        $Qcb->bindInt(':products_id', $id);
        $Qcb->bindValue(':products_id', (int)$id . '#%');
        $Qcb->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      }
          
      if ($error == false) {
        $Qstatus = $osC_Database->query('update :table_products set products_status = :products_status where products_id = :products_id');
        $Qstatus->bindTable(':table_products', TABLE_PRODUCTS);
        $Qstatus->bindInt(":products_id", $id);
        $Qstatus->bindValue(":products_status", $flag);
        $Qstatus->execute();
        
        if(!$osC_Database->isError()) {
          osC_Cache::clear('categories');
          osC_Cache::clear('category_tree');
          osC_Cache::clear('product-' . $id);
          osC_Cache::clear('also_purchased');
          osC_Cache::clear('sefu-products');
          osC_Cache::clear('new_products');
          osC_Cache::clear('feature-products');
          
          return true;
        }
      }

      return false;
    }
  }
?>
