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

  class osC_Products {
    var $_category,
        $_recursive = true,
        $_manufacturer,
        $_products_attributes,
        $_sql_query,
        $_sort_by,
        $_sort_by_direction;

/* Class constructor */

    function osC_Products($id = null) {
      if (is_numeric($id)) {
        $this->_category = $id;
      }
      
      if ( (defined('DISPLAY_SUBCATALOGS_PRODUCTS')) && ((int)DISPLAY_SUBCATALOGS_PRODUCTS == -1) ) {
        $this->_recursive = false;
      }
    }

/* Public methods */

    function hasCategory() {
      return isset($this->_category) && !empty($this->_category);
    }

    function isRecursive() {
      return $this->_recursive;
    }

    function hasManufacturer() {
      return isset($this->_manufacturer) && !empty($this->_manufacturer);
    }

    function hasProductAttributes() {
      return isset($this->_products_attributes) && !empty($this->_products_attributes);
    }

    function setCategory($id, $recursive = true) {
      $this->_category = $id;

      if ($recursive === false) {
        $this->_recursive = false;
      }
    }

    function setManufacturer($id) {
      $this->_manufacturer = $id;
    }

    function setProductAttributesFilter($products_attributes) {
      $this->_products_attributes = $products_attributes;
    }

    function setSortBy($field, $direction = '+') {
      switch ($field) {
        case 'sku':
          $this->_sort_by = 'p.products_sku';
          break;
        case 'manufacturer':
          $this->_sort_by = 'm.manufacturers_name';
          break;
        case 'quantity':
          $this->_sort_by = 'p.products_quantity';
          break;
        case 'weight':
          $this->_sort_by = 'p.products_weight';
          break;
        case 'price':
          $this->_sort_by = 'final_price';
          break;
      }

      $this->_sort_by_direction = ($direction == '-') ? '-' : '+';
    }

    function setSortByDirection($direction) {
      $this->_sort_by_direction = ($direction == '-') ? '-' : '+';
    }

    function &execute() {
      global $osC_Database, $osC_Language, $osC_CategoryTree, $osC_Image;
      
      $Qlisting = $osC_Database->query('select p.*, pd.*, m.*, if(s.status, s.specials_new_products_price, null) as specials_new_products_price, if(s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price)) as final_price, i.image from :table_products p left join :table_products_variants pv on (p.products_id = pv.products_id and pv.is_default = 1) left join :table_manufacturers m using(manufacturers_id) left join :table_specials s on (p.products_id = s.products_id) left join :table_manufacturers_info mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languages_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd');
      $Qlisting->bindTable(':table_products', TABLE_PRODUCTS);
      $Qlisting->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qlisting->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qlisting->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qlisting->bindTable(':table_specials', TABLE_SPECIALS);
      $Qlisting->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qlisting->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      
      if ($this->hasCategory()) {
        $Qlisting->appendQuery(', :table_categories c, :table_products_to_categories p2c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id');
        $Qlisting->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qlisting->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qlisting->appendQuery('and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
      }else {
        $Qlisting->appendQuery('where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
      }
      
      $Qlisting->bindInt(':default_flag', 1);
      $Qlisting->bindInt(':language_id', $osC_Language->getID());
      $Qlisting->bindInt(':languages_id', $osC_Language->getID());
      
      if ($this->hasCategory()) {
        if ($this->isRecursive()) {
          $subcategories_array = array($this->_category);

          $Qlisting->appendQuery('and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and p2c.categories_id in (:categories_id)');
          $Qlisting->bindRaw(':categories_id', implode(',', $osC_CategoryTree->getChildren($this->_category, $subcategories_array)));
        } else {
          $Qlisting->appendQuery('and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and pd.language_id = :language_id and p2c.categories_id = :categories_id');
          $Qlisting->bindInt(':language_id', $osC_Language->getID());
          $Qlisting->bindInt(':categories_id', $this->_category);
        }
      }
      
      if ($this->hasManufacturer()) {
        $Qlisting->appendQuery('and m.manufacturers_id = :manufacturers_id');
        $Qlisting->bindInt(':manufacturers_id', $this->_manufacturer);
      }
      
      $products = array();
      if ($this->hasProductAttributes()) {
        foreach ($this->_products_attributes as $products_attributes_values_id => $value) {
          if( !empty($value) ){
            $Qproducts = $osC_Database->query('select products_id from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id and value = :value and language_id = :language_id');
            $Qproducts->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
            $Qproducts->bindInt(':products_attributes_values_id', $products_attributes_values_id);
            $Qproducts->bindValue(':value', $value);
            $Qproducts->bindInt(':language_id', $osC_Language->getID());
            $Qproducts->execute();
            

            $tmp_products = array();
            while ($Qproducts->next()) {
              $tmp_products[] = $Qproducts->valueInt('products_id');
            }
            $products[] = $tmp_products;

            $Qproducts->freeResult();
          }
        }
      
        if (!empty($products)) {
          $products_ids = $products[0];

          for($i = 1; $i < sizeof($products); $i++) {
            $products_ids = array_intersect($products_ids, $products[$i]);
          }

          if ( !empty($products_ids) ) {
            $Qlisting->appendQuery('and p.products_id in (' . implode(',', $products_ids) . ' ) ');
          } else {
            //if no products match, then do not display any result
            $Qlisting->appendQuery('and 1 = 0 ');
          }
        }
      }

      $Qlisting->appendQuery('order by');

      if (isset($this->_sort_by)) {
        $Qlisting->appendQuery(':order_by :order_by_direction, pd.products_name');
        $Qlisting->bindRaw(':order_by', $this->_sort_by);
        $Qlisting->bindRaw(':order_by_direction', (($this->_sort_by_direction == '-') ? 'desc' : ''));
      } else {
        $Qlisting->appendQuery('pd.products_name :order_by_direction');
        $Qlisting->bindRaw(':order_by_direction', (($this->_sort_by_direction == '-') ? 'desc' : ''));
      }

      $Qlisting->setBatchLimit((isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1), MAX_DISPLAY_SEARCH_RESULTS);
      $Qlisting->execute();
      
      return $Qlisting;
    }
  }
?>
