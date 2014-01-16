<?php
/*
  $Id: search.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/products.php');

  class osC_Search extends osC_Products {
    var $_period_min_year = null,
        $_period_max_year = null,
        $_date_from = null,
        $_date_to = null,
        $_price_from = null,
        $_price_to = null,
        $_raw_keywords = null,
        $_keywords = null,
        $_search_terms_id = null,
        $_synonyms = null,
        $_number_of_results = null;

/* Class constructor */

    function osC_Search() {
      global $osC_Database;

      $Qproducts = $osC_Database->query('select min(year(products_date_added)) as min_year, max(year(products_date_added)) as max_year from :table_products limit 1');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->execute();

      $this->_period_min_year = $Qproducts->valueInt('min_year');
      $this->_period_max_year = $Qproducts->valueInt('max_year');
    }

/* Public methods */

    function getMinYear() {
      return $this->_period_min_year;
    }

    function getMaxYear() {
      return $this->_period_max_year;
    }

    function getDateFrom() {
      return $this->_date_from;
    }

    function getDateTo() {
      return $this->_date_to;
    }

    function getPriceFrom() {
      return $this->_price_from;
    }

    function getPriceTo() {
      return $this->_price_to;
    }

    function getKeywords() {
      return $this->_keywords;
    }
    
    function getSynonyms() {
      return $this->_synonyms;
    }

    function getNumberOfResults() {
      return $this->_number_of_results;
    }

    function hasDateSet($flag = null) {
      if ($flag == 'from') {
        return isset($this->_date_from);
      } elseif ($flag == 'to') {
        return isset($this->_date_to);
      }

      return isset($this->_date_from) && isset($this->_date_to);
    }

    function hasPriceSet($flag = null) {
      if ($flag == 'from') {
        return isset($this->_price_from);
      } elseif ($flag == 'to') {
        return isset($this->_price_to);
      }

      return isset($this->_price_from) && isset($this->_price_to);
    }

    function hasKeywords() {
      return isset($this->_keywords) && !empty($this->_keywords);
    }

    function hasSynonyms() {
      return isset($this->_synonyms) && !empty($this->_synonyms);
    }
    
    function setDateFrom($timestamp) {
      $this->_date_from = $timestamp;
    }

    function setDateTo($timestamp) {
      $this->_date_to = $timestamp;
    }

    function setPriceFrom($price) {
      $this->_price_from = $price;
    }

    function setPriceTo($price) {
      $this->_price_to = $price;
    }

    function setKeywords($keywords) {
      $terms = explode(' ', trim($keywords));

      $terms_array = array();

      $counter = 0;

      foreach ($terms as $word) {
        $counter++;

        if ($counter > 5) {
          break;
        } elseif (!empty($word)) {
          if (!in_array($word, $terms_array)) {
            $terms_array[] = $word;
          }
        }
      }

      $this->initialiseSynonyms($keywords);
      
      $this->_raw_keywords = $keywords;
      $this->_keywords = implode(' ', $terms_array);
    }

    function initialiseSynonyms($keywords) {
      global $osC_Database, $osC_Language;
      
      $Qsynonym = $osC_Database->query('select search_terms_id, synonym from :table_search_terms where text = :text');
      $Qsynonym->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qsynonym->bindValue(':text', trim($keywords));
      $Qsynonym->execute();
      
      if ($Qsynonym->numberOfRows() > 0) {
        $this->_search_terms_id =  $Qsynonym->valueInt('search_terms_id');  

        $synonyms = array();
        $synonym_array = explode(' ', trim($Qsynonym->value('synonym')));
        
        $counter = count($this->_keywords);
        foreach ($synonym_array as $word) {
          $counter++;
  
          if ($counter > 5) {
            break;
          } elseif (!empty($word)) {
            if (!in_array($word, $synonyms)) {
              $synonyms[] = $word;
            }
          }
        }
        
        $this->_synonyms = implode(' ', $synonyms);
      }
      $Qsynonym->freeResult();
    }
      
    function updateSearchTerm() {
      global $osC_Database;
      
      if ( is_numeric($this->_search_terms_id) ) {
        $Qterm = $osC_Database->query('update :table_search_terms set products_count = :products_count, search_count = search_count + 1 where search_terms_id = :search_terms_id');
        $Qterm->bindInt(':search_terms_id', $this->_search_terms_id);
      } else {
        $Qterm = $osC_Database->query('insert into :table_search_terms (text, products_count, search_count, show_in_terms, date_updated) values (:text, :products_count, :search_count, :show_in_terms, now())');
        $Qterm->bindValue(':text', $this->_raw_keywords);
        $Qterm->bindInt(':search_count', 1);
        $Qterm->bindInt(':show_in_terms', 0);
      }

      $Qterm->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qterm->bindInt(':products_count', $this->_number_of_results);
      $Qterm->execute();
      
      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
    
    function &execute() {
      global $osC_Database, $osC_Customer, $osC_Currencies, $osC_Language, $osC_Image, $osC_CategoryTree;
      
      $Qlisting = $osC_Database->query('select SQL_CALC_FOUND_ROWS distinct p.*, pd.*, m.*, i.image, vs.status, if(vs.status, vs.variants_specials_price, if(s.status, s.specials_new_products_price, null)) as specials_new_products_price, if(vs.status, vs.variants_specials_price, if(s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price))) as final_price');

      if (($this->hasPriceSet('from') || $this->hasPriceSet('to')) && (DISPLAY_PRICE_WITH_TAX == '1')) {
        $Qlisting->appendQuery(', sum(tr.tax_rate) as tax_rate');
      }

      $Qlisting->appendQuery('from :table_products p left join :table_products_variants pv on (p.products_id = pv.products_id and pv.is_default = 1) left join :table_manufacturers m using(manufacturers_id) left join :table_specials s on (p.products_id = s.products_id) left join :table_variants_specials vs on (vs.products_variants_id = pv.products_variants_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag)');
      $Qlisting->bindTable(':table_products', TABLE_PRODUCTS);
      $Qlisting->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qlisting->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qlisting->bindTable(':table_specials', TABLE_SPECIALS);
      $Qlisting->bindTable(':table_variants_specials', TABLE_VARIANTS_SPECIALS);
      $Qlisting->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qlisting->bindInt(':default_flag', 1);

      if (($this->hasPriceSet('from') || $this->hasPriceSet('to')) && (DISPLAY_PRICE_WITH_TAX == '1')) {
        if ($osC_Customer->isLoggedOn()) {
          $customer_country_id = $osC_Customer->getCountryID();
          $customer_zone_id = $osC_Customer->getZoneID();
        } else {
          $customer_country_id = STORE_COUNTRY;
          $customer_zone_id = STORE_ZONE;
        }

        $Qlisting->appendQuery('left join :table_tax_rates tr on p.products_tax_class_id = tr.tax_class_id left join :table_zones_to_geo_zones gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = 0 or gz.zone_country_id = :zone_country_id) and (gz.zone_id is null or gz.zone_id = 0 or gz.zone_id = :zone_id)');
        $Qlisting->bindTable(':table_tax_rates', TABLE_TAX_RATES);
        $Qlisting->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qlisting->bindInt(':zone_country_id', $customer_country_id);
        $Qlisting->bindInt(':zone_id', $customer_zone_id);
      }

      $Qlisting->appendQuery(', :table_products_description pd, :table_categories c, :table_products_to_categories p2c');
      $Qlisting->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qlisting->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qlisting->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);

      $Qlisting->appendQuery('where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id');
      $Qlisting->bindInt(':language_id', $osC_Language->getID());

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

      if ($this->hasKeywords()) {
        $Qlisting->prepareSearch($this->_keywords, array('pd.products_name', 'pd.products_description'), true);
      }

      if ($this->hasSynonyms()) {
        $Qlisting->prepareSearch($this->_synonyms, array('pd.products_name', 'pd.products_description'), true);
      }
      
      if ($this->hasDateSet('from')) {
        $Qlisting->appendQuery('and p.products_date_added >= :products_date_added');
        $Qlisting->bindValue(':products_date_added', date('Y-m-d H:i:s', $this->_date_from));
      }

      if ($this->hasDateSet('to')) {
        $Qlisting->appendQuery('and p.products_date_added <= :products_date_added');
        $Qlisting->bindValue(':products_date_added', date('Y-m-d H:i:s', $this->_date_to));
      }

      if ($this->hasPriceSet('from')) {
        if ($osC_Currencies->exists($_SESSION['currency'])) {
          $this->_price_from = $this->_price_from / $osC_Currencies->value($_SESSION['currency']);
        }
      }

      if ($this->hasPriceSet('to')) {
        if ($osC_Currencies->exists($_SESSION['currency'])) {
          $this->_price_to = $this->_price_to / $osC_Currencies->value($_SESSION['currency']);
        }
      }

      if (DISPLAY_PRICE_WITH_TAX == '1') {
        if ($this->_price_from > 0) {
          $Qlisting->appendQuery('and (if(vs.status, vs.variants_specials_price, if (s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price))) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= :price_from)');
          $Qlisting->bindFloat(':price_from', $this->_price_from);
        }

        if ($this->_price_to > 0) {
          $Qlisting->appendQuery('and (if(vs.status, vs.variants_specials_price, if (s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price))) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= :price_to)');
          $Qlisting->bindFloat(':price_to', $this->_price_to);
        }
      } else {
        if ($this->_price_from > 0) {
          $Qlisting->appendQuery('and (if(vs.status, vs.variants_specials_price, if (s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price))) >= :price_from)');
          $Qlisting->bindFloat(':price_from', $this->_price_from);
        }

        if ($this->_price_to > 0) {
          $Qlisting->appendQuery('and (if(vs.status, vs.variants_specials_price, if (s.status, s.specials_new_products_price, if (pv.products_price, pv.products_price, p.products_price))) <= :price_to)');
          $Qlisting->bindFloat(':price_to', $this->_price_to);
        }
      }

      if (($this->hasPriceSet('from') || $this->hasPriceSet('to')) && (DISPLAY_PRICE_WITH_TAX == '1')) {
        $Qlisting->appendQuery('group by p.products_id, tr.tax_priority');
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
      
      $this->_number_of_results = $Qlisting->getBatchSize();

      if ( $this->hasKeywords() ) {
        $this->updateSearchTerm();  
      }
      
      return $Qlisting;
    }
  }
?>
