<?php
/*
  $Id: language.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('../includes/classes/language.php');

  class osC_Language_Admin extends osC_Language {

/* Public methods */
    function loadIniFile($filename = null, $comment = '#', $language_code = null) {
      $ini_array = self::parseIniFile($filename, $comment, $language_code);

      $this->_definitions = array_merge($this->_definitions, $ini_array);
    }

    function parseIniFile($filename = null, $comment = '#', $language_code = null) {
      if ( is_null($language_code) ) {
        $language_code = $this->_code;
      }

      if ( $this->_languages[$language_code]['parent_id'] > 0 ) {
        $this->loadIniFile($filename, $comment, $this->getCodeFromID($this->_languages[$language_code]['parent_id']));
      }

      if ( is_null($filename) ) {
        if ( file_exists('includes/languages/' . $language_code . '.php') ) {
          $contents = file('includes/languages/' . $language_code . '.php');
        } else {
          return array();
        }
      } else {
        if ( substr(realpath('includes/languages/' . $language_code . '/' . $filename), 0, strlen(realpath('includes/languages/' . $language_code))) != realpath('includes/languages/' . $language_code) ) {
          return array();
        }

        if ( !file_exists('includes/languages/' . $language_code . '/' . $filename) ) {
          return array();
        }
        
        $contents = file('includes/languages/' . $language_code . '/' . $filename);
      }

      $ini_array = array();

      foreach ( $contents as $line ) {
        $line = trim($line);

        $firstchar = substr($line, 0, 1);

        if ( !empty($line) && ( $firstchar != $comment) ) {
          $delimiter = strpos($line, '=');

          if ( $delimiter !== false ) {
            $key = trim(substr($line, 0, $delimiter));
            $value = trim(substr($line, $delimiter + 1));

            $ini_array[$key] = $value;
          } elseif ( isset($key) ) {
            $ini_array[$key] .= trim($line);
          }
        }
      }

      unset($contents);

      return $ini_array;
    }

    function injectDefinitions($file, $language_code = null) {
      if ( is_null($language_code) ) {
        $language_code = $this->_code;
      }

      if ( $this->_languages[$language_code]['parent_id'] > 0 ) {
        $this->injectDefinitions($file, $this->getCodeFromID($this->_languages[$language_code]['parent_id']));
      }

      foreach ($this->extractDefinitions($language_code . '/' . $file) as $def) {
        $this->_definitions[$def['key']] = $def['value'];
      }
    }

    function &extractDefinitions($xml) {
      $definitions = array();

      if ( file_exists(dirname(__FILE__) . '/../../../includes/languages/' . $xml) ) {
        $osC_XML = new osC_XML(file_get_contents(dirname(__FILE__) . '/../../../includes/languages/' . $xml));

        $definitions = $osC_XML->toArray();

        if (isset($definitions['language']['definitions']['definition'][0]) === false) {
          $definitions['language']['definitions']['definition'] = array($definitions['language']['definitions']['definition']);
        }

        $definitions = $definitions['language']['definitions']['definition'];
      }

      return $definitions;
    }

    function export($id, $groups, $include_language_data = true) {
      global $osC_Database, $osC_Currencies;

      $language = osC_Language_Admin::getData($id);

      $export_array = array();

      if ( $include_language_data === true ) {
        $export_array['language']['data'] = array('title-CDATA' => $language['name'],
                                                  'code-CDATA' => $language['code'],
                                                  'locale-CDATA' => $language['locale'],
                                                  'character_set-CDATA' => $language['charset'],
                                                  'text_direction-CDATA' => $language['text_direction'],
                                                  'date_format_short-CDATA' => $language['date_format_short'],
                                                  'date_format_long-CDATA' => $language['date_format_long'],
                                                  'time_format-CDATA' => $language['time_format'],
                                                  'default_currency-CDATA' => $osC_Currencies->getCode($language['currencies_id']),
                                                  'numerical_decimal_separator-CDATA' => $language['numeric_separator_decimal'],
                                                  'numerical_thousands_separator-CDATA' => $language['numeric_separator_thousands']);

        if ( $language['parent_id'] > 0 ) {
          $export_array['language']['data']['parent_language_code'] = osC_Language_Admin::getCode($language['parent_id']);
        }
      }

      $Qdefs = $osC_Database->query('select content_group, definition_key, definition_value from :table_languages_definitions where languages_id = :languages_id and content_group in (":content_group") order by content_group, definition_key');
      $Qdefs->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qdefs->bindInt(':languages_id', $id);
      $Qdefs->bindRaw(':content_group', implode('", "', $groups));
      $Qdefs->execute();

      while ($Qdefs->next()) {
        $export_array['language']['definitions']['definition'][] = array('key' => $Qdefs->value('definition_key'),
                                                                         'value-CDATA' => $Qdefs->value('definition_value'),
                                                                         'group' => $Qdefs->value('content_group'));
      }

      $osC_XML = new osC_XML($export_array, $language['charset']);
      $xml = $osC_XML->toXML();

      header('Content-Description: File Transfer');
      header('Content-disposition: attachment; filename=' . $language['code'] . '.xml');
      header('Content-Type: text/xml');
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: ' . strlen($xml));
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');      

      ob_clean();
      flush();
      echo $xml;           
      exit;      
    }

    function import($file, $type) {
      global $osC_Database, $osC_Currencies;

      if (file_exists('../includes/languages/' . $file . '.xml')) {
        $osC_XML = new osC_XML(file_get_contents('../includes/languages/' . $file . '.xml'));
        $source = $osC_XML->toArray();

        $language = array('name' => $source['language']['data']['title'],
                          'code' => $source['language']['data']['code'],
                          'locale' => $source['language']['data']['locale'],
                          'charset' => $source['language']['data']['character_set'],
                          'date_format_short' => $source['language']['data']['date_format_short'],
                          'date_format_long' => $source['language']['data']['date_format_long'],
                          'time_format' => $source['language']['data']['time_format'],
                          'text_direction' => $source['language']['data']['text_direction'],
                          'currency' => $source['language']['data']['default_currency'],
                          'numeric_separator_decimal' => $source['language']['data']['numerical_decimal_separator'],
                          'numeric_separator_thousands' => $source['language']['data']['numerical_thousands_separator'],
                          'parent_language_code' => $source['language']['data']['parent_language_code'],
                          'parent_id' => 0
                         );

        if (!$osC_Currencies->exists($language['currency'])) {
          $language['currency'] = DEFAULT_CURRENCY;
        }

        if ( !empty($language['parent_language_code']) ) {
          $Qlanguage = $osC_Database->query('select languages_id from :table_languages where code = :code');
          $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
          $Qlanguage->bindValue(':code', $language['parent_language_code']);
          $Qlanguage->execute();

          if ( $Qlanguage->numberOfRows() === 1 ) {
            $language['parent_id'] = $Qlanguage->valueInt('languages_id');
          }
        }

        $definitions = array();

        if ( isset($source['language']['definitions']['definition']) ) {
          $definitions = $source['language']['definitions']['definition'];

          if ( isset($definitions['key']) && isset($definitions['value']) && isset($definitions['group']) ) {
            $definitions = array(array('key' => $definitions['key'],
                                       'value' => $definitions['value'],
                                       'group' => $definitions['group']));
          }
        }
        
        $tables = array();
        
        if ( isset($source['language']['tables']['table']) ) {
          $tables = $source['language']['tables']['table'];
        }

        unset($source);

        $error = false;
        $add_category_and_product_placeholders = true;

        $osC_Database->startTransaction();

        $Qcheck = $osC_Database->query('select languages_id from :table_languages where code = :code');
        $Qcheck->bindTable(':table_languages', TABLE_LANGUAGES);
        $Qcheck->bindValue(':code', $language['code']);
        $Qcheck->execute();

        if ($Qcheck->numberOfRows() === 1) {
          $add_category_and_product_placeholders = false;

          $language_id = $Qcheck->valueInt('languages_id');

          $Qlanguage = $osC_Database->query('update :table_languages set name = :name, code = :code, locale = :locale, charset = :charset, date_format_short = :date_format_short, date_format_long = :date_format_long, time_format = :time_format, text_direction = :text_direction, currencies_id = :currencies_id, numeric_separator_decimal = :numeric_separator_decimal, numeric_separator_thousands = :numeric_separator_thousands, parent_id = :parent_id where languages_id = :languages_id');
          $Qlanguage->bindInt(':languages_id', $language_id);
        } else {
          $Qlanguage = $osC_Database->query('insert into :table_languages (name, code, locale, charset, date_format_short, date_format_long, time_format, text_direction, currencies_id, numeric_separator_decimal, numeric_separator_thousands, parent_id) values (:name, :code, :locale, :charset, :date_format_short, :date_format_long, :time_format, :text_direction, :currencies_id, :numeric_separator_decimal, :numeric_separator_thousands, :parent_id)');
        }
        $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
        $Qlanguage->bindValue(':name', $language['name']);
        $Qlanguage->bindValue(':code', $language['code']);
        $Qlanguage->bindValue(':locale', $language['locale']);
        $Qlanguage->bindValue(':charset', $language['charset']);
        $Qlanguage->bindValue(':date_format_short', $language['date_format_short']);
        $Qlanguage->bindValue(':date_format_long', $language['date_format_long']);
        $Qlanguage->bindValue(':time_format', $language['time_format']);
        $Qlanguage->bindValue(':text_direction', $language['text_direction']);
        $Qlanguage->bindInt(':currencies_id', $osC_Currencies->getID($language['currency']));
        $Qlanguage->bindValue(':numeric_separator_decimal', $language['numeric_separator_decimal']);
        $Qlanguage->bindValue(':numeric_separator_thousands', $language['numeric_separator_thousands']);
        $Qlanguage->bindInt(':parent_id', $language['parent_id']);
        $Qlanguage->setLogging($_SESSION['module'], ($Qcheck->numberOfRows() === 1 ? $language_id : null));
        $Qlanguage->execute();

        if ($osC_Database->isError()) {
          $error = true;
        } else {
          if ($Qcheck->numberOfRows() !== 1) {
            $language_id = $osC_Database->nextID();
          }

          $default_language_id = osC_Language_Admin::getData(osC_Language_Admin::getID(DEFAULT_LANGUAGE), 'languages_id');

          if ($type == 'replace') {
            $Qdel =  $osC_Database->query('delete from :table_languages_definitions where languages_id = :languages_id');
            $Qdel->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
            $Qdel->bindInt(':languages_id', $language_id);
            $Qdel->execute();

            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }

        if ($error === false) {
          $osC_DirectoryListing = new osC_DirectoryListing('../includes/languages/' . $file);
          $osC_DirectoryListing->setRecursive(true);
          $osC_DirectoryListing->setIncludeDirectories(false);
          $osC_DirectoryListing->setAddDirectoryToFilename(true);
          $osC_DirectoryListing->setCheckExtension('xml');

          foreach ($osC_DirectoryListing->getFiles() as $files) {
            $definitions = array_merge($definitions, osC_Language_Admin::extractDefinitions($file . '/' . $files['name']));
          }

          foreach ($definitions as $def) {
            $insert = false;
            $update = false;

            if ($type == 'replace') {
              $insert = true;
            } else {
              $Qcheck = $osC_Database->query('select definition_key, content_group from :table_languages_definitions where definition_key = :definition_key and languages_id = :languages_id and content_group = :content_group');
              $Qcheck->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
              $Qcheck->bindValue(':definition_key', $def['key']);
              $Qcheck->bindInt(':languages_id', $language_id);
              $Qcheck->bindValue(':content_group', $def['group']);
              $Qcheck->execute();

              if ($Qcheck->numberOfRows() > 0) {
                if ($type == 'update') {
                  $update = true;
                }
              } elseif ($type == 'add') {
                $insert = true;
              }
            }

            if ( ($insert === true) || ($update === true) ) {
              if ($insert === true) {
                $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
              } else {
                $Qdef = $osC_Database->query('update :table_languages_definitions set content_group = :content_group, definition_key = :definition_key, definition_value = :definition_value where definition_key = :definition_key and languages_id = :languages_id and content_group = :content_group');
                $Qdef->bindValue(':definition_key', $def['key']);
                $Qdef->bindValue(':content_group', $def['group']);
              }
              $Qdef->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
              $Qdef->bindInt(':languages_id', $language_id);
              $Qdef->bindValue(':content_group', $def['group']);
              $Qdef->bindValue(':definition_key', $def['key']);
              $Qdef->bindValue(':definition_value', $def['value']);
              $Qdef->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }

        if ($add_category_and_product_placeholders === true) {
        
          if ( !empty($tables) ) {
            foreach( $tables as $table ){
              $table_name = str_replace('toc_', DB_TABLE_PREFIX, $table['meta']['name']);
              $key_field = $table['meta']['key_field'];
              $language_field = $table['meta']['language_field'];
              
              $Qcheck = $osC_Database->query('select * from :table_name where ' . $language_field . ' = :language_id');
              $Qcheck->bindTable(':table_name', $table_name);
              $Qcheck->bindInt(':language_id', $default_language_id);
              $Qcheck->execute();
              
              while ( $Qcheck->next() ) {
                $data = $Qcheck->toArray();
                $data[$language_field] = $language_id;
                $insert = false;
                
                foreach ($table['definition'] as $definition) {
                  if ($data[$key_field] == $definition['key']) {
                    $insert = true;
                    foreach($definition as $key => $value) {
                      if ( ($key != 'key') && array_key_exists($key, $data) ) {
                        $data[$key] = $osC_Database->escapeString($value);
                      }
                    }
                  }
                }
                
                if ($insert === true) {
                  $fields = array_keys($data);
                  $values = array();
                  foreach($fields as $field) {
                    $values[] = "'" . $data[$field] . "'";
                  }
                  
                  $Qinsert = $osC_Database->query('insert into :table_name (' . implode(', ', $fields) . ') values (' . implode(', ', $values) . ')');
                  $Qinsert->bindTable(':table_name', $table_name);
                  $Qinsert->execute();
                }
              }
            }
          }
          
          if ($error === false) {
            $Qcategories = $osC_Database->query('select categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description from :table_categories_description where language_id = :language_id');
            $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
            $Qcategories->bindInt(':language_id', $default_language_id);
            $Qcategories->execute();

            while ($Qcategories->next()) {
              $Qinsert = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
              $Qinsert->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
              $Qinsert->bindInt(':categories_id', $Qcategories->valueInt('categories_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':categories_name', $Qcategories->value('categories_name'));
              $Qinsert->bindValue(':categories_url', $Qcategories->value('categories_url'));
              $Qinsert->bindValue(':categories_page_title', $Qcategories->value('categories_page_title'));
              $Qinsert->bindValue(':categories_meta_keywords', $Qcategories->value('categories_meta_keywords'));
              $Qinsert->bindValue(':categories_meta_description', $Qcategories->value('categories_meta_description'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qproducts = $osC_Database->query('select products_id, products_name, products_description, products_keyword, products_tags, products_url, products_friendly_url, products_page_title, products_meta_keywords, products_meta_description, products_viewed from :table_products_description where language_id = :language_id');
            $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
            $Qproducts->bindInt(':language_id', $default_language_id);
            $Qproducts->execute();

            while ($Qproducts->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_description (products_id, language_id, products_name, products_description, products_keyword, products_tags, products_url, products_friendly_url, products_page_title, products_meta_keywords, products_meta_description, products_viewed) values (:products_id, :language_id, :products_name, :products_description, :products_keyword, :products_tags, :products_url, :products_friendly_url, :products_page_title, :products_meta_keywords, :products_meta_description, :products_viewed)');
              $Qinsert->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
              $Qinsert->bindInt(':products_id', $Qproducts->valueInt('products_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':products_name', $Qproducts->value('products_name'));
              $Qinsert->bindValue(':products_description', $Qproducts->value('products_description'));
              $Qinsert->bindValue(':products_keyword', $Qproducts->value('products_keyword'));
              $Qinsert->bindValue(':products_tags', $Qproducts->value('products_tags'));
              $Qinsert->bindValue(':products_url', $Qproducts->value('products_url'));
              $Qinsert->bindValue(':products_friendly_url', $Qproducts->value('products_friendly_url'));
              $Qinsert->bindValue(':products_page_title', $Qproducts->value('products_page_title'));
              $Qinsert->bindValue(':products_meta_keywords', $Qproducts->value('products_meta_keywords'));
              $Qinsert->bindValue(':products_meta_description', $Qproducts->value('products_meta_description'));
              $Qinsert->bindInt(':products_viewed', $Qproducts->valueInt('products_viewed'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $QcustomizationFields = $osC_Database->query('select customization_fields_id, languages_id, name from :table_customization_fields_description where languages_id = :languages_id');
            $QcustomizationFields->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
            $QcustomizationFields->bindInt(':languages_id', $default_language_id);
            $QcustomizationFields->execute();

            while ($QcustomizationFields->next()) {
              $Qinsert = $osC_Database->query('insert into :table_customization_fields_description (customization_fields_id, languages_id, name) values (:customization_fields_id, :languages_id, :name)');
              $Qinsert->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
              $Qinsert->bindInt(':customization_fields_id', $QcustomizationFields->valueInt('customization_fields_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':name', $QcustomizationFields->value('name'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
          
          if ($error === false) {
            $Qvariants = $osC_Database->query('select products_variants_groups_id, products_variants_groups_name from :table_products_variants_groups where language_id = :language_id');
            $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
            $Qvariants->bindInt(':language_id', $default_language_id);
            $Qvariants->execute();

            while ($Qvariants->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_variants_groups (products_variants_groups_id, language_id, products_variants_groups_name) values (:products_variants_groups_id, :language_id, :products_variants_groups_name)');
              $Qinsert->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
              $Qinsert->bindInt(':products_variants_groups_id', $Qvariants->valueInt('products_variants_groups_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':products_variants_groups_name', $Qvariants->value('products_variants_groups_name'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qvalues = $osC_Database->query('select products_variants_values_id, products_variants_values_name from :table_products_variants_values where language_id = :language_id');
            $Qvalues->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
            $Qvalues->bindInt(':language_id', $default_language_id);
            $Qvalues->execute();

            while ($Qvalues->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_variants_values (products_variants_values_id, language_id, products_variants_values_name) values (:products_variants_values_id, :language_id, :products_variants_values_name)');
              $Qinsert->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
              $Qinsert->bindInt(':products_variants_values_id', $Qvalues->valueInt('products_variants_values_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':products_variants_values_name', $Qvalues->value('products_variants_values_name'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qmanufacturers = $osC_Database->query('select manufacturers_id, manufacturers_url, manufacturers_friendly_url from :table_manufacturers_info where languages_id = :languages_id');
            $Qmanufacturers->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
            $Qmanufacturers->bindInt(':languages_id', $default_language_id);
            $Qmanufacturers->execute();

            while ($Qmanufacturers->next()) {
              $Qinsert = $osC_Database->query('insert into :table_manufacturers_info (manufacturers_id, languages_id, manufacturers_url, manufacturers_friendly_url) values (:manufacturers_id, :languages_id, :manufacturers_url, :manufacturers_friendly_url)');
              $Qinsert->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
              $Qinsert->bindInt(':manufacturers_id', $Qmanufacturers->valueInt('manufacturers_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':manufacturers_url', $Qmanufacturers->value('manufacturers_url'));
              $Qinsert->bindValue(':manufacturers_friendly_url', $Qmanufacturers->value('manufacturers_friendly_url'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qimages = $osC_Database->query('select image_id, description, image, image_url, sort_order, status from :table_slide_images where language_id = :language_id');
            $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
            $Qimages->bindInt(':language_id', $default_language_id);
            $Qimages->execute();

            while ($Qimages->next()) {
              $Qinsert = $osC_Database->query('insert into :table_slide_images (image_id, language_id, description, image, image_url, sort_order, status) values (:image_id, :language_id, :description, :image, :image_url, :sort_order, :status)');
              $Qinsert->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
              $Qinsert->bindInt(':image_id', $Qimages->valueInt('image_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':description', $Qimages->value('description'));
              $Qinsert->bindValue(':image', $Qimages->value('image'));
              $Qinsert->bindValue(':image_url', $Qimages->value('image_url'));
              $Qinsert->bindInt(':sort_order', $Qimages->valueInt('sort_order'));
              $Qinsert->bindInt(':status', $Qimages->valueInt('status'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
          
          if ($error === false) {
            $Qattributes = $osC_Database->query('select products_attributes_values_id, products_attributes_groups_id, name, module, value, status, sort_order from :table_products_attributes_values where language_id = :language_id');
            $Qattributes->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
            $Qattributes->bindInt(':language_id', $default_language_id);
            $Qattributes->execute();

            while ($Qattributes->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_attributes_values (products_attributes_values_id, products_attributes_groups_id, language_id, name, module, value, status, sort_order) values (:products_attributes_values_id, :products_attributes_groups_id, :language_id, :name, :module, :value, :status, :sort_order)');
              $Qinsert->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
              $Qinsert->bindInt(':products_attributes_values_id', $Qattributes->valueInt('products_attributes_values_id'));
              $Qinsert->bindInt(':products_attributes_groups_id', $Qattributes->valueInt('products_attributes_groups_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':name', $Qattributes->value('name'));
              $Qinsert->bindValue(':module', $Qattributes->value('module'));
              $Qinsert->bindValue(':value', $Qattributes->value('value'));
              $Qinsert->bindInt(':status', $Qattributes->valueInt('status'));
              $Qinsert->bindInt(':sort_order', $Qattributes->valueInt('sort_order'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qattributes = $osC_Database->query('select products_id, products_attributes_values_id, value from :table_products_attributes where language_id = :language_id');
            $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
            $Qattributes->bindInt(':language_id', $default_language_id);
            $Qattributes->execute();

            while ($Qattributes->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_attributes (products_id, products_attributes_values_id, value, language_id) values (:products_id, :products_attributes_values_id, :value, :language_id)');
              $Qinsert->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
              $Qinsert->bindInt(':products_id', $Qattributes->valueInt('products_id'));
              $Qinsert->bindInt(':products_attributes_values_id', $Qattributes->valueInt('products_attributes_values_id'));
              $Qinsert->bindValue(':value', $Qattributes->value('value'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }          

          if ($error === false) {
            $Qfaqs = $osC_Database->query('select faqs_id, faqs_question, faqs_url, faqs_answer from :table_faqs_description where language_id = :language_id');
            $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
            $Qfaqs->bindInt(':language_id', $default_language_id);
            $Qfaqs->execute();

            while ($Qfaqs->next()) {
              $Qinsert = $osC_Database->query('insert into :table_faqs_description (faqs_id, language_id, faqs_question, faqs_answer, faqs_url) values (:faqs_id, :language_id, :faqs_question, :faqs_answer, :faqs_url)');
              $Qinsert->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
              $Qinsert->bindInt(':faqs_id', $Qfaqs->valueInt('faqs_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':faqs_question', $Qfaqs->value('faqs_question'));
              $Qinsert->bindValue(':faqs_answer', $Qfaqs->value('faqs_answer'));
              $Qinsert->bindValue(':faqs_url', $Qfaqs->value('faqs_url'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        
          if ($error === false) {
            $Qcoupons = $osC_Database->query('select coupons_id, coupons_name, coupons_description from :table_coupons_description where language_id = :language_id');
            $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
            $Qcoupons->bindInt(':language_id', $default_language_id);
            $Qcoupons->execute();

            while ($Qcoupons->next()) {
              $Qinsert = $osC_Database->query('insert into :table_coupons_description (coupons_id, language_id, coupons_name, coupons_description) values (:coupons_id, :language_id, :coupons_name, :coupons_description)');
              $Qinsert->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
              $Qinsert->bindInt(':coupons_id', $Qcoupons->valueInt('coupons_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':coupons_name', $Qcoupons->value('coupons_name'));
              $Qinsert->bindValue(':coupons_description', $Qcoupons->value('coupons_description'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qarticles = $osC_Database->query('select articles_id, articles_name, articles_description, articles_url, articles_page_title, articles_meta_keywords, articles_meta_description from :table_articles_description where language_id = :language_id');
            $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
            $Qarticles->bindInt(':language_id', $default_language_id);
            $Qarticles->execute();

            while ($Qarticles->next()) {
              $Qcheck = $osC_Database->query('select * from :table_articles_description where articles_id = :articles_id and language_id = :language_id');
              $Qcheck->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
              $Qcheck->bindInt(':articles_id', $Qarticles->valueInt('articles_id'));
              $Qcheck->bindInt(':language_id', $language_id);
              $Qcheck->execute();
              
              if ($Qcheck->numberOfRows() === 0) {
                $Qinsert = $osC_Database->query('insert into :table_articles_description (articles_id, language_id, articles_name, articles_description, articles_url, articles_page_title, articles_meta_keywords, articles_meta_description) values (:articles_id, :language_id, :articles_name, :articles_description, :articles_url, :articles_page_title, :articles_meta_keywords, :articles_meta_description)');
                $Qinsert->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
                $Qinsert->bindInt(':articles_id', $Qarticles->valueInt('articles_id'));
                $Qinsert->bindInt(':language_id', $language_id);
                $Qinsert->bindValue(':articles_name', $Qarticles->value('articles_name'));
                $Qinsert->bindValue(':articles_description', $Qarticles->value('articles_description'));
                $Qinsert->bindValue(':articles_url', $Qarticles->value('articles_url'));
                $Qinsert->bindValue(':articles_page_title', $Qarticles->value('articles_page_title'));
                $Qinsert->bindValue(':articles_meta_keywords', $Qarticles->value('articles_meta_keywords'));
                $Qinsert->bindValue(':articles_meta_description', $Qarticles->value('articles_meta_description'));
                $Qinsert->execute();
  
                if ($osC_Database->isError()) {
                  $error = true;
                  break;
                }
              }
            }
          }
          
          if ($error === false) {
            $Qcategories = $osC_Database->query('select articles_categories_id, articles_categories_name, articles_categories_url, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description from :table_articles_categories_description where language_id = :language_id');
            $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
            $Qcategories->bindInt(':language_id', $default_language_id);
            $Qcategories->execute();

            while ($Qcategories->next()) {
              $Qinsert = $osC_Database->query('insert into :table_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description) values (:articles_categories_id, :language_id, :articles_categories_name, :articles_categories_url, :articles_categories_page_title, :articles_categories_meta_keywords, :articles_categories_meta_description )');
              $Qinsert->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
              $Qinsert->bindInt(':articles_categories_id', $Qcategories->valueInt('articles_categories_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':articles_categories_name', $Qcategories->value('articles_categories_name'));
              $Qinsert->bindValue(':articles_categories_url', $Qcategories->value('articles_categories_url'));
              $Qinsert->bindValue(':articles_categories_page_title', $Qcategories->value('articles_categories_page_title'));
              $Qinsert->bindValue(':articles_categories_meta_keywords', $Qcategories->value('articles_categories_meta_keywords'));
              $Qinsert->bindValue(':articles_categories_meta_description', $Qcategories->value('articles_categories_meta_description'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        
         if ($error === false) {
            $Qgroups = $osC_Database->query('select customers_groups_id, customers_groups_name from :table_customers_groups_description where language_id = :language_id');
            $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
            $Qgroups->bindInt(':language_id', $default_language_id);
            $Qgroups->execute();

            while ($Qgroups->next()) {
              $Qinsert = $osC_Database->query('insert into :table_customers_groups_description (customers_groups_id, language_id, customers_groups_name) values (:customers_groups_id, :language_id, :customers_groups_name)');
              $Qinsert->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
              $Qinsert->bindInt(':customers_groups_id', $Qgroups->valueInt('customers_groups_id'));
              $Qinsert->bindInt(':language_id', $language_id);
              $Qinsert->bindValue(':customers_groups_name', $Qgroups->value('customers_groups_name'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
                
         if ($error === false) {
            $Qratings = $osC_Database->query('select ratings_id, ratings_text from :table_ratings_description where languages_id = :languages_id');
            $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
            $Qratings->bindInt(':languages_id', $default_language_id);
            $Qratings->execute();

            while ($Qratings->next()) {
              $Qinsert = $osC_Database->query('insert into :table_ratings_description (ratings_id, languages_id, ratings_text) values (:ratings_id, :languages_id, :ratings_text)');
              $Qinsert->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
              $Qinsert->bindInt(':ratings_id', $Qratings->valueInt('ratings_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':ratings_text', $Qratings->value('ratings_text'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }

          if ($error === false) {
            $Qpolls = $osC_Database->query('select polls_id, polls_title from :table_polls_description where languages_id = :languages_id');
            $Qpolls->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
            $Qpolls->bindInt(':languages_id', $default_language_id);
            $Qpolls->execute();

            while ($Qpolls->next()) {
              $Qinsert = $osC_Database->query('insert into :table_polls_description (polls_id, languages_id, polls_title) values (:polls_id, :languages_id, :polls_title)');
              $Qinsert->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
              $Qinsert->bindInt(':polls_id', $Qpolls->valueInt('polls_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':polls_title', $Qpolls->value('polls_title'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
          
          if ($error === false) {
            $Qanswers = $osC_Database->query('select polls_answers_id, answers_title from :table_polls_answers_description where languages_id = :languages_id');
            $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
            $Qanswers->bindInt(':languages_id', $default_language_id);
            $Qanswers->execute();

            while ($Qanswers->next()) {
              $Qinsert = $osC_Database->query('insert into :table_polls_answers_description (polls_answers_id, languages_id, answers_title) values (:polls_answers_id, :languages_id, :answers_title)');
              $Qinsert->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
              $Qinsert->bindInt(':polls_answers_id', $Qanswers->valueInt('polls_answers_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':answers_title', $Qanswers->value('answers_title'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        
          if ($error === false) {
            $Qattachments = $osC_Database->query('select attachments_id, attachments_name, attachments_description from :table_products_attachments_description where languages_id = :language_id');
            $Qattachments->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
            $Qattachments->bindInt(':language_id', $default_language_id);
            $Qattachments->execute();

            while ($Qattachments->next()) {
              $Qinsert = $osC_Database->query('insert into :table_products_attachments_description (attachments_id, languages_id, attachments_name, attachments_description) values (:attachments_id, :languages_id, :attachments_name, :attachments_description)');
              $Qinsert->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
              $Qinsert->bindInt(':attachments_id', $Qattachments->valueInt('attachments_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':attachments_name', $Qattachments->value('attachments_name'));
              $Qinsert->bindValue(':attachments_description', $Qattachments->value('attachments_description'));
              $Qinsert->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
          
          if ( $error === false ) {
            $Qdepart = $osC_Database->query('select departments_id, departments_title, departments_description from :table_departments_description where languages_id = :language_id');
            $Qdepart->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
            $Qdepart->bindInt(':language_id', $default_language_id);
            $Qdepart->execute();

            while ($Qdepart->next()) {
              $Qinsert = $osC_Database->query('insert into :table_departments_description (departments_id, languages_id, departments_title, departments_description) values (:departments_id, :languages_id, :departments_title, :departments_description)');
              $Qinsert->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
              $Qinsert->bindInt(':departments_id', $Qdepart->valueInt('departments_id'));
              $Qinsert->bindInt(':languages_id', $language_id);
              $Qinsert->bindValue(':departments_title', $Qdepart->value('departments_title'));
              $Qinsert->bindValue(':departments_description', $Qdepart->value('departments_description'));
              $Qinsert->execute();

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

        osC_Cache::clear('languages');

        return true;
      } else {
        $osC_Database->rollbackTransaction();
      }

      return false;
    }

    function getData($id, $key = null) {
      global $osC_Database;

      $Qlanguage = $osC_Database->query('select * from :table_languages where languages_id = :languages_id');
      $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qlanguage->bindInt(':languages_id', $id);
      $Qlanguage->execute();

      $result = $Qlanguage->toArray();

      $Qlanguage->freeResult();

      if ( empty($key) ) {
        return $result;
      } else {
        return $result[$key];
      }
    }

    function getID($code = null) {
      global $osC_Database;

      if ( empty($code) ) {
        return $this->_languages[$this->_code]['id'];
      }

      $Qlanguage = $osC_Database->query('select languages_id from :table_languages where code = :code');
      $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qlanguage->bindValue(':code', $code);
      $Qlanguage->execute();

      $result = $Qlanguage->toArray();

      $Qlanguage->freeResult();

      return $result['languages_id'];
    }

    function getCode($id = null) {
      global $osC_Database;

      if ( empty($id) ) {
        return $this->_code;
      }

      $Qlanguage = $osC_Database->query('select code from :table_languages where languages_id = :languages_id');
      $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qlanguage->bindValue(':languages_id', $id);
      $Qlanguage->execute();

      $result = $Qlanguage->toArray();

      $Qlanguage->freeResult();

      return $result['code'];
    }

    function update($id, $language, $default = false) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qlanguage = $osC_Database->query('update :table_languages set name = :name, code = :code, locale = :locale, charset = :charset, date_format_short = :date_format_short, date_format_long = :date_format_long, time_format = :time_format, text_direction = :text_direction, currencies_id = :currencies_id, numeric_separator_decimal = :numeric_separator_decimal, numeric_separator_thousands = :numeric_separator_thousands, parent_id = :parent_id, sort_order = :sort_order where languages_id = :languages_id');
      $Qlanguage->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qlanguage->bindValue(':name', $language['name']);
      $Qlanguage->bindValue(':code', $language['code']);
      $Qlanguage->bindValue(':locale', $language['locale']);
      $Qlanguage->bindValue(':charset', $language['charset']);
      $Qlanguage->bindValue(':date_format_short', $language['date_format_short']);
      $Qlanguage->bindValue(':date_format_long', $language['date_format_long']);
      $Qlanguage->bindValue(':time_format', $language['time_format']);
      $Qlanguage->bindValue(':text_direction', $language['text_direction']);
      $Qlanguage->bindInt(':currencies_id', $language['currencies_id']);
      $Qlanguage->bindValue(':numeric_separator_decimal', $language['numeric_separator_decimal']);
      $Qlanguage->bindValue(':numeric_separator_thousands', $language['numeric_separator_thousands']);
      $Qlanguage->bindInt(':parent_id', $language['parent_id']);
      $Qlanguage->bindInt(':sort_order', $language['sort_order']);
      $Qlanguage->bindInt(':languages_id', $id);
      $Qlanguage->setLogging($_SESSION['module'], $id);
      $Qlanguage->execute();

      if ($osC_Database->isError()) {
        $error = true;
      }

      if ($error === false) {
        if ($default === true) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindValue(':configuration_value', $language['code']);
          $Qupdate->bindValue(':configuration_key', 'DEFAULT_LANGUAGE');
          $Qupdate->setLogging($_SESSION['module'], $id);
          $Qupdate->execute();

          if ($osC_Database->isError() === false) {
            if ($Qupdate->affectedRows()) {
              osC_Cache::clear('configuration');
            }
          } else {
            $error = true;
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('languages');

        return true;
      } else {
        $osC_Database->rollbackTransaction();
      }

      return false;
    }

    function saveDefinition($id, $group, $key, $value) {
      global $osC_Database;

      $Qupdate = $osC_Database->query('update :table_languages_definitions set definition_value = :definition_value where definition_key = :definition_key and languages_id = :languages_id and content_group = :content_group');
      $Qupdate->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qupdate->bindValue(':definition_value', $value);
      $Qupdate->bindValue(':definition_key', $key);
      $Qupdate->bindInt(':languages_id', $id);
      $Qupdate->bindValue(':content_group', $group);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();

      if (!$osC_Database->isError()) {
        osC_Cache::clear('languages-' . osC_Language_Admin::getData($id, 'code') . '-' . $group);
        
        return true;
      }

      return false;
    }
    
    function addDefinition($data) {
      global $osC_Database;

      $Qinsert = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
      $Qinsert->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qinsert->bindValue(':definition_value', $data['definition_value']);
      $Qinsert->bindValue(':definition_key', $data['definition_key']);
      $Qinsert->bindInt(':languages_id', $data['languages_id']);
      $Qinsert->bindValue(':content_group', $data['definition_group']);
      $Qinsert->setLogging($_SESSION['module'], $data['languages_id']);
      $Qinsert->execute();

      if (!$osC_Database->isError()) {
        osC_Cache::clear('languages-' . osC_Language_Admin::getData($data['languages_id'], 'code') . '-' . $data['definition_group']);
        
        return true;
      }

      return false;
    }
    
    function deleteDefinition($id) {
      global $osC_Database;

      $Qdefs = $osC_Database->query('select languages_id, content_group from :table_languages_definitions where id = :languages_definitions_id');
      $Qdefs->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qdefs->bindInt(':languages_definitions_id', $id);
      $Qdefs->execute();
      
      $languages_id = $Qdefs->valueInt('languages_id');
      $group = $Qdefs->value('content_group');
      
      $Qdelete = $osC_Database->query('delete from :table_languages_definitions where id = :languages_definitions_id');
      $Qdelete->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qdelete->bindInt(':languages_definitions_id', $id);
      $Qdelete->setLogging($_SESSION['module'], $id);
      $Qdelete->execute();
      
      if (!$osC_Database->isError()) {
        osC_Cache::clear('languages-' . osC_Language_Admin::getData($languages_id, 'code') . '-' . $group);
        
        return true;
      }

      return false;
    }
    
    function saveDefinitions($id, $group, $data) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      foreach ($data as $key => $value) {
        $Qupdate = $osC_Database->query('update :table_languages_definitions set definition_value = :definition_value where definition_key = :definition_key and languages_id = :languages_id and content_group = :content_group');
        $Qupdate->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
        $Qupdate->bindValue(':definition_value', $value);
        $Qupdate->bindValue(':definition_key', $key);
        $Qupdate->bindInt(':languages_id', $id);
        $Qupdate->bindValue(':content_group', $group);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();

        if ($osC_Database->isError()) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('languages-' . osC_Language_Admin::getData($id, 'code') . '-' . $group);

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function insertDefinition($group, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      foreach ($osC_Language->getAll() as $l) {
        $Qdefinition = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
        $Qdefinition->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
        $Qdefinition->bindInt(':languages_id', $l['id']);
        $Qdefinition->bindValue(':content_group', $group);
        $Qdefinition->bindValue(':definition_key', $data['key']);
        $Qdefinition->bindValue(':definition_value', $data['value'][$l['id']]);
        $Qdefinition->setLogging($_SESSION['module']);
        $Qdefinition->execute();

        if ($osC_Database->isError()) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('languages-' . osC_Language_Admin::getData($id, 'code') . '-' . $group);

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function remove($id, $del_files = false) {
      global $osC_Database, $osC_Language;

      $Qcheck = $osC_Database->query('select code from :table_languages where languages_id = :languages_id');
      $Qcheck->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qcheck->bindInt(':languages_id', $id);
      $Qcheck->execute();

      if ($Qcheck->value('code') != DEFAULT_LANGUAGE) {
        $error = false;

        $osC_Database->startTransaction();

        $Qcategories = $osC_Database->query('delete from :table_categories_description where language_id = :language_id');
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $id);
        $Qcategories->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
        
        if ($error === false) {
          $Qcustomization = $osC_Database->query('delete from :table_customization_fields_description where languages_id = :languages_id');
          $Qcustomization->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
          $Qcustomization->bindInt(':languages_id', $id);
          $Qcustomization->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qproducts = $osC_Database->query('delete from :table_products_description where language_id = :language_id');
          $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qproducts->bindInt(':language_id', $id);
          $Qproducts->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
              
        if ($error === false) {
          $QcustomizationFields = $osC_Database->query('delete from :table_customization_fields_description where languages_id = :languages_id');
          $QcustomizationFields->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
          $QcustomizationFields->bindInt(':languages_id', $id);
          $QcustomizationFields->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qproducts = $osC_Database->query('delete from :table_products_variants_groups where language_id = :language_id');
          $Qproducts->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
          $Qproducts->bindInt(':language_id', $id);
          $Qproducts->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qproducts = $osC_Database->query('delete from :table_products_variants_values where language_id = :language_id');
          $Qproducts->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
          $Qproducts->bindInt(':language_id', $id);
          $Qproducts->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qmanufacturers = $osC_Database->query('delete from :table_manufacturers_info where languages_id = :languages_id');
          $Qmanufacturers->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
          $Qmanufacturers->bindInt(':languages_id', $id);
          $Qmanufacturers->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qstatus = $osC_Database->query('delete from :table_orders_status where language_id = :language_id');
          $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
          $Qstatus->bindInt(':language_id', $id);
          $Qstatus->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qreturns = $osC_Database->query('delete from :table_orders_returns_status where languages_id = :language_id');
          $Qreturns->bindTable(':table_orders_returns_status', TABLE_ORDERS_RETURNS_STATUS);
          $Qreturns->bindInt(':language_id', $id);
          $Qreturns->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qreturns = $osC_Database->query('delete from :table_orders_transactions_status where language_id = :language_id');
          $Qreturns->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
          $Qreturns->bindInt(':language_id', $id);
          $Qreturns->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qgroup = $osC_Database->query('delete from :table_products_images_groups where language_id = :language_id');
          $Qgroup->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
          $Qgroup->bindInt(':language_id', $id);
          $Qgroup->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qclasses = $osC_Database->query('delete from :table_quantity_unit_classes where language_id = :language_id');
          $Qclasses->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
          $Qclasses->bindInt(':language_id', $id);
          $Qclasses->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qclasses = $osC_Database->query('delete from :table_weight_classes where language_id = :language_id');
          $Qclasses->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
          $Qclasses->bindInt(':language_id', $id);
          $Qclasses->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }        
      
        if ($error === false) {
          $Qarticles = $osC_Database->query('delete from :table_articles_description where language_id = :language_id');
          $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
          $Qarticles->bindInt(':language_id', $id);
          $Qarticles->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
            
        if ($error === false) {
          $Qcategories = $osC_Database->query('delete from :table_articles_categories_description where language_id = :language_id');
          $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
          $Qcategories->bindInt(':language_id', $id);
          $Qcategories->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
                  
        if ($error === false) {
          $Qcoupons = $osC_Database->query('delete from :table_coupons_description where language_id = :language_id');
          $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
          $Qcoupons->bindInt(':language_id', $id);
          $Qcoupons->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
                        
        if ($error === false) {
          $Qgroups = $osC_Database->query('delete from :table_customers_groups_description where language_id = :language_id');
          $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
          $Qgroups->bindInt(':language_id', $id);
          $Qgroups->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
                              
        if ($error === false) {
          $Qtemplates = $osC_Database->query('delete from :table_email_templates_description where language_id = :language_id');
          $Qtemplates->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
          $Qtemplates->bindInt(':language_id', $id);
          $Qtemplates->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
                                    
        if ($error === false) {
          $Qfaqs = $osC_Database->query('delete from :table_faqs_description where language_id = :language_id');
          $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
          $Qfaqs->bindInt(':language_id', $id);
          $Qfaqs->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
                                          
        if ($error === false) {
          $Qattributes = $osC_Database->query('delete from :table_products_attributes_values where language_id = :language_id');
          $Qattributes->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
          $Qattributes->bindInt(':language_id', $id);
          $Qattributes->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qattributes = $osC_Database->query('delete from :table_products_attributes where language_id = :language_id');
          $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qattributes->bindInt(':language_id', $id);
          $Qattributes->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }        
                             
        if ($error === false) {
          $Qimages = $osC_Database->query('delete from :table_slide_images where language_id = :language_id');
          $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
          $Qimages->bindInt(':language_id', $id);
          $Qimages->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qlanguages = $osC_Database->query('delete from :table_languages where languages_id = :language_id');
          $Qlanguages->bindTable(':table_languages', TABLE_LANGUAGES);
          $Qlanguages->bindInt(':language_id', $id);
          $Qlanguages->setLogging($_SESSION['module'], $id);
          $Qlanguages->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qdefinitions = $osC_Database->query('delete from :table_languages_definitions where languages_id = :languages_id');
          $Qdefinitions->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
          $Qdefinitions->bindInt(':languages_id', $id);
          $Qdefinitions->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
              
        if ($error === false) {
          $Qratings = $osC_Database->query('delete from :table_ratings_description where languages_id = :languages_id');
          $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
          $Qratings->bindInt(':languages_id', $id);
          $Qratings->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qpolls = $osC_Database->query('delete from :table_polls_description where languages_id = :languages_id');
          $Qpolls->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
          $Qpolls->bindInt(':languages_id', $id);
          $Qpolls->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qanswers = $osC_Database->query('delete from :table_polls_answers_description where languages_id = :languages_id');
          $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
          $Qanswers->bindInt(':languages_id', $id);
          $Qanswers->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qattachments = $osC_Database->query('delete from :table_products_attachments_description where languages_id = :languages_id');
          $Qattachments->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
          $Qattachments->bindInt(':languages_id', $id);
          $Qattachments->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qdepartment = $osC_Database->query('delete from :table_departments_description where languages_id = :languages_id');
          $Qdepartment->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
          $Qdepartment->bindInt(':languages_id', $id);
          $Qdepartment->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $osC_Database->commitTransaction();

          osC_Cache::clear('languages');
          
          //delete language files too
          if ($del_files === true) {
          	$lang_admin_path = DIR_FS_CATALOG . DIR_FS_ADMIN . 'includes/languages/';
          	$lang_front_path = DIR_FS_CATALOG . 'includes/languages/';
          	$lang_install_path = DIR_FS_CATALOG . 'install/includes/languages/';
          	$lang_code = $Qcheck->value('code');
          	
          	if (file_exists($lang_admin_path .  $lang_code . '.php')) {
          		@unlink($lang_admin_path . $lang_code . '.php');
          	}
          	
          	if (is_dir($lang_admin_path .  $lang_code)) {
          		osc_remove($lang_admin_path .  $lang_code);
          	}
          	
          	if (file_exists($lang_front_path .  $lang_code . '.xml')) {
          		@unlink($lang_front_path . $lang_code . '.xml');
          	}
          	
          	if (is_dir($lang_front_path .  $lang_code)) {
          		osc_remove($lang_front_path .  $lang_code);
          	}
          	
          	if (file_exists($lang_install_path .  $lang_code . '.php')) {
          		@unlink($lang_install_path . $lang_code . '.php');
          	}
          	 
          	if (is_dir($lang_install_path .  $lang_code)) {
          		osc_remove($lang_install_path .  $lang_code);
          	}
          }

          return true;
        } else {
          $osC_Database->rollbackTransaction();
        }
      }

      return false;
    }

    function deleteDefinitions($language_id, $group, $keys) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      foreach ($keys as $id) {
        $Qdel = $osC_Database->query('delete from :table_languages_definitions where id = :id');
        $Qdel->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
        $Qdel->bindValue(':id', $id);
        $Qdel->setLogging($_SESSION['module'], $id);
        $Qdel->execute();

        if ($osC_Database->isError()) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('languages-' . osC_Language_Admin::getData($language_id, 'code') . '-' . $group);

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function showImage($code = null, $width = '16', $height = '10', $parameters = null) {
      if ( empty($code) ) {
        $code = $this->_code;
      }

      $image_code = strtolower(substr($code, 3));

      if ( !is_numeric($width) ) {
        $width = 16;
      }

      if ( !is_numeric($height) ) {
        $height = 10;
      }

      return osc_image('../images/worldflags/' . $image_code . '.png', $this->_languages[$code]['name'], $width, $height, $parameters);
    }

    function isDefined($key) {
      return isset($this->_definitions[$key]);
    }
    
    function set($code = '') {
      $this->_code = $code;

      if (empty($this->_code)) {
        if (isset($_SESSION['admin_language'])) {
          $this->_code = $_SESSION['admin_language'];
        } elseif (isset($_COOKIE['admin_language'])) {
          $this->_code = $_COOKIE['admin_language'];
        } else {
          $this->_code = $this->getBrowserSetting();
        }
      }

      if (empty($this->_code) || ($this->exists($this->_code) === false)) {
        $this->_code = DEFAULT_LANGUAGE;
      }

      if (!isset($_COOKIE['admin_language']) || (isset($_COOKIE['admin_language']) && ($_COOKIE['admin_language'] != $this->_code))) {
        osc_setcookie('admin_language', $this->_code, time()+60*60*24*90);
      }

      if ((isset($_SESSION['admin_language']) === false) || (isset($_SESSION['admin_language']) && ($_SESSION['admin_language'] != $this->_code))) {
        $_SESSION['admin_language'] = $this->_code;
      }
    }
  }
?>
