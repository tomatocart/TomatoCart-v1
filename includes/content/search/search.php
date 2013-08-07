<?php
/*
  $Id: search.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/search.php');

  class osC_Search_Search extends osC_Template {

/* Private variables */

    var $_module = 'search',
        $_group = 'search',
        $_page_title,
        $_page_image = 'table_background_browse.gif',
        $_page_contents = 'search.php';

/* Class constructor */

    function osC_Search_Search() {
      global $osC_Services, $osC_Language, $breadcrumb, $osC_Search;

      $this->_page_title = $osC_Language->get('search_heading');

      $osC_Search = new osC_Search();

      if (isset($_GET['keywords'])) {
        $this->_page_title = $osC_Language->get('search_results_heading');
        $this->_page_contents = 'results.php';

        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($osC_Language->get('breadcrumb_search_results'), osc_href_link(FILENAME_SEARCH, osc_get_all_get_params()));
        }

        $this->_process();
      } else {
        $this->addJavascriptPhpFilename('templates/' . $this->getCode() . '/javascript/search/search.php');
      }
    }

/* Private methods */

    function _process() {
      global $osC_Language, $messageStack, $osC_Search, $Qlisting;

      if (isset($_GET['datefrom_days']) && is_numeric($_GET['datefrom_days']) && isset($_GET['datefrom_months']) && is_numeric($_GET['datefrom_months']) && isset($_GET['datefrom_years']) && is_numeric($_GET['datefrom_years'])) {
        if (@checkdate($_GET['datefrom_months'], $_GET['datefrom_days'], $_GET['datefrom_years'])) {
          $osC_Search->setDateFrom(mktime(0, 0, 0, $_GET['datefrom_months'], $_GET['datefrom_days'], $_GET['datefrom_years']));
        } else {
          $messageStack->add('search', $osC_Language->get('error_search_invalid_from_date'));
        }
      }

      if (isset($_GET['dateto_days']) && is_numeric($_GET['dateto_days']) && isset($_GET['dateto_months']) && is_numeric($_GET['dateto_months']) && isset($_GET['dateto_years']) && is_numeric($_GET['dateto_years'])) {
        if (@checkdate($_GET['dateto_months'], $_GET['dateto_days'], $_GET['dateto_years'])) {
          $osC_Search->setDateTo(mktime(23, 59, 59, $_GET['dateto_months'], $_GET['dateto_days'], $_GET['dateto_years']));
        } else {
          $messageStack->add('search', $osC_Language->get('error_search_invalid_to_date'));
        }
      }

      if ($osC_Search->hasDateSet()) {
        if ($osC_Search->getDateFrom() > $osC_Search->getDateTo()) {
          $messageStack->add('search', $osC_Language->get('error_search_to_date_less_than_from_date'));
        }
      }

      if (isset($_GET['pfrom']) && !empty($_GET['pfrom'])) {
        if (settype($_GET['pfrom'], 'double')) {
          $osC_Search->setPriceFrom($_GET['pfrom']);
        } else {
          $messageStack->add('search', $osC_Language->get('error_search_price_from_not_numeric'));
        }
      }

      if (isset($_GET['pto']) && !empty($_GET['pto'])) {
        if (settype($_GET['pto'], 'double')) {
          $osC_Search->setPriceTo($_GET['pto']);
        } else {
          $messageStack->add('search', $osC_Language->get('error_search_price_to_not_numeric'));
        }
      }

      if ($osC_Search->hasPriceSet('from') && $osC_Search->hasPriceSet('to') && ($osC_Search->getPriceFrom() >= $osC_Search->getPriceTo())) {
        $messageStack->add('search', $osC_Language->get('error_search_price_to_less_than_price_from'));
      }

      if (isset($_GET['keywords']) && is_string($_GET['keywords']) && !empty($_GET['keywords'])) {
        $osC_Search->setKeywords(urldecode($_GET['keywords']));

        if ($osC_Search->hasKeywords() === false) {
          $messageStack->add('search', $osC_Language->get('error_search_invalid_keywords'));
        }
      }

      if (!$osC_Search->hasKeywords() && !$osC_Search->hasPriceSet('from') && !$osC_Search->hasPriceSet('to') && !$osC_Search->hasDateSet('from') && !$osC_Search->hasDateSet('to')) {
        $messageStack->add('search', $osC_Language->get('error_search_at_least_one_input'));
      }

      if (isset($_GET['cPath']) && is_numeric($_GET['cPath']) && ($_GET['cPath'] > 0)) {
        $osC_Search->setCategory($_GET['cPath'], (isset($_GET['recursive']) && ($_GET['recursive'] == '1') ? true : false));
        
        //filter the products with category, manufactuer and shop by price
        if (isset($_GET['filter']) && is_numeric($_GET['filter']) && ($_GET['filter'] > 0)) {
          $osC_Search->setManufacturer($_GET['filter']);
        }
      }

      if (isset($_GET['manufacturers']) && is_numeric($_GET['manufacturers']) && ($_GET['manufacturers'] > 0)) {
        $osC_Search->setManufacturer($_GET['manufacturers']);
        
        //filter the products with category, manufactuer and shop by price
        if (isset($_GET['filter']) && is_numeric($_GET['filter']) && ($_GET['filter'] > 0)) {
          $osC_Search->setCategory($_GET['filter']);
        }
      }

      if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        if (strpos($_GET['sort'], '|d') !== false) {
          $osC_Search->setSortBy(substr($_GET['sort'], 0, -2), '-');
        } else {
          $osC_Search->setSortBy($_GET['sort']);
        }
      }

      if ($messageStack->size('search') > 0) {
        $this->_page_contents = 'search.php';
        $this->addJavascriptPhpFilename('templates/' . $this->getCode() . '/javascript/search/search.php');
      } else {
        $Qlisting = $osC_Search->execute();
      }
    }
  }
?>