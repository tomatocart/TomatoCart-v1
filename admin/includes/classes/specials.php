<?php
/*
  $Id: specials.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Specials_Admin {
    /**
     * Get the products
     * 
     * @access public
     * @param $start int used for pagination
     * @param $limit int used for pagination
     * @return array
     */
    function getProducts($start, $limit) {
      global $osC_Database, $osC_Language;
      
      $Qproducts = $osC_Database->query('select p.products_id, pd.products_name, p.products_tax_class_id from :table_products p, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id and p.products_type <> :products_type');
      $Qproducts->appendQuery(' order by pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':products_type', PRODUCT_TYPE_GIFT_CERTIFICATE);
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
      
      $result = array();
      while ($Qproducts->next()) {
        $result['products'][] = array('products_id' => $Qproducts->valueInt('products_id'),
                                      'products_name' => $Qproducts->value('products_name'),
                                      'products_tax_class_id' => $Qproducts->valueInt('products_tax_class_id'));
      }
      
      $result['total'] = $Qproducts->getBatchSize();
      
      return $result;
    }
    
    /**
     * Get the variants products
     *
     * @access public
     * @param $start int used for pagination
     * @param $limit int used for pagination
     * @return array
     */
    function getVariantsProducts($start, $limit) {
      global $osC_Database, $osC_Language;
      
      //get the general data for the variants products
      $Qproducts = $osC_Database->query('select pd.products_name, pv.products_variants_id, p.products_tax_class_id from :table_products_variants pv inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) where p.products_type <> :products_type and pv.products_status = 1 order by pd.products_name');
      $Qproducts->bindTable(':table_original_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':products_type', PRODUCT_TYPE_GIFT_CERTIFICATE);
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
      
      //get the variants group name and value name
      $result = array();
      if ($Qproducts->numberOfRows() > 0) {
        while($Qproducts->next()) {
          $product = array('products_id' => $Qproducts->valueInt('products_variants_id'),
                           'products_tax_class_id' => $Qproducts->valueInt('products_tax_class_id'));
          
          $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants_entries pve inner join :table_products_variants_groups pvg on (pve.products_variants_groups_id = pvg.products_variants_groups_id and pvg.language_id = :group_language_id) inner join :table_products_variants_values pvv on (pve.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :value_language_id) where pve.products_variants_id = :products_variants_id');
          $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
          $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
          $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
          $Qvariants->bindInt(':group_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':value_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':products_variants_id',  $Qproducts->valueInt('products_variants_id'));
          $Qvariants->execute();
          
          //attach the group and value for the products name
          $product['products_name'] = $Qproducts->value('products_name');
          if ($Qvariants->numberOfRows() > 0) {
            while($Qvariants->next()) {
              $product['products_name'] .= '(' . $Qvariants->value('products_variants_groups_name') . ':' . $Qvariants->value('products_variants_values_name') . ')';
            }
          }
          
          $result['products'][] = $product;
        }
      }
      
      $result['total'] = $Qproducts->getBatchSize();
      
      return $result;
    }
    
    /**
     * load the variants products for batch add
     *
     * @access public
     * @param $in_categories array filter
     * @param $manufacturer int filter
     * @param $products_sku string filter
     * @param $products_name string filter
     * @return array
     */
    function loadVariantsProducts($in_categories = array(), $manufacturer = null, $products_sku = null, $products_name = null) {
      global $osC_Database, $osC_Language;
      
      //filter categories
      if (count($in_categories) > 0) {
        $Qproducts = $osC_Database->query('select pd.products_name, pv.products_variants_id, pv.products_price from :table_products_variants pv inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) inner join :table_products_to_categories p2c on p.products_id = p2c.products_id where p.products_type <> :products_type and pv.products_status = 1 and p2c.categories_id in (:categories_id)');
        $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qproducts->bindRaw(':categories_id', implode(',', $in_categories));
      }else {
        $Qproducts = $osC_Database->query('select pd.products_name, pv.products_variants_id, pv.products_price from :table_products_variants pv inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) where p.products_type <> :products_type and pv.products_status = 1');
      }
      
      //filter manufacturer
      if ($manufacturer !== null) {
        $Qproducts->appendQuery('and p.manufacturers_id = :manufacturers_id');
        $Qproducts->bindValue(':manufacturers_id', $manufacturer);
      }
      
      //filter products sku
      if ($products_sku !== null) {
        $Qproducts->appendQuery('and p.products_sku like :products_sku');
        $Qproducts->bindValue(':products_sku', '%' . $products_sku . '%');
      }
      
      //filter products name
      if ($products_name !== null) {
        $Qproducts->appendQuery('and pd.products_name like :products_name');
        $Qproducts->bindValue(':products_name', '%' . $products_name . '%');
      }
      
      $Qproducts->appendQuery('and pv.products_variants_id not in (select products_variants_id from :table_variants_specials)');
      
      $Qproducts->appendQuery('order by pd.products_name');
      $Qproducts->bindTable(':table_original_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qproducts->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':products_type', PRODUCT_TYPE_GIFT_CERTIFICATE);
      $Qproducts->execute();
      
      $result = array();
      if ($Qproducts->numberOfRows() > 0) {
        while($Qproducts->next()) {
          $product = array('products_id' => $Qproducts->valueInt('products_variants_id'), 
                           'products_name' => $Qproducts->value('products_name'), 
                           'products_price' => $Qproducts->value('products_price'), 
                           'special_price' => 0);
          
          $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants_entries pve inner join :table_products_variants_groups pvg on (pve.products_variants_groups_id = pvg.products_variants_groups_id and pvg.language_id = :group_language_id) inner join :table_products_variants_values pvv on (pve.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :value_language_id) where pve.products_variants_id = :products_variants_id');
          $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
          $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
          $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
          $Qvariants->bindInt(':group_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':value_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':products_variants_id',  $Qproducts->valueInt('products_variants_id'));
          $Qvariants->execute();
          
          //attach the group and value for the products name
          if ($Qvariants->numberOfRows() > 0) {
            while($Qvariants->next()) {
              $product['products_name'] .= '<br />(' . $Qvariants->value('products_variants_groups_name') . ':' . $Qvariants->value('products_variants_values_name') . ')';
            }
          }
          
          $result[] = $product;
        }
      }
      
      return $result;
    }
    
    /**
     * Get the variants specials
     *
     * @access public
     * @param $start int used for pagination
     * @param $limit int used for pagination
     * @param $in_categories int filter categories ids
     * @param $search string filter keywords
     * @param $manufacturers_id int filter manaufacturer id
     * @return array
     */
    function getVariantsSpecials($start, $limit, $in_categories = array(), $search = null, $manufacturers_id = null) {
      global $osC_Language, $osC_Database;
      
      //filter categories
      if (count($in_categories) > 0) {
        $Qspecials = $osC_Database->query('select vs.*, pv.products_price, p.products_id, pd.products_name from :table_variants_specials vs inner join :table_products_variants pv on vs.products_variants_id = pv.products_variants_id inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) inner join :table_products_to_categories p2c on (p.products_id = p2c.products_id and p2c.categories_id in (:categories_id))');
        $Qspecials->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qspecials->bindRaw(':categories_id', implode(',', $in_categories));
      } else {
        $Qspecials = $osC_Database->query('select vs.*, pv.products_price, p.products_id, pd.products_name from :table_variants_specials vs inner join :table_products_variants pv on vs.products_variants_id = pv.products_variants_id inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id)');
      }
      
      //filter search keyword
      if ($search !== null) {
        $Qspecials->appendQuery('and pd.products_name like :products_name');
        $Qspecials->bindValue(':products_name', '%' . $search . '%');
      }
      
      //filter manufacturer
      if ($manufacturers_id !== null) {
        $Qspecials->appendQuery('and p.manufacturers_id = :manufacturers_id');
        $Qspecials->bindValue(':manufacturers_id', $manufacturers_id);
      }
      
      $Qspecials->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
      $Qspecials->bindTable(':table_original_products', TABLE_PRODUCTS);
      $Qspecials->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qspecials->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qspecials->bindInt(':language_id', $osC_Language->getID());
      $Qspecials->setExtBatchLimit($start, $limit);
      $Qspecials->execute();
      
      $result = array('total' => $Qspecials->getBatchSize(), 'special_products' => array());
      if ($Qspecials->numberOfRows() > 0) {
        while($Qspecials->next()) {
          $special_product = array('specials_id' => $Qspecials->valueInt('variants_specials_id'),
                                   'products_id' => $Qspecials->valueInt('products_variants_id'),
                                   'products_price' => $Qspecials->value('products_price'),
                                   'variants_specials_price' => $Qspecials->value('variants_specials_price'));
          
          //attach the group and value for the products name
          $special_product['products_name'] = $Qspecials->value('products_name');
          $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants_entries pve inner join :table_products_variants_groups pvg on (pve.products_variants_groups_id = pvg.products_variants_groups_id and pvg.language_id = :group_language_id) inner join :table_products_variants_values pvv on (pve.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :value_language_id) where pve.products_variants_id = :products_variants_id');
          $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
          $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
          $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
          $Qvariants->bindInt(':group_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':value_language_id', $osC_Language->getID());
          $Qvariants->bindInt(':products_variants_id',  $Qspecials->valueInt('products_variants_id'));
          $Qvariants->execute();
          
          if ($Qvariants->numberOfRows() > 0) {
            while($Qvariants->next()) {
              $special_product['products_name'] .= '(' . $Qvariants->value('products_variants_groups_name') . ':' . $Qvariants->value('products_variants_values_name') . ')';
            }
          }
          
          $result['special_products'][] = $special_product;
        }
      }
      
      return $result;
    }
    
    /**
     * Get the data of variants special
     *
     * @access public
     * @param $id int
     * @return array
     */
    function getVariantsData($id) {
      global $osC_Database, $osC_Language;
      
      $Qvariants_special = $osC_Database->query('select pd.products_name, pv.products_price, vs.variants_specials_id as specials_id, vs.products_variants_id as products_id, vs.variants_specials_price as specials_new_products_price, vs.specials_date_added, vs.specials_last_modified, vs.start_date, vs.expires_date, vs.date_status_change, vs.status from :table_variants_specials vs inner join :table_products_variants pv on vs.products_variants_id = pv.products_variants_id inner join :table_original_products p on pv.products_id = p.products_id inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) where vs.variants_specials_id = :specials_id');
      $Qvariants_special->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
      $Qvariants_special->bindTable(':table_original_products', TABLE_PRODUCTS);
      $Qvariants_special->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qvariants_special->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qvariants_special->bindInt(':language_id', $osC_Language->getID());
      $Qvariants_special->bindInt(':specials_id', $id);
      $Qvariants_special->execute();
      
      $data = $Qvariants_special->toArray();
      
      //attach the variant group and value for the products name
      $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants_entries pve inner join :table_products_variants_groups pvg on (pve.products_variants_groups_id = pvg.products_variants_groups_id and pvg.language_id = :group_language_id) inner join :table_products_variants_values pvv on (pve.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :value_language_id) where pve.products_variants_id = :products_variants_id');
      $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
      $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
      $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qvariants->bindInt(':group_language_id', $osC_Language->getID());
      $Qvariants->bindInt(':value_language_id', $osC_Language->getID());
      $Qvariants->bindInt(':products_variants_id',  $data['products_id']);
      $Qvariants->execute();
      
      if ($Qvariants->numberOfRows() > 0) {
        while($Qvariants->next()) {
          $data['products_name'] .= '(' . $Qvariants->value('products_variants_groups_name') . ':' . $Qvariants->value('products_variants_values_name') . ')';
        }
      }
      
      return $data;
    }
    
    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qspecial = $osC_Database->query('select p.products_id, pd.products_name, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.start_date, s.expires_date, s.date_status_change, s.status from :table_products p, :table_specials s, :table_products_description pd where s.specials_id = :specials_id and s.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = :language_id limit 1');
      $Qspecial->bindTable(':table_specials', TABLE_SPECIALS);
      $Qspecial->bindTable(':table_products', TABLE_PRODUCTS);
      $Qspecial->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qspecial->bindInt(':specials_id', $id);
      $Qspecial->bindInt(':language_id', $osC_Language->getID());
      $Qspecial->execute();

      $data = $Qspecial->toArray();

      $Qspecial->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database;

      $error = false;
      
      //variants specials
      if ($data['variants'] == 'on') {
        $Qproduct = $osC_Database->query('select products_price, products_id from :table_products_variants where products_variants_id = :products_id limit 1');
        $Qproduct->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      }else {
        $Qproduct = $osC_Database->query('select products_price from :table_products where products_id = :products_id limit 1');
        $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);
      }

      $Qproduct->bindInt(':products_id', $data['products_id']);
      $Qproduct->execute();

      $specials_price = $data['specials_price'];

      if ( substr($specials_price, -1) == '%' ) {
        $specials_price = $Qproduct->valueDecimal('products_price') - (((double)$specials_price / 100) * $Qproduct->valueDecimal('products_price'));
      }

      if ( ( $specials_price < '0.00' ) || ( $specials_price >= $Qproduct->valueDecimal('products_price') ) ) {
        $error = true;

//HPDL        $osC_MessageStack->add_session($this->_module, ERROR_SPECIALS_PRICE, 'error');
      }

      if ( $data['expires_date'] < $data['start_date'] ) {
        $error = true;

//HPDL        $osC_MessageStack->add_session($this->_module, ERROR_SPECIALS_DATE, 'error');
      }

      if ( $error == false ) {
        if ( is_numeric($id) ) {
          //update variants specials
          if ($data['variants'] == 'on') {
            $Qspecial = $osC_Database->query('update :table_variants_specials set variants_specials_price = :specials_new_products_price, specials_last_modified = now(), expires_date = :expires_date, start_date = :start_date, status = :status where variants_specials_id = :specials_id');
            $Qspecial->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
          }else {
            $Qspecial = $osC_Database->query('update :table_specials set specials_new_products_price = :specials_new_products_price, specials_last_modified = now(), expires_date = :expires_date, start_date = :start_date, status = :status where specials_id = :specials_id');
            $Qspecial->bindTable(':table_specials', TABLE_SPECIALS);
          }
          
          $Qspecial->bindInt(':specials_id', $id);
        } else {
          //insert variants specials
          if ($data['variants'] == 'on') {
            $Qspecial = $osC_Database->query('insert into :table_variants_specials (products_variants_id, variants_specials_price, specials_date_added, expires_date, start_date, status) values (:products_id, :specials_new_products_price, now(), :expires_date, :start_date, :status)');
            $Qspecial->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
          }else {
            $Qspecial = $osC_Database->query('insert into :table_specials (products_id, specials_new_products_price, specials_date_added, expires_date, start_date, status) values (:products_id, :specials_new_products_price, now(), :expires_date, :start_date, :status)');
            $Qspecial->bindTable(':table_specials', TABLE_SPECIALS);
          }
          
          $Qspecial->bindInt(':products_id', $data['products_id']);
        }

        $Qspecial->bindValue(':specials_new_products_price', $specials_price);
        $Qspecial->bindValue(':expires_date', $data['expires_date']);
        $Qspecial->bindValue(':start_date', $data['start_date']);
        $Qspecial->bindInt(':status', $data['status']);
        $Qspecial->setLogging($_SESSION['module'], $id);
        $Qspecial->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ( $error === false ) {
        if ($data['variants'] == 'on') {
          osC_Cache::clear('product-' . $Qproduct->valueInt('products_id'));
          osC_Cache::clear('product-variants-specials-' . $data['products_id']);
        }else {
          osC_Cache::clear('product-' . $data['products_id']);
          osC_Cache::clear('product-specials-' . $data['products_id']);
        }
        
        osC_Cache::clear('new_products');
        
        return true;
      }

      return false;
    }

    /**
     * Delete the general specials or variants specials
     * 
     * @access public
     * @param $id int special id
     * @param $products_type int flag for general or variants
     * @return boolean
     */
    function delete($id, $products_type) {
      global $osC_Database;
      
      //delete general specials
      if ($products_type == 1) {
        $Qproduct = $osC_Database->query('select products_id from :table_specials where specials_id = :specials_id');
        $Qproduct->bindTable(':table_specials', TABLE_SPECIALS);
        $Qproduct->bindInt(':specials_id', $id);
        $Qproduct->setLogging($_SESSION['module'], $id);
        $Qproduct->execute();
        
        $products_id = $Qproduct->valueInt('products_id');
        
        $Qspecial = $osC_Database->query('delete from :table_specials where specials_id = :specials_id');
        $Qspecial->bindTable(':table_specials', TABLE_SPECIALS);
        $Qspecial->bindInt(':specials_id', $id);
        $Qspecial->setLogging($_SESSION['module'], $id);
        $Qspecial->execute();
        
        if ( !$osC_Database->isError() ) {
          osC_Cache::clear('product-' . $products_id);
          osC_Cache::clear('product-specials-' . $products_id);
        
          return true;
        }
      }
      
      //delete variants specials
      if ($products_type == 2) {
        $Qproduct = $osC_Database->query('select vs.products_variants_id, pv.products_id from :table_variants_specials vs inner join :table_products_variants pv on vs.products_variants_id = pv.products_variants_id where variants_specials_id = :specials_id');
        $Qproduct->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
        $Qproduct->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
        $Qproduct->bindInt(':specials_id', $id);
        $Qproduct->execute();
        
        $variant_products_id = $Qproduct->valueInt('products_variants_id');
        $products_id = $Qproduct->valueInt('products_id');
        
        $Qspecial = $osC_Database->query('delete from :table_variants_specials where variants_specials_id = :specials_id');
        $Qspecial->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
        $Qspecial->bindInt(':specials_id', $id);
        $Qspecial->execute();
        
        if ( !$osC_Database->isError() ) {
          osC_Cache::clear('product-' . $products_id);
          osC_Cache::clear('product-variants-specials-' . $variant_products_id);
        
          return true;
        }
      }

      return false;
    }
  }
?>
