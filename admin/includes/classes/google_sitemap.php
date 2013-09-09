<?php
/*
  $Id: google_sitemap.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('../includes/functions/html_output.php');
  require_once('../includes/classes/sefu.php');
  require_once('../includes/classes/category_tree.php');

  class toC_Google_Sitemap {

    var $_file_name = '',
        $_save_path = '',
        $_base_url = '',
        $_max_entries = 0,
        $_max_file_size = 0,
        $_file_array = array(),
        $_compression = false,
        $_products_change_freq = '',
        $_categories_change_freq = '',
        $_articles_change_freq = '',
        $_products_priority = '',
        $_categories_priority = '',
        $_articles_priority = '',
        $_original_language_code = '',
        $_sefu = null;

    function toC_Google_Sitemap($language_code = 'en_US', $products_change_freq = 'weekly', $products_priority = 0.5, $categories_change_freq = 'weekly', $categories_priority = 0.5, $articles_change_freq = 'weekly', $articles_priority = 0.25){
      global $osC_CategoryTree, $osC_Language;
      
      $this->_original_language_code = $osC_Language->getCode();
      
      $osC_Language->set($language_code);
      
      if ($language_code !== 'en_US') {
        $this->_file_name = "sitemaps_{$language_code}_";
      }else {
        $this->_file_name = "sitemaps";
      }
      
      $this->_save_path = DIR_FS_CATALOG;
      $this->_base_url = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      $this->_max_file_size = 10 * 1024 * 1024;
      $this->_max_entries = 50000;
      $this->_sefu = new toC_Sefu();
      
      $this->_products_change_freq = $products_change_freq;
      $this->_products_priority = $products_priority;
      $this->_categories_change_freq = $categories_change_freq;
      $this->_categories_priority = $categories_priority;
      $this->_articles_change_freq = $articles_change_freq;
      $this->_articles_priority = $articles_priority;
      
      $osC_CategoryTree = new osC_CategoryTree();
    }
    
    function generateSitemap() {
      return $this->_createCategorySitemap() && $this->_createProductSitemap() && $this->_createArticleSitemap() && $this->_createIndexSitemap();
    }

    function setCompression($compression) {
      if($compression == 1)
        $this->_compression = true;
    }

    function _hrefLink($page, $parameters) {
      $link = osc_href_link($page, $parameters, 'NONSSL', false);

      return $this->_sefu->generateURL($link, $page, $parameters);
    }

    function _createSitemapFile($file) {
      $file_name = $this->_save_path  . $this->_file_name . $file . '.xml';
			
      if ($this->_compression == true) {
        $file_name .= '.gz';
        $handle = gzopen($file_name,'wb9');
      } else {
        $handle = fopen($file_name, 'w');
      }

      $this->_file_array[] =  $file_name;

      return $handle;
    }

    function _writeFile($handle, $data) {
      if ($this->_compression == true) {
        gzwrite($handle, $data);
      } else {
        fwrite($handle, $data);
      }
    }

    function _writeSitemapFile(&$handle, $data, &$num_of_entries, &$num_of_files, $type) {
      $num_of_entries++;
      $this->_writeFile($handle, $data);

      if ( ($num_of_entries >= $this->_max_entries) || (filesize(end($this->_file_array)) >= $this->_max_file_size)) {
        $num_of_entries = 0;
        $num_of_files++;
        $handle = $this->_recreateSitemap($handle, $type, $num_of_files);
      }
    }

    function _closeSitemapFile($handle) {
      if($this->_compression) {
        fwrite($handle, '</urlset>');
        fclose($handle);
      }else{
        gzwrite($handle, '</urlset>');
        gzclose($handle);
      }
    }

    function _createUrlElement($url, $last_mod, $change_freq, $priority) {
      global $osC_Language;
      
      $xml = "\t" . '<url>' . "\n";
      
      //multiple language
      if (count($osC_Language->getAll() > 0)) {
        $xml .= "\t\t" . '<loc>' . $url . '?language=' . $osC_Language->getCode() . '</loc>' . "\n";
      }else {
        $xml .= "\t\t" . '<loc>' . $url . '</loc>' . "\n";
      }
      
      $xml .= "\t\t" . '<lastmod>' . $last_mod . '</lastmod>' . "\n";
      $xml .= "\t\t" . '<changefreq>' . $change_freq . '</changefreq>' . "\n";
      $xml .= "\t\t" . '<priority>' . $priority . '</priority>' . "\n";
      $xml .= "\t" . '</url>' . "\n";

      return $xml;
    }

    function _createXmlHeader() {
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

      return $xml;
    }

    function _createIndexSitemap() {
      global $osC_Language;
      
      $handle = fopen($this->_save_path . 'sitemapsIndex.xml', 'w');
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
      fwrite($handle, $xml);
      
      $directory_listing = new osC_DirectoryListing($this->_save_path);
      $directory_listing->setIncludeDirectories(false);
      $directory_listing->setIncludeFiles(true);
      $directory_listing->setCheckExtension('xml');
      $xmls = $directory_listing->getFiles();
      
      if (!empty($xmls)) {
        foreach($xmls as $xml) {
          if (($xml['name'] !== $this->_file_name . 'Index.xml') && preg_match('/^sitemaps[A-Za-z_]+\.xml$/', $xml['name'])) {
            $content = "\t". '<sitemap>' . "\n";
            $content .= "\t\t" . '<loc>'.$this->_base_url . basename($xml['name']) . '</loc>' . "\n";
            $content .= "\t\t" . '<lastmod>'.date ("Y-m-d", filemtime($this->_save_path . basename($xml['name']))).'</lastmod>' . "\n";
            $content .= "\t" . '</sitemap>' . "\n";
            fwrite($handle, $content);
          }
        }
      }

      fwrite($handle, '</sitemapindex>');

      fclose($handle);
      
      $osC_Language->set($this->_original_language_code);

      return true;
    }

    function _recreateSitemap($handle, $filename, $num_of_file) {
        $this->_closeSitemapFile($handle);
        $file = $filename . $num_of_file;
        $handle = $this->_createSitemapFile($file);
        $this->_writeFile($handle, $this->_createXmlHeader());

        return $handle;
    }

    function _createProductSitemap() {
      global $osC_Database;
			
      $num_of_entries = 0;
      $num_of_product_file = 0;

      $Qproducts = $osC_Database->query('select products_id, if( products_last_modified is null , products_date_added, products_last_modified ) as last_modified, products_ordered from :table_products where products_status=1 order by products_ordered desc');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->execute();

      $handle = $this->_createSitemapFile('Products');
      $this->_writeFile($handle, $this->_createXmlHeader());

      while ( $Qproducts->next() ) {
        $location = $this->_base_url . $this->_hrefLink(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id'));
        $last_mod = date ("Y-m-d", osC_DateTime::getTimestamp($Qproducts->value('last_modified')));

        $this->_writeSitemapFile($handle, $this->_createUrlElement($location, $last_mod, $this->_products_change_freq, $this->_products_priority), $num_of_entries, $num_of_product_file, 'Product');
      }
      $Qproducts->freeResult();
      $this->_closeSitemapFile($handle);

      return true;
    }

    function _createCategorySitemap() {
      global $osC_Database, $osC_CategoryTree;

      $num_of_entries = 0;
      $num_of_category_file = 0;

      $Qcategories = $osC_Database->query('select categories_id, if( last_modified is null , date_added, last_modified ) as last_modified from :table_categories order by parent_id asc, sort_order asc, categories_id asc');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->execute();

      $handle = $this->_createSitemapFile('Categories');
      $this->_writeFile($handle, $this->_createXmlHeader());
      while ( $Qcategories->next() ) {
        $location    = $this->_base_url . $this->_hrefLink(FILENAME_DEFAULT, 'cPath=' . $osC_CategoryTree->getFullcPath($Qcategories->valueInt('categories_id')));
        $last_mod    = date ("Y-m-d", osC_DateTime::getTimestamp( $Qcategories->value('last_modified')));

        $this->_writeSitemapFile($handle, $this->_createUrlElement($location, $last_mod, $this->_categories_change_freq, $this->_categories_priority), $num_of_entries, $num_of_category_file, 'Category');
      }
      $Qcategories->freeResult();
      $this->_closeSitemapFile($handle);

      return true;
    }

    function _createArticleSitemap() {
      global $osC_Database;

      $num_of_entries = 0;
      $num_of_article_file = 0;

      $Qarticles = $osC_Database->query('select articles_id , if( articles_last_modified  is null || articles_last_modified in (\'0000-00-00 00:00:00\'), articles_date_added, articles_last_modified  ) as last_modified from :table_articles order by articles_order asc, articles_id asc');
      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->execute();
			
      $handle = $this->_createSitemapFile('Articles');
      $this->_writeFile($handle, $this->_createXmlHeader());
      while ( $Qarticles->next() ) {
        $location    = $this->_base_url . $this->_hrefLink(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->valueInt('articles_id'));
        $last_mod    = date ("Y-m-d", osC_DateTime::getTimestamp( $Qarticles->value('last_modified')));
        $change_freq = $article_change_frequency;

        $this->_writeSitemapFile($handle, $this->_createUrlElement($location, $last_mod, $this->_articles_change_freq, $this->_articles_priority), $num_of_entries, $num_of_article_file, 'Article');
      }
      $Qarticles->freeResult();
      $this->_closeSitemapFile($handle);

      return true;
    }

    function getSubmitURL() {
      $sitemap_url = $this->_base_url . 'sitemapsIndex.xml';
      return htmlspecialchars( utf8_encode('http://www.google.com/webmasters/sitemaps/ping?sitemap=' . $sitemap_url));
    }
  }

?>
