<?php
/*
  $Id: import.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/image.php');

  class toC_Importer{
    var $_csv_delimiter = '',
        $_csv_enclosure = '',
        $_csv_line_length = 1000,
        $_type = '',
        $_tmp_file;

    function toC_Importer($parameters){
      $this->_csv_delimiter = $parameters['csv_field_seperator'];
      $this->_csv_enclosure = $parameters['csv_field_enclosed'];
      $this->_csv_line_length = $parameters['csv_line_length'];
      $this->_file_type = strtolower($parameters['file_type']);
      $this->_compression_type = strtolower($parameters['compression_type']);
      $this->_type = strtolower($parameters['type']);
      $this->_filename = $parameters['filename'];
      $this->_image_file = $parameters['image_file'];
    }

    function getImporter($parameters) {
      if ($parameters['type'] == 'customers') {
        return new osC_Customers_Importer($parameters);
      }else if($parameters['type'] == 'products'){
        return new osC_Products_Importer($parameters);
      }
    }

    function parse(){
      $temp_file = new upload($this->_filename, DIR_FS_CACHE);
      
      if ( $temp_file->exists() && $temp_file->parse() && $temp_file->save() ) {
      	$this->_filename = $temp_file;
      }
      
      if ($this->_compression_type == 'zip') {
      	require_once('../ext/zip/pclzip.lib.php');
      	$archive = new PclZip($this->_filename->destination . $this->_filename->filename);
      	
      	if ($archive->extract(PCLZIP_OPT_PATH, DIR_FS_CACHE) == 0) {
        	return false;
        } else {
        	$file = $archive->extractByIndex(0);
        	@unlink($this->_filename->destination . $this->_filename->filename);
          $this->_filename = DIR_FS_CACHE . $file[0]['stored_filename'];
        }
      } else {
	      $this->_filename = $this->_filename->destination . $this->_filename->filename;
      }
      
      
      switch ($this->_file_type) {
        case 'csv': return $this->parseCsvFile();
        case 'xml': return $this->parseXmlFile();
      }
    }

    function insertData($table_name, $data) {
      global $osC_Database;

      $fields = array_keys($data);
      $values = array_values($data);

      $fields = implode(',', $fields);
      for ($i = 0; $i < sizeof($values); $i++) {
        if( !( $values[$i] == 'now()' || $values[$i] == 'NULL' ) )
          $values[$i] = "'" . $osC_Database->parseString($values[$i]) . "'";
      }
      $values = implode(',', $values);


      $Qinsert = $osC_Database->query('insert into ' . $table_name . ' (' . $fields . ') values (' . $values . ')');
      $Qinsert->setLogging($_SESSION['module']);
      $Qinsert->execute();

      $insert_id = $osC_Database->nextID();

      return $insert_id;
    }
  }

  class osC_Customers_Importer extends toC_Importer{
    function osC_Customer_Importer($parameters){
      parent::toC_Importer($parameters);
    }

    function getCountryId($countries_name){
      global $osC_Database;

      $Qcountry = $osC_Database->query('select countries_id from :table_countries where countries_name = :countries_name');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindValue(':countries_name', $countries_name);
      $Qcountry->execute();

      if ($Qcountry->numberOfRows() == 1) {
        $countries_id = $Qcountry->valueInt('countries_id');
      } else {
        $countries_id = 0;
      }

      return $countries_id;
    }

    function insertCustomer($customer, $address_books){
      global $osC_Database;

      $customers_id = toC_Importer::insertData(TABLE_CUSTOMERS, $customer);

      if(isset($customers_id) && $customers_id != ''){
        $default_address_id = null;
        foreach($address_books as $address){
          $address['customers_id'] = $customers_id;
          $address['entry_country_id'] = osC_Customers_Importer::getCountryId($address['entry_country']);
          unset($address['entry_country']);

          $address_book_id = toC_Importer::insertData(TABLE_ADDRESS_BOOK, $address);

          if($default_address_id == null) {
            $default_address_id = $address_book_id;

            $Qaddress = $osC_Database->query('update :table_customers set customers_default_address_id = :customers_default_address_id where customers_id = :customers_id');
            $Qaddress->bindTable(':table_customers', TABLE_CUSTOMERS);
            $Qaddress->bindInt(':customers_default_address_id', $default_address_id);
            $Qaddress->bindInt(':customers_id', $customers_id);
            $Qaddress->setLogging($_SESSION['module']);
            $Qaddress->execute();
          }
        }
      }
    }

    function parseCsvFile(){
      global $osC_Language;

      $columns = array();
      $handle = @fopen($this->_filename, 'r');

      if ($handle) {
        $first_row = true;
        while (($cells = fgetcsv($handle, $this->_csv_line_length, $this->_csv_delimiter, $this->_csv_enclosure)) !== FALSE) {
          
          if ($first_row == true) {
            $columns = $cells;
            $first_row = false;
          } else {
            if (count($cells) < 10) {
              return false;
            } else {
              $data = array();
              $data['customers_id'] = $cells[0];
              $data['customers_groups_id'] = $cells[1];
              $data['customers_gender'] = $cells[2];
              $data['customers_firstname'] = $cells[3];
              $data['customers_lastname'] = $cells[4];
              $data['customers_dob'] = $cells[5];
              $data['customers_email_address'] = $cells[6];
              $data['customers_telephone'] = $cells[7];
              $data['customers_fax'] = $cells[8];
              $data['customers_status'] = $cells[9];
              $data['date_account_created'] = 'now()';
  
              $num_of_address = floor((sizeof($cells) - 10) / 14);
              $address_books = array();
  
              for ($i = 1; $i <= $num_of_address; $i++) {
                $address = array();
  
                $address['entry_gender'] = $cells[10 + 14 * ($i - 1) + 0];
                $address['entry_company'] = $cells[10 + 14 * ($i - 1) + 1];
                $address['entry_firstname'] = $cells[10 +  14 * ($i - 1) + 2];
                $address['entry_lastname'] = $cells[10 +  14 * ($i - 1) + 3];
                $address['entry_street_address'] = $cells[10 + 14 * ($i - 1) + 4];
                $address['entry_suburb'] = $cells[10 +  14 * ($i - 1) + 5];
                $address['entry_postcode'] = $cells[10 +  14 * ($i - 1) + 6];
                $address['entry_city'] = $cells[10 + 14 * ($i - 1) + 7];
                $address['entry_state'] = $cells[10 + 14 * ($i - 1) + 8];
                $address['entry_country_id'] = $cells[10 + 14 * ($i - 1) + 9];
                $address['entry_zone_id'] = $cells[10 +  14 * ($i - 1) + 10];
                $address['entry_telephone'] = $cells[10 + 14 * ($i - 1) + 11];
                $address['entry_fax'] = $cells[10 + 14 * ($i - 1) + 12];
                $address['entry_country'] = $cells[10 + 14 * ($i - 1) + 13];
  
                $address_books[] = $address;
              }
              $this->insertCustomer($data, $address_books);
            }
            
          }
        }
      }
      return true;
    }

    function parseXmlFile(){
      $customers = @simplexml_load_file($this->_filename);

      if (is_object($customers)) {
        foreach ($customers->Customer as $customer){
          $data['customers_id'] = $customer->ID;
          $data['customers_gender'] = $customer->Gender;
          $data['customers_firstname'] = $customer->Firstname;
          $data['customers_lastname'] = $customer->Lastname;
          $data['customers_dob'] = $customer->DateOfBirthday;
          $data['customers_email_address'] = $customer->Email;
          $data['customers_password'] = osc_encrypt_string($customer->Password);
          $data['customers_telephone'] = $customer->Telephone;
          $data['customers_fax'] = $customer->Fax;
          $data['customers_password'] = $customer->PassWord;
          $data['customers_status'] = $customer->Status;
          $data['date_account_created'] = 'now()';
  
          $address_books = array();
          foreach ($customer->AddressBooks->AddressBook as $address_book) {
            $address['entry_gender'] = $address_book->Gender;
            $address['entry_company'] = $address_book->Company;
            $address['entry_firstname'] = $address_book->Firstname;
            $address['entry_lastname'] = $address_book->Lastname;
            $address['entry_street_address'] = $address_book->Street;
            $address['entry_suburb'] = $address_book->Suburb;
            $address['entry_postcode'] = $address_book->Postcode;
            $address['entry_city'] = $address_book->City;
            $address['entry_country'] = $address_book->Country;
            $address['entry_state'] = $address_book->State;
            $address['entry_country_id'] = $address_book->CountryId;
            $address['entry_zone_id'] = $address_book->ZoneId;
            $address['entry_telephone'] = $address_book->Telephone;
            $address['entry_fax'] = $address_book->Fax;
  
            $address_books[] = $address;
          }
          $this->insertCustomer($data, $address_books);
        }
        
        return true;
      }
      
      return false;
    }
  }

  class osC_Products_Importer extends toC_Importer {
    var $_image_file = '';

    function osC_Products_Importer($parameters){
      parent::toC_Importer($parameters);

      if (!empty($parameters['image_file'])) {
        $this->_image_file = $parameters['image_file'];
        
        $temp_file = new upload($this->_image_file, DIR_FS_CACHE);
        
        if ( $temp_file->exists() && $temp_file->parse() && $temp_file->save() ) {
        
          require_once('../ext/zip/pclzip.lib.php');
  
          $archive = new PclZip($temp_file->destination . $temp_file->filename);
          
          $path = realpath($temp_file->destination . $temp_file->filename);
          
        	if ($archive->extract(PCLZIP_OPT_PATH, realpath(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/_upload/')) == 0) {
          	return false;
          } else {
          	@unlink($path);
          }
        }
      }
    }
    
    function isProductExist($products_id) {
      global $osC_Database;
      
      $Qcheck = $osC_Database->query('select * from :table_products where products_id = :products_id');
      $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
      $Qcheck->bindValue(':products_id', $products_id);
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() > 0) {
        return true;
      } else {
        return false;
      }
    }

    function insertProduct($product, $descriptions, $categories_id, $images){
      global $osC_Database;

      $insert = true;
      $products_id = (int) $product['products_id'];
      unset($product['products_id']);
      
      if (($products_id > 0) && osC_Products_Importer::isProductExist($products_id)) {
        $insert = false;
      }

      if($insert == true) {
        $products_id = toC_Importer::insertData(TABLE_PRODUCTS, $product);
        
        //categories
        if (is_array($categories_id) && !empty($categories_id)) {
          foreach($categories_id as $category) {
            $data = array('categories_id' => $category, 'products_id' => $products_id);
            toC_Importer::insertData(TABLE_PRODUCTS_TO_CATEGORIES, $data);
          }
        }

        //description
        foreach($descriptions as $description) {
          $description['products_id'] = $products_id;
          toC_Importer::insertData(TABLE_PRODUCTS_DESCRIPTION, $description);
        }

        $sort_id = 1;
        foreach($images as $image){
          $image['products_id'] = $products_id;
          $image['default_flag'] = (($sort_id == 1) ? 1 : 0);
          $image['sort_order'] = $sort_id++;
          
          if ( file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/_upload/' . $image['image']) ) {
            $image_id = toC_Importer::insertData(TABLE_PRODUCTS_IMAGES, $image);
            copy('../images/products/_upload/' . $image['image'], '../images/products/originals/' . $image['image']);

            $new_image_name =  $products_id . '_' . $image_id . '_' . $image['image'];
            @rename('../images/products/originals/' . $image['image'], '../images/products/originals/' . $new_image_name);

            $Qupdate = $osC_Database->query('update :table_products_images set image = :image where id = :id');
            $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qupdate->bindValue(':image', $new_image_name);
            $Qupdate->bindInt(':id', $image_id);
            $Qupdate->execute();  
          
            $osC_Image = new osC_Image_Admin();
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($new_image_name, $group['id'], 'products');
              }
            }
          }
        }
      }
    }

    function parseXmlFile(){
      global $osC_Language;
      
      $products = @simplexml_load_file($this->_filename);

      if (is_object($products)) {
        foreach ($products->Product as $product){
          $data['products_id'] = (string)$product->ID;
          $data['products_type'] = (string)$product->Type;
          $data['products_quantity'] = (string)$product->Quantity;
          $data['products_moq'] = (string)$product->Moq;
          $data['products_max_order_quantity'] = (string)$product->MaxQuantity;
          $data['products_price'] = (string)$product->Price;
          $data['products_sku'] = (string)$product->Sku;
          $data['products_model'] = (string)$product->Model;
          $data['products_weight'] = (string)$product->Weight;
          $data['products_weight_class'] = (string)$product->WeightClass;
          $data['products_status'] = (string)$product->Status;
          $data['products_tax_class_id'] = (string)$product->Tax;
          $data['manufacturers_id'] = (string)$product->Manufacturer;
          $data['quantity_discount_groups_id'] = (string)$product->QuantityDiscountGroup;
          $data['quantity_unit_class'] = (string)$product->UnitClass;
          $data['order_increment'] = (string)$product->OrderIncrement;
          $data['products_attributes_groups_id'] = (string)$product->ProductsAttributesGroup;
          $data['products_date_added'] = 'now()';
          $data['products_last_modified'] = 'now()';
          $data['products_date_available'] = 'NULL';
          $data['products_ordered'] = '0';

          $categories_id = array();
          if (is_object($product->Categories->Category)) {
            foreach ($product->Categories->Category as $cat) {
              $categories_id[] = (string)$cat;
            }
          }

          if (is_object($product->Descriptions->Description)) {
            $descriptions = array();
            foreach ($product->Descriptions->Description as $descriptionElem) {
              foreach ($osC_Language->getAll() as $l) {
                if ($l['code'] == $descriptionElem['code']) {
                  $description['language_id'] = $l['id'];
                }
              }
      
              $description['products_name'] = (string)$descriptionElem->ProductsName;
              $description['products_description'] = (string)$descriptionElem->ProductsDescription;
              $description['products_keyword'] = (string)$descriptionElem->ProductsKeyword;
              $description['products_tags'] = (string)$descriptionElem->ProductsTags;
              $description['products_url'] = (string)$descriptionElem->ProductsUrl;
              $description['products_friendly_url'] = (string)$descriptionElem->ProductsFriendlyUrl;
              $description['products_page_title'] = (string)$descriptionElem->ProductsPageTitle;
              $description['products_meta_keywords'] = (string)$descriptionElem->ProductsMetaKeywords;
              $description['products_meta_description'] = (string)$descriptionElem->ProductsMetaDescription;
    
              $descriptions[] = $description;
            }
          }
          
          $productsImages = array();
          if (is_object($product->Images->Image)) {
            foreach ($product->Images->Image as $imgElem) {
              $productImage['image'] = (string)$imgElem->ImageName;
              $productImage['date_added'] = 'now()';
    
              $productsImages[] = $productImage;
            }
          }

          $this->insertProduct($data, $descriptions, $categories_id, $productsImages);
        }
        return true;
      }
      return false;
    }

    function parseCsvFile(){
      global $osC_Language;

      $columns = array();

      $handle = @fopen($this->_filename, 'r');

      if ($handle) {
        $first_row = true;
        while (($cells = fgetcsv($handle, $this->_csv_line_length, $this->_csv_delimiter, $this->_csv_enclosure)) !== FALSE) {

        if($first_row == true) {
            $columns = $cells;
            $first_row = false;
          } else {
            if (count($cells) < 16) {
              return false;
            } else {
              $data = array();
              $data['products_id'] = $cells[0];
              $data['products_type'] = $cells[1];
              $data['products_quantity'] = $cells[2];
              $data['products_moq'] = $cells[3];
              $data['products_max_order_quantity'] = $cells[4];
              $data['products_price'] = $cells[5];
              $data['products_sku'] = $cells[6];
              $data['products_model'] = $cells[7];
              $data['products_weight'] = $cells[8];
              $data['products_weight_class'] = $cells[9];
              $data['products_status'] = $cells[10];
              $data['products_tax_class_id'] = $cells[11];
              $data['manufacturers_id'] = $cells[12];
              $data['quantity_discount_groups_id'] = $cells[13];
              $data['quantity_unit_class'] = $cells[14];
              $data['order_increment'] = $cells[15];
              $data['products_attributes_groups_id'] = $cells[16];
              $data['products_date_added'] = 'now()';
              $data['products_last_modified'] = 'now()';
              $data['products_date_available'] = 'NULL';
              $data['products_ordered'] = '0';
              
              $categories_id = explode('##', $cells[17]);

              $image_str = $cells[18];
              $images = array();
              if (!empty($image_str)) {
                $tmp = explode('##', $image_str);
                foreach ($tmp as $img) {
                  $image['image'] = $img;
                  $image['date_added'] = 'now()';
  
                  $images[] = $image;
                }
              }
              
              $num_of_desc = floor((sizeof($cells) - 19) / 10);
              $descriptions = array();
  
              for ($i = 1; $i <= $num_of_desc; $i++) {
                $address = array();
  
                $col = $columns[19 + ($i - 1) * 10];
                $description['language_id'] = 0;
                foreach ($osC_Language->getAll() as $l){
                  if( strpos($col, $l['code']) !== false ){
                    $description['language_id'] = $l['id'];
                  }
                }
  
                $description['products_name'] = $cells[19 + 10 * ($i - 1) + 0];
                $description['products_short_description'] = $cells[19 + 10 * ($i - 1) + 1];
                $description['products_description'] = $cells[19 + 10 * ($i - 1) + 2];
                $description['products_keyword'] = $cells[19 + 10 * ($i - 1) + 3];
                $description['products_tags'] = $cells[19 + 10 * ($i - 1) + 4];
                $description['products_url'] = $cells[19 + 10 * ($i - 1) + 5];
                $description['products_friendly_url'] = $cells[19 + 10 * ($i - 1) + 6];
                $description['products_page_title'] = $cells[19 + 10 * ($i - 1) + 7];
                $description['products_meta_keywords'] = $cells[19 + 10 * ($i - 1) + 8];
                $description['products_meta_description'] = $cells[19 + 10 * ($i - 1) + 9];
  
                $descriptions[] = $description;
              }

              $this->insertProduct($data, $descriptions, $categories_id, $images);
            }
          }
        }
      }
      return true;
    }
  }
?>
