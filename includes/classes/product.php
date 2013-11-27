<?php
/*
  $Id: product.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Product {
    var $_data = array(),
        $_customers_id = null,
        $_customers_groups_id = null,
        $_current_variants = null,
        $_customer_group_discount = null;

    function osC_Product($id, $customers_id =null) {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Image, $osC_Cache, $osC_Currencies;

      if (!empty($id)) {
        if ( $osC_Cache->read('product-' . $id . '-' . $osC_Language->getCode()) ) {
          $this->_data = $osC_Cache->getCache();
        } else {
          $Qproduct = $osC_Database->query('select p.products_id as id, p.products_type as type, p.products_max_order_quantity as max_order_quantity, p.products_moq as products_moq, p.order_increment as order_increment, p.products_price as price, p.products_tax_class_id as tax_class_id, p.products_date_added as date_added, p.products_date_available as date_available, p.manufacturers_id, p.quantity_discount_groups_id, p.quantity_unit_class, pd.products_name as name, pd.products_short_description as products_short_description, pd.products_description as description, pd.products_page_title as page_title, pd.products_meta_keywords as meta_keywords, pd.products_meta_description as meta_description, p.products_model as model, p.products_sku as sku, pd.products_keyword as keyword, pd.products_tags as tags, pd.products_url as url, p.quantity_discount_groups_id as quantity_discount_groups_id, p.products_weight as products_weight, p.products_weight_class as products_weight_class, quc.quantity_unit_class_title as unit_class, m.manufacturers_name from :table_products p left join :table_manufacturers m on (p.manufacturers_id = m.manufacturers_id), :table_products_description pd, :table_quantity_unit_classes quc where');
          $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);
          $Qproduct->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
          $Qproduct->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qproduct->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
  
          if (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $id)) {
            $this->_current_variants = osc_parse_variants_from_id_string($id);
            
            $Qproduct->appendQuery('p.products_id = :products_id');
            $Qproduct->bindInt(':products_id', osc_get_product_id($id));
          } else {
            $Qproduct->appendQuery('pd.products_keyword = :products_keyword');
            $Qproduct->bindValue(':products_keyword', $id);
          }
  
          $Qproduct->appendQuery('and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id and p.quantity_unit_class = quc.quantity_unit_class_id and quc.language_id = pd.language_id ');
          $Qproduct->bindInt(':language_id', $osC_Language->getID());
          $Qproduct->execute();
          
          if ($Qproduct->numberOfRows() === 1) {
            $this->_data = $Qproduct->toArray();
  
            //images
            $this->_data['images'] = array();
  
            $Qimages = $osC_Database->query('select id, image, default_flag from :table_products_images where products_id = :products_id order by sort_order');
            $Qimages->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qimages->bindInt(':products_id', $this->_data['id']);
            $Qimages->execute();
  
            while ($Qimages->next()) {
              $this->_data['images'][] = $Qimages->toArray();
            }
  
            //categories
            $Qcategory = $osC_Database->query('select categories_id from :table_products_to_categories where products_id = :products_id limit 1');
            $Qcategory->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
            $Qcategory->bindInt(':products_id', $this->_data['id']);
            $Qcategory->execute();
  
            $this->_data['category_id'] = $Qcategory->valueInt('categories_id');
            
            //attachments
            $Qattachments = $osC_Database->query('select attachments_name, pa.attachments_id, filename, cache_filename, attachments_description from :table_products_attachments pa, :table_products_attachments_description pad, :table_products_attachments_to_products pa2p where pa.attachments_id = pad.attachments_id and pa2p.attachments_id = pa.attachments_id and pa2p.products_id = :products_id and pad.languages_id = :language_id');
            $Qattachments->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
            $Qattachments->bindTable(':table_products_attachments_description', TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION);
            $Qattachments->bindTable(':table_products_attachments_to_products', TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS);
            $Qattachments->bindInt(":products_id", $this->_data['id']);
            $Qattachments->bindInt(":language_id", $osC_Language->getID());
            $Qattachments->execute();
            
            while ($Qattachments->next()) {
              $this->_data['attachments'][] = array('attachment_name' => $Qattachments->value('attachments_name'),
                                                    'filename' => $Qattachments->value('filename'),
                                                    'attachments_id' => $Qattachments->valueInt('attachments_id'),
                                                    'attachment_file_name' => $Qattachments->value('cache_filename'),
                                                    'description' => $Qattachments->value('attachments_description'));
            }
            
            //accessories
            $Qaccessories = $osC_Database->query('select accessories_id from :table_products_accessories pa where pa.products_id =  :products_id');
            $Qaccessories->bindTable(':table_products_accessories', TABLE_PRODUCTS_ACCESSORIES);
            $Qaccessories->bindInt(":products_id", $this->_data['id']);
            $Qaccessories->execute();
            
            while ($Qaccessories->next()) {
              $this->_data['accessories'][] = $Qaccessories->value('accessories_id');
            }
            
            //variants
            $this->iniProductVariants();
            
            //attributes
            $Qattributes = $osC_Database->query('select pav.name, pav.module, pav.value as selections, pa.value from :table_products_attributes pa, :table_products_attributes_values pav where pa.products_attributes_values_id = pav.products_attributes_values_id and pa.language_id = pav.language_id and pa.products_id = :products_id and pa.language_id = :language_id');
            $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
            $Qattributes->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
            $Qattributes->bindInt(':products_id', $this->_data['id']);
            $Qattributes->bindInt(':language_id', $osC_Language->getID());
            $Qattributes->execute();
            
            $attributes = array();
            while ($Qattributes->next()) {
              $name = $Qattributes->value('name');
              $value = $Qattributes->value('value');
              
              if ($Qattributes->value('module') == 'pull_down_menu') {
                $selections = $Qattributes->value('selections');
                $selections = explode(',', $selections);
                
                if (isset($selections[$value - 1])) {
                  $value = $selections[$value - 1];
                }
              }
              
              $attributes[] = array('name' => $name, 'value' => $value);
            }
            $this->_data['attributes'] = $attributes;
            
            //customizations
            $Qcustomizations = $osC_Database->query('select cf.customization_fields_id, products_id, type, is_required, name from :table_customization_fields cf inner join :table_customization_fields_description cfd on cf.customization_fields_id = cfd.customization_fields_id where cf.products_id = :products_id and cfd.languages_id = :languages_id');
            $Qcustomizations->bindTable(':table_customization_fields', TABLE_CUSTOMIZATION_FIELDS);
            $Qcustomizations->bindTable(':table_customization_fields_description', TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION);
            $Qcustomizations->bindInt(':products_id', $this->_data['id']);
            $Qcustomizations->bindInt(':languages_id', $osC_Language->getID());
            $Qcustomizations->execute();
            
            $customizations = array();
            while ($Qcustomizations->next()) {
              $customizations[] = array('customization_fields_id' => $Qcustomizations->valueInt('customization_fields_id'),
                                        'products_id' => $Qcustomizations->valueInt('products_id'),
                                        'type' => $Qcustomizations->valueInt('type'),
                                        'is_required' => $Qcustomizations->valueInt('is_required'),
                                        'name' => $Qcustomizations->value('name'));
            }
            $this->_data['customizations'] = $customizations;
  
            if ($this->_data['type'] == PRODUCT_TYPE_DOWNLOADABLE) {
              $Qdownloadables = $osC_Database->query('select * from :table_products_downloadables where products_id = :products_id');
              $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qdownloadables->bindInt(':products_id', $id);
              $Qdownloadables->execute();
              
              $downloadable = $Qdownloadables->toArray(); 
              $this->_data = array_merge($this->_data, $downloadable);
            } else if ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $Qcertificates = $osC_Database->query('select * from :table_products_gift_certificates where products_id = :products_id');
              $Qcertificates->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
              $Qcertificates->bindInt(':products_id', $id);
              $Qcertificates->execute();
              
              $certificate = $Qcertificates->toArray(); 
              $this->_data = array_merge($this->_data, $certificate);
            }
          }
          
          $osC_Cache->write('product-' . $id . '-' . $osC_Language->getCode(), $this->_data);
        }
        
        //format the variants display price
        if ( isset($this->_data['variants']) && is_array($this->_data['variants']) ) {
          $products_variants = $this->_data['variants'];
          
          foreach ($products_variants as $products_id_string => $data) {
            $this->_data['variants'][$products_id_string]['display_price'] = $osC_Currencies->displayPrice($data['price'], $this->_data['tax_class_id']);
          }
        }
        
        //the customer group and discount group is not cached
        if ($customers_id == null) {
          global $osC_Customer;
        
          if(is_object($osC_Customer) && ($osC_Customer->getID() !== false)){
            $this->_customers_id = $osC_Customer->getID();
            $this->_customers_groups_id = $osC_Customer->getCustomerGroupID();
            $this->_customer_group_discount = $osC_Customer->getCustomerGroupDiscount();
          }
        } else {
          global $osC_Database;
        
          $QcustomerGroup = $osC_Database->query('select c.customers_groups_id, cg.customers_groups_discount from :table_customers c, :table_customers_groups cg where c.customers_groups_id = cg.customers_groups_id and c.customers_id = :customers_id ');
          $QcustomerGroup->bindTable(':table_customers', TABLE_CUSTOMERS);
          $QcustomerGroup->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
        
          $QcustomerGroup->bindInt(':customers_id', $customers_id);
          $QcustomerGroup->execute();
        
          if($QcustomerGroup->numberOfRows() == 1){
            $this->_customers_id = $customers_id;
            $this->_customers_groups_id = $QcustomerGroup->valueInt('customers_groups_id');
            $this->_customer_group_discount = $QcustomerGroup->value('customers_groups_discount');
          }
        }
        
        if ($this->_data['quantity_discount_groups_id'] > 0) {
          $this->_data['quantity_discount'] = $this->getDiscountGroup($this->_customers_groups_id);
        }
      }
    }
      
    function getAverageReviewsRating() {
      global $osC_Services, $osC_Database, $osC_Language;

      if (is_object($osC_Services) && $osC_Services->isStarted('reviews')) {
        if ($this->_reviews_average_rating == null) {
          $Qavg = $osC_Database->query('select avg(reviews_rating) as rating from :table_reviews where products_id = :products_id and languages_id = :languages_id and reviews_status = 1');
          $Qavg->bindTable(':table_reviews', TABLE_REVIEWS);
          $Qavg->bindInt(':products_id', $this->_data['id']);
          $Qavg->bindInt(':languages_id', $osC_Language->getID());
          $Qavg->execute();
        
          $this->_reviews_average_rating = round($Qavg->value('rating'));
        }
      }
      
      return $this->_reviews_average_rating;
    }
    
    function getDiscountGroup($customers_groups_id = null){
      global $osC_Database;

      $Qdiscount = $osC_Database->query('select qdg.*, qdgv.* from :table_quantity_discount_groups qdg, :table_quantity_discount_groups_values qdgv where qdg.quantity_discount_groups_id =:quantity_discount_groups_id and qdg.quantity_discount_groups_id = qdgv.quantity_discount_groups_id ');
      $Qdiscount->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qdiscount->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qdiscount->bindInt(':quantity_discount_groups_id', $this->_data['quantity_discount_groups_id']);

      if (($customers_groups_id != null) && ($customers_groups_id > 0)) {
        $Qdiscount->appendQuery(' and qdgv.customers_groups_id = :customers_groups_id');
        $Qdiscount->bindInt(':customers_groups_id', $customers_groups_id);
      }else{
        $Qdiscount->appendQuery(' and qdgv.customers_groups_id = 0 ');
      }

      $Qdiscount->appendQuery(' order by qdgv.quantity');
      $Qdiscount->execute();

      if ($Qdiscount->numberOfRows() > 0) {
        $quantity_discount_array = array();

        //Initialize the quantity discount array, start with quantity 1
        $quantity_discount_array[1] = 0;
        while ($Qdiscount->next()) {
          $quantity_discount_array[$Qdiscount->value('quantity')] =$Qdiscount->value('discount');
        }
        $Qdiscount->freeResult();

        return $quantity_discount_array;
      }else if($customers_groups_id != null){
        return $this->getDiscountGroup();
      }else{
        return false;
      }
    }

    function getProductVariantsId($variants){
      $product_id_string = osc_get_product_id_string($this->getID(), $variants);

      if(isset($this->_data['variants']) && isset($this->_data['variants'][$product_id_string])){
        return $this->_data['variants'][$product_id_string]['variants_id'];
      }else{
        return false;
      }
    }
    
    function getVariantsComboboxArray() {
      if ($this->hasVariants()) {
        $combobox_array = array();
        
        //sort the variants groups based on its its sort order or name (ASC direction)
        $variants_groups = $this->_data['variants_groups'];
        usort($variants_groups, array('self', '_sortVariantsGroups'));

        foreach ($variants_groups as $group) {
          $values = array();
          
          //sort the variants entries based on its sort order or name (ASC direction)
          $variants_entries = $this->_data['variants_groups_values'][$group['groups_id']];
          usort($variants_entries, array('self', '_sortVariantsEntries'));
          
          foreach($variants_entries as $variants_enty) {
            $values[] = array('id' => $variants_enty['variants_values_id'], 'text' => $variants_enty['variants_values_name']);
          }
          
       
          $combobox_array[$group['groups_name']] = osc_draw_pull_down_menu(
            'variants[' . $group['groups_id'] . ']', 
            $values, 
            (!osc_empty($this->_current_variants)) ? $this->_current_variants[ $group['groups_id']] : $this->_data['default_variant']['groups_id'][$group['groups_id']]);
        }
        return $combobox_array;
      }
      
      return false;
    }
    
    /**
     * sort the variants groups based on its its sort order or name (ASC direction)
     *
     * @access private
     * @param array $group_a a
     * @param array $group_b
     *
     * return int
     *
     */
    function _sortVariantsGroups($group_a, $group_b) {
     if ($group_a['sort_order'] < $group_b['sort_order']) {
        return -1;
      }
      
      if ($group_a['sort_order'] > $group_b['sort_order']) {
        return 1;
      }
      
      //sort order is equel. Compare their names
      if ($group_a['sort_order'] == $group_b['sort_order']) {
        return strnatcmp($group_a['groups_name'], $group_b['groups_name']);
      }
    }
    
    /**
     * Sort the variants entries for each variants group based on its sort order or name (ASC direction)
     * 
     * @access private
     * @param array $entry a 
     * @param array $entry_b
     * 
     * return int
     * 
     */
    function _sortVariantsEntries($entry_a, $entry_b) {
      if ($entry_a['sort_order'] < $entry_b['sort_order']) {
        return -1;
      }
      
      if ($entry_a['sort_order'] > $entry_b['sort_order']) {
        return 1;
      }
      
      //sort order is equel. Compare their names
      if ($entry_a['sort_order'] == $entry_b['sort_order']) {
        return strnatcmp($entry_a['variants_values_name'], $entry_b['variants_values_name']);
      }
    }
    
    function getDefaultVariant() {
      if ($this->hasVariants()) {
        return $this->_data['default_variant'];
      }
      
      return false;
    }

    function iniProductVariants() {
      global $osC_Database, $osC_Language, $osC_Currencies;

      $products_variants = array();

      $Qvariants = $osC_Database->query('select * from :table_products_variants where products_id = :products_id order by is_default DESC');
      $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qvariants->bindInt(':products_id', $this->getID());
      $Qvariants->execute();

      $groups = array();
      $groups_values = array();
      while ($Qvariants->next()) {
        $Qvalues = $osC_Database->query('select pve.products_variants_groups_id as groups_id, pve.products_variants_values_id as variants_values_id, pvg.products_variants_groups_name as groups_name, pvg.sort_order as groups_sort_order, pvv.products_variants_values_name as variants_values_name, pvv.sort_order as sort_order from :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = pvv.language_id and pvg.language_id = :language_id and pve.products_variants_id = :products_variants_id order by pve.products_variants_groups_id');
        $Qvalues->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
        $Qvalues->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
        $Qvalues->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
        $Qvalues->bindInt(':language_id', $osC_Language->getID());
        $Qvalues->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
        $Qvalues->execute();
        
        $variants = array();
        $groups_name = array();
        while ($Qvalues->next()) {
          $variants[$Qvalues->value('groups_id')] = $Qvalues->value('variants_values_id');
          $groups_name[$Qvalues->value('groups_name')] = $Qvalues->value('variants_values_name');
          
          $groups[] = array('groups_id' => $Qvalues->valueInt('groups_id'), 
                            'groups_name' => $Qvalues->value('groups_name'), 
                            'sort_order' => $Qvalues->valueInt('groups_sort_order'));
          
          if (!is_array($groups_values[$Qvalues->value('groups_id')])) {
            $groups_values[$Qvalues->value('groups_id')] = array();
          }
          
          if (!in_array($Qvalues->value('variants_values_id'), $groups_values[$Qvalues->value('groups_id')])) {
            $groups_values[$Qvalues->value('groups_id')][$Qvalues->value('variants_values_id')] = array('variants_values_id' => $Qvalues->valueInt('variants_values_id'), 
                                                                                                        'sort_order' => $Qvalues->valueInt('sort_order'), 
                                                                                                        'variants_values_name' => $Qvalues->value('variants_values_name'));
          }
        }
        $Qvalues->freeResult();
        $product_id_string = osc_get_product_id_string($this->getID(), $variants);

        $products_variants[$product_id_string]['variants_id'] = $Qvariants->valueInt('products_variants_id');
        $products_variants[$product_id_string]['is_default'] = $Qvariants->valueInt('is_default');
        $products_variants[$product_id_string]['sku'] = $Qvariants->value('products_sku');
        $products_variants[$product_id_string]['price'] = $Qvariants->value('products_price');
//display price should not be cached, it should be retrieved at runtime
//      $products_variants[$product_id_string]['display_price'] = $osC_Currencies->displayPrice($Qvariants->value('products_price'), $this->_data['tax_class_id']);
        $products_variants[$product_id_string]['status'] = $Qvariants->valueInt('products_status');
//quantity will not be cached, it will be retrieved at runtime
//        $products_variants[$product_id_string]['quantity'] = $Qvariants->value('products_quantity');
        $products_variants[$product_id_string]['weight'] = $Qvariants->value('products_weight');
        $products_variants[$product_id_string]['image'] = $this->getImageByID($Qvariants->value('products_images_id'));
        $products_variants[$product_id_string]['groups_id'] = $variants;
        $products_variants[$product_id_string]['groups_name'] = $groups_name;
        $products_variants[$product_id_string]['filename'] = $Qvariants->value('filename');
        $products_variants[$product_id_string]['cache_filename'] = $Qvariants->value('cache_filename');
        
        if ($Qvariants->valueInt('is_default') == 1) {
          $this->_data['default_variant'] = $products_variants[$product_id_string];
          $this->_data['default_variant']['product_id_string'] = $product_id_string;
        }
        
        if ($this->_data['type'] == PRODUCT_TYPE_DOWNLOADABLE) {
          $products_variants[$product_id_string]['filename'] = $Qvariants->value('filename');
          $products_variants[$product_id_string]['cache_filename'] = $Qvariants->value('cache_filename');
        }
      }
      
      $Qvariants->freeResult();

      $this->_data['variants'] = $products_variants;
      $this->_data['variants_groups'] = $groups;
      $this->_data['variants_groups_values'] = $groups_values;
    }
    
    function hasQuantityDiscount(){
      return (isset($this->_data['quantity_discount']) && !empty($this->_data['quantity_discount']));
    }

    function getQuantityDiscount($quantity){
      $quantity_discount = 0;
      if($this->hasQuantityDiscount()){
        $quantities = array_keys($this->_data['quantity_discount']);
        for ($i = sizeof($quantities); $i > 0; $i--) {
          if($quantity >= $quantities[$i-1]){
            $quantity_discount = $this->_data['quantity_discount'][$quantities[$i-1]];
            break;
          }
        }
      }
      return $quantity_discount;
    }


    function isValid() {
      if (empty($this->_data)) {
        return false;
      }

      return true;
    }

    function getData($key) {
      if (isset($this->_data[$key])) {
        return $this->_data[$key];
      }

      return false;
    }

    function getID() {
      return $this->_data['id'];
    }

    function getTitle() {
      return $this->_data['name'];
    }

    function getProductType() {
      return $this->_data['type'];
    }
    
    function isSimple() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_SIMPLE));
    }
    
    function isVirtual() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_VIRTUAL));
    }
    
    function isDownloadable() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_DOWNLOADABLE));
    }
    
    function hasSampleFile() {
      return (isset($this->_data['sample_filename']) && !empty($this->_data['sample_filename']));
    }
      
    function getSampleFile() {
      return $this->_data['sample_filename'];
    }
    
    function isGiftCertificate() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE));
    }
    
    function getGiftCertificateType() {
      if ($this->isGiftCertificate()) {
        return $this->_data['gift_certificates_type'];
      }
      
      return false;
    }
    
    function isEmailGiftCertificate() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($this->_data['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL)); 
    }
    
    function isPhysicalGiftCertificate() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($this->_data['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL));
    }

    function isFixAmountGiftCertificate() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($this->_data['gift_certificates_amount_type'] == GIFT_CERTIFICATE_TYPE_FIX_AMOUNT));
    }

    function isOpenAmountGiftCertificate() {
      return (isset($this->_data['type']) && ($this->_data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($this->_data['gift_certificates_amount_type'] == GIFT_CERTIFICATE_TYPE_OPEN_AMOUNT));
    }
    
    function getOpenAmountMinValue() {
      return $this->_data['open_amount_min_value'];
    }
    
    function getOpenAmountMaxValue() {
      return $this->_data['open_amount_max_value'];
    }    
    
    function getShortDescription() {
      return $this->_data['products_short_description'];
    }
    
    function getDescription() {
      return $this->_data['description'];
    }

    function hasModel() {
      return (isset($this->_data['model']) && !empty($this->_data['model']));
    }

    function getModel($variants = null) {
      if ($variants == null || empty($variants)) {
        return $this->_data['model'];
      } else {
        $product_id_string = osc_get_product_id_string($this->getID(), $variants);
        
        return $this->_data['variants'][$product_id_string]['model'];
      }
    }
    
    function hasSKU() {
      return (isset($this->_data['sku']) && !empty($this->_data['sku']));
    }
    
    function getSKU($variants = null) {
      if ($variants == null || empty($variants)) {
        $sku = $this->_data['sku'];
  
        if (is_array($this->_data['default_variant']) && !empty($this->_data['default_variant'])){
          $sku = $this->_data['default_variant']['sku'];
        }
        
        return $sku;
      } else {
        $product_id_string = osc_get_product_id_string($this->getID(), $variants);
        
        return $this->_data['variants'][$product_id_string]['sku'];
      }
    }

    function hasKeyword() {
      return (isset($this->_data['keyword']) && !empty($this->_data['keyword']));
    }

    function getKeyword() {
      return $this->_data['keyword'];
    }

    function hasPageTitle() {
      return (isset($this->_data['page_title']) && !empty($this->_data['page_title']));
    }

    function getPageTitle() {
      return $this->_data['page_title'];
    }
  
    function hasMetaKeywords() {
      return (isset($this->_data['meta_keywords']) && !empty($this->_data['meta_keywords']));
    }

    function getMetaKeywords() {
      return $this->_data['meta_keywords'];
    }
  
    function hasMetaDescription() {
      return (isset($this->_data['meta_description']) && !empty($this->_data['meta_description']));
    }

    function getMetaDescription() {
      return $this->_data['meta_description'];
    }
    
    function hasTags() {
      return (isset($this->_data['tags']) && !empty($this->_data['tags']));
    }

    function getTags() {
      return $this->_data['tags'];
    }
    
    function getMOQ() {
      return $this->_data['products_moq'];
    }
    
    function getMaxOrderQuantity() {
      return $this->_data['max_order_quantity'];
    }
    
    function getOrderIncrement() {
      return $this->_data['order_increment'];
    }

    function getUnitClass() {
      return $this->_data['unit_class'];
    }
    
    function getSpecialPrice($variants = array()) {
      global $osC_Specials;
      
      $new_price = null;
      if (count($variants) < 1) {
        $new_price = $osC_Specials->getPrice($this->_data['id']);
      }else {
        //get the new special price - support the variants specials
        $products_variants_id = $this->getProductVariantsId($variants);
        
        //variants specials price
        $new_price = $osC_Specials->getVariantsPrice($products_variants_id);
      }
      
      return $new_price;
    }
    
    function getPrice($variants = null, $quantity = 1) {

      //get product price
      $product_price = $this->_data['price'];
      if (is_array($variants) && !empty($variants)) {
        $product_id_string = osc_get_product_id_string($this->getID(), $variants);
        if (isset($this->_data['variants'][$product_id_string]))
          $product_price = $this->_data['variants'][$product_id_string]['price'];
      } else {
        if ($this->hasVariants()) {
          if (is_array($this->_data['default_variant']) && !empty($this->_data['default_variant'])){
              $product_price = $this->_data['default_variant']['price'];
          }
        }
      }
      

      $qty_discount = $this->getQuantityDiscount($quantity);
      $customer_grp_discount = is_numeric($this->_customer_group_discount) ? $this->_customer_group_discount : 0;

      $product_price = round($product_price * (1 - $qty_discount/100) * (1 - $customer_grp_discount/100), 2);
      return $product_price;
    }
    
    function getPriceFormated($with_special = false, $variants = array()) {
      global $osC_Services, $osC_Specials, $osC_Currencies;
    
      $price = '';
      if ($this->isGiftCertificate() && $this->isOpenAmountGiftCertificate()) {
        $price = $osC_Currencies->displayPrice($this->_data['open_amount_min_value'], $this->_data['tax_class_id']) . ' ~ ' . $price = $osC_Currencies->displayPrice($this->_data['open_amount_max_value'], $this->_data['tax_class_id']);
      } else {
        //get the new special price - support the variants specials
        $new_price = null;
        
        if (($with_special === true) && is_object($osC_Services) && $osC_Services->isStarted('specials')) {
          if ($this->hasVariants()) {
            if (is_array($variants) && !empty($variants)) {
              $products_variants_id = $this->getProductVariantsId($variants);
            }else {
              $products_variants_id = $this->_data['default_variant']['variants_id'];
            }
            
            //variants specials price
            $new_price = $osC_Specials->getVariantsPrice($products_variants_id);
            if ($new_price !== null) {
              $price = '<s>' . $osC_Currencies->displayPrice($this->getPrice($variants), $this->_data['tax_class_id']) . '</s> <span class="productSpecialPrice">' . $osC_Currencies->displayPrice($new_price, $this->_data['tax_class_id']) . '</span>';
            }
          }else {
            //general special price
            $new_price = $osC_Specials->getPrice($this->_data['id']);
            if ($new_price !== null) {
              $price = '<s>' . $osC_Currencies->displayPrice($this->_data['price'], $this->_data['tax_class_id']) . '</s> <span class="productSpecialPrice">' . $osC_Currencies->displayPrice($new_price, $this->_data['tax_class_id']) . '</span>';
            }
          } 
        }
        
        //not to find the new special price
        if ($new_price == null) {
          $price = $osC_Currencies->displayPrice($this->getPrice($variants), $this->_data['tax_class_id']);
        }
      }
    
      return $price;
    }

    function getCategoryID() {
      return $this->_data['category_id'];
    }

    function getImages() {
      return $this->_data['images'];
    }
    
    function hasSpecial() {
      global $osC_Services, $osC_Specials;

      if ( is_object($osC_Services) && $osC_Services->isStarted('specials') ) {
        $special = $osC_Specials->getPrice($this->_data['id']);
        
        if ( is_numeric($special) && ($special > 0) ) {
          return true;
        }
      }

      return false;
    }

    function hasImage() {
      foreach ($this->_data['images'] as $image) {
        if ($image['default_flag'] == '1') {
          return true;
        }
      }
    }

    function getImage($variants = null) {
      $default_image = null;
      $default_variant_image = null;
      $current_variants = null;
      
      if (isset($this->_data['images']) && is_array($this->_data['images'])) {
        foreach ($this->_data['images'] as $image) {
          //get variant default image
          if ($this->hasVariants()) {
          	if (!osc_empty($this->_current_variants)) {
          		$current_variants = $this->_current_variants;
          	}else if ($variants !== null) {
          		$current_variants = $variants;
          	}
          	
            if ($current_variants !== null) {
              $product_id_string = osc_get_product_id_string($this->getID(), $current_variants);
              $product_variant = $this->_data['variants'][$product_id_string];
              
              $default_variant_image = $product_variant['image'];
              break;
            }else if (is_array($this->_data['default_variant']) && !empty($this->_data['default_variant'])) {
              if ($image['id'] == $this->_data['default_variant']['image']) {
                $default_variant_image = $image['image'];
              }
            }
          } 
          
          //get default image
          if ($image['default_flag'] == '1') {
            $default_image = $image['image'];
          }
        }
      }
      
      if ($default_variant_image != null) {
        return $default_variant_image;
      }
      
      return $default_image;
    }
    
    function getImageByID($id) {
      foreach ($this->_data['images'] as $image) {
        if ($image['id'] == $id) {
          return $image['image'];
        }
      }
    }

    function hasURL() {
      return (isset($this->_data['url']) && !empty($this->_data['url']));
    }

    function getURL() {
      return $this->_data['url'];
    }

    function getDateAvailable() {
      return $this->_data['date_available'];
    }

    function getDateAdded() {
      return $this->_data['date_added'];
    }

    function getWeight($variants = null){
      if ($variants == null || empty($variants)) {
        return $this->_data['products_weight'];
      } else {
        $product_id_string = osc_get_product_id_string($this->getID(), $variants);
        
        return $this->_data['variants'][$product_id_string]['weight'];
      }
    }

    function getTaxClassID(){
      return $this->_data['tax_class_id'];
    }

    function getWeightClass() {
      return $this->_data['products_weight_class'];
    }
    
    function getManufacturer() {
      return $this->_data['manufacturers_name'];
    }
    
    function getQuantityFromDB() {
      global $osC_Database;
      
      if (!isset($this->_data['quantity'])) {
        $Qquantity = $osC_Database->query('select products_quantity as quantity from :table_products where products_id = :products_id');
        $Qquantity->bindTable(':table_products', TABLE_PRODUCTS);
        $Qquantity->bindInt(':products_id', $this->_data['id']);
        
        if ($Qquantity->numberOfRows() === 1) {
          $this->_data['quantity'] = $Qquantity->value('quantity');
        }
        $Qquantity->freeResult();
      }
 
      if ( $this->hasVariants() && !isset($this->_data['default_variant']['quantity']) ) {
        foreach ($this->_data['variants'] as $id => $variant) {
          $Qvariants = $osC_Database->query('select products_quantity as quantity from :table_products_variants where products_variants_id = :products_variants_id');
          $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qvariants->bindInt(':products_variants_id', $variant['variants_id']);
          $Qvariants->execute();
          
          if ($Qvariants->numberOfRows() === 1) {
            $this->_data['variants'][$id]['quantity'] = $Qvariants->value('quantity');
            
            if ($variant['is_default'] == '1') {
              $this->_data['default_variant']['quantity'] = $Qvariants->value('quantity');
            }
          }
        }
      }
    }

    function getQuantity($products_id_string = '') {
      $this->getQuantityFromDB();
      
      if (is_numeric(strpos($products_id_string,'#'))) {
        if (isset($this->_data['variants'][$products_id_string])) {
          return $this->_data['variants'][$products_id_string]['quantity'];
        }
      } else {
        $quantity = $this->_data['quantity'];
        
        if (is_array($this->_data['default_variant']) && !empty($this->_data['default_variant'])) {
          $quantity = $this->_data['default_variant']['quantity'];
        }

        return $quantity;
      }
    }
    
    function hasVariants() {
      return (isset($this->_data['variants']) && !empty($this->_data['variants']));
    }

    function &getVariants() {
      $this->getQuantityFromDB();
      
      return $this->_data['variants'];
    }
    
    function hasCustomizations() {
      return (isset($this->_data['customizations']) && !empty($this->_data['customizations']));
    }

    function &getCustomizations() {
      return $this->_data['customizations'];
    }
    
    function hasRequiredCustomizationFields() {
      global $toC_Customization_Fields;
      
      foreach ($this->getCustomizations() as $field) {
        if ($field['is_required'] == '1') {
          return true;
        }
      }

      return false;
    }
    
    function hasAttributes() {
      return (isset($this->_data['attributes']) && !empty($this->_data['attributes']));
    }

    function &getAttributes() {
      return $this->_data['attributes'];
    }

    function hasAttachments(){
      return (isset($this->_data['attachments']) && !empty($this->_data['attachments']));
    }
    
    function getAttachments() {
      return $this->_data['attachments'];
    }
    
    function hasAccessories(){
      return (isset($this->_data['accessories']) && !empty($this->_data['accessories']));
    }
    
    function getAccessories() {
      return $this->_data['accessories'];
    }
    
    function checkEntry($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select p.products_id from :table_products p');
      $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);

      if (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $id)) {
        $Qcheck->appendQuery('where p.products_id = :products_id');
        $Qcheck->bindInt(':products_id', osc_get_product_id($id));
      } else {
        $Qcheck->appendQuery(', :table_products_description pd where pd.products_keyword = :products_keyword and pd.products_id = p.products_id');
        $Qcheck->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qcheck->bindValue(':products_keyword', $id);
      }

      $Qcheck->appendQuery('and p.products_status = 1 limit 1');
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() === 1) {
        return true;
      }

      return false;
    }

    function incrementCounter() {
      global $osC_Database, $osC_Language;

      $Qupdate = $osC_Database->query('update :table_products_description set products_viewed = products_viewed+1 where products_id = :products_id and language_id = :language_id');
      $Qupdate->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qupdate->bindInt(':products_id', osc_get_product_id($this->_data['id']));
      $Qupdate->bindInt(':language_id', $osC_Language->getID());
      $Qupdate->execute();
    }

    function numberOfImages() {
      return sizeof($this->_data['images']);
    }

    function &getListingNew() {
      global $osC_Database, $osC_Language;

      $Qproducts = $osC_Database->query('select p.products_id, p.products_price, p.products_type, p.products_tax_class_id, p.products_date_added, pd.products_name, pd.products_keyword, m.manufacturers_name, i.image from :table_products p left join :table_manufacturers m on (p.manufacturers_id = m.manufacturers_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc, pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setBatchLimit((isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1), MAX_DISPLAY_PRODUCTS_NEW);
      $Qproducts->execute();

      return $Qproducts;
    }
    
    function &getListingFeature() {
      global $osC_Database;
      
      $Qproducts = $osC_Database->query('select products_id from :table_products_frontpage');
      $Qproducts->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qproducts->execute();
      
      return $Qproducts;
    }
    
    function &getListingSearch($search) {
      global $osC_Database, $osC_Language, $osC_Image;
      
      $terms = explode(',', $search);
      
      $Qproducts = $osC_Database->query('select p.products_id, p.products_price, p.products_tax_class_id, p.products_date_added, pd.products_name, pd.products_keyword, m.manufacturers_name, i.image from :table_products p left join :table_manufacturers m on (p.manufacturers_id = m.manufacturers_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where');
      foreach ($terms as $term) {
        $Qproducts->appendQuery('pd.products_name like :term and');
        $Qproducts->bindValue(':term', '%' . $term . '%');
      }
      $Qproducts->appendQuery('p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc, pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setBatchLimit((isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1), MAX_DISPLAY_PRODUCTS_NEW);
      $Qproducts->execute();
      
      return $Qproducts;
    }

    function renderCustomizationFieldsList() {
      global $toC_Customization_Fields;
      
      $output = '<ul>';
      foreach ($this->getCustomizations() as $field) {
        $tmp = $toC_Customization_Fields->getCustomizationField($this->getID(), $field['customization_fields_id']);
        $value = ($tmp === false) ? null : $tmp['customization_value'];

        if ($field['type'] == 0) {
          $output .= '<li>' . osc_draw_label($field['name'], 'customizations_' . $field['customization_fields_id'], $value, ($field['is_required'] == '1' ? true : false)) . osc_draw_file_field('customizations_' . $field['customization_fields_id'], true) . '<br /><span>' . $value . '</span></li>';
        } else {
          $output .= '<li>' . osc_draw_label($field['name'], 'customizations[' . $field['customization_fields_id'] . ']', null, ($field['is_required'] == '1' ? true : false)) . osc_draw_input_field('customizations[' . $field['customization_fields_id'] . ']', $value, 'size="30"') . '</li>';
        }
      }
      $output .= '</ul>';

      return $output;
    }
    
    function renderQuantityDiscountTable(){
      global $osC_Language;

      $output = '<table border="0" cellspacing="0" cellpadding="2" class="productDiscountsTable">' . "\n" .
                '<thead>' . "\n" .
                '  <tr>' . "\n" .
                '    <th>' . $osC_Language->get('table_heading_quantity') . '</th>' . "\n" .
                '    <th align="right">' . $osC_Language->get('table_heading_discount') . '</th>' . "\n" .
                '  </tr>' . "\n" .
                '</thead>' . "\n";

      $output .= '<tbody>';
      $quantities = array_keys($this->_data['quantity_discount']);
      for($i = 0; $i < (sizeof($quantities) - 1); $i++){
        $output .= '  <tr>' . "\n" .
                   '    <td>' . $quantities[$i] . ' ~ ' . ($quantities[$i+1] - 1) . '</td>' . "\n" .
                   '    <td align="right">'  . $this->_data['quantity_discount'][$quantities[$i]] . '%</td> ' . "\n" .
                   '  </tr>' . "\n";
      }

      $output .= '  <tr>' . "\n" .
                 '    <td>' . $quantities[sizeof($quantities) - 1] . '+' . '</td>' . "\n" .
                 '    <td align="right">'  . $this->_data['quantity_discount'][$quantities[sizeof($quantities) - 1]] . '%</td> ' . "\n" .
                 '  </tr>' . "\n";

      $output .= '</tbody></table>';

      return $output;
    }

    function updateStock($orders_id, $orders_products_id, $products_id, $products_quantity){
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if (STOCK_LIMITED == '1') {
        $Qvariants = $osC_Database->query('select products_variants_groups_id, products_variants_values_id from :table_orders_products_variants where orders_products_id = :orders_products_id order by products_variants_groups_id');
        $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $Qvariants->bindInt(':orders_products_id', $orders_products_id);
        $Qvariants->execute();

        if($Qvariants->numberOfRows() > 0){

          $variants = array();
          while ($Qvariants->next()){
            $variants[$Qvariants->value('products_variants_groups_id')] = $Qvariants->value('products_variants_values_id');
          }
          $Qvariants->freeResult();

          $osC_Product = new osC_Product($products_id);
          $products_variants_id = $osC_Product->getProductVariantsId($variants);

          $Qstock = $osC_Database->query('select products_quantity from :table_products_variants where products_variants_id = :products_variants_id');
          $Qstock->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qstock->bindInt(':products_variants_id', $products_variants_id);
          $Qstock->execute();

          if ($Qstock->numberOfRows() > 0) {
            $attrib_stock_left = $Qstock->valueInt('products_quantity');
            $attrib_stock_left = $attrib_stock_left - $products_quantity;

            $QstockUpdate = $osC_Database->query('update :table_products_variants set products_quantity = :products_quantity where products_variants_id = :products_variants_id');
            $QstockUpdate->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $QstockUpdate->bindInt(':products_quantity', $attrib_stock_left);
            $QstockUpdate->bindInt(':products_variants_id', $products_variants_id);
            $QstockUpdate->setLogging($_SESSION['module'], $orders_id);
            $QstockUpdate->execute();
          }

          if ( !$osC_Database->isError() ) {
            
            if( $attrib_stock_left < 1 && STOCK_EMAIL_ALERT == '1') {
              $variants_group = $osC_Product->getVariants();
              foreach($variants_group as $group_id => $group_array) {
                if($group_array['variants_id'] == $products_variants_id) {
                  $variants_name = implode(',', array_values($group_array['groups_name']));
                  break; 
                }
              }
             self::outstockAlert($osC_Product->_data['name'], $attrib_stock_left, 'with the variant of ' . $variants_name);
            }
          
            //shouldn't disable the product
//            if ((STOCK_ALLOW_CHECKOUT == '-1') && ($attrib_stock_left < 1)) {
//              $QstockUpdate = $osC_Database->query('update :table_products_variants set products_status = 0 where products_variants_id = :products_variants_id');
//              $QstockUpdate->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
//              $QstockUpdate->bindInt(':products_variants_id', $products_variants_id);
//              $QstockUpdate->execute();
//
//              if ( $osC_Database->isError() ) {
//                $error = true;
//              }
//            }
          }
        }

        if ($error === false) {
          $Qstock = $osC_Database->query('select products_name, products_quantity from :table_products p, :table_products_description pd where p.products_id = pd.products_id and p.products_id = :products_id and pd.language_id = :language_id');
          $Qstock->bindTable(':table_products', TABLE_PRODUCTS);
          $Qstock->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qstock->bindTable(':language_id', $osC_Language->getID());
          $Qstock->bindInt(':products_id', $products_id);
          $Qstock->execute();

          if ($Qstock->numberOfRows() > 0) {
            $stock_left = $Qstock->valueInt('products_quantity');
            $stock_left = $stock_left - $products_quantity;

            $Qupdate = $osC_Database->query('update :table_products set products_quantity = :products_quantity where products_id = :products_id');
            $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
            $Qupdate->bindInt(':products_quantity', $stock_left);
            $Qupdate->bindInt(':products_id', $products_id);
            $Qupdate->setLogging($_SESSION['module'], $orders_id);
            $Qupdate->execute();

            if ( !$osC_Database->isError() ) {
              if( $stock_left < 1 && STOCK_EMAIL_ALERT == '1') {
                self::outstockAlert($Qstock->value('products_name'), $stock_left);
              }
              
              //shouldn't disable the product
//              if ((STOCK_ALLOW_CHECKOUT == '-1') && ($stock_left < 1)) {
//                $Qupdate = $osC_Database->query('update :table_products set products_status = 0 where products_id = :products_id');
//                $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
//                $Qupdate->bindInt(':products_id', $products_id);
//                $Qupdate->setLogging($_SESSION['module'], $orders_id);
//                $Qupdate->execute();
//
//                if ( $osC_Database->isError() ) {
//                  $error = true;
//                }
//              }
            }
          }
        }
      }

      if ( $error === false ) {
        $Qupdate = $osC_Database->query('update :table_products set products_ordered = products_ordered + :products_ordered where products_id = :products_id');
        $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
        $Qupdate->bindInt(':products_ordered', $products_quantity);
        $Qupdate->bindInt(':products_id', $products_id);
        $Qupdate->setLogging($_SESSION['module'], $orders_id);
        $Qupdate->execute();

        if ( $osC_Database->isError() ) {
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

    function outstockAlert($products_name, $stock, $variants_name = '') {
      require_once('includes/classes/email_template.php');
      $email_template = toC_Email_Template::getEmailTemplate('out_of_stock_alerts');
      $email_template->setData($products_name, $stock, $variants_name);
      $email_template->buildMessage();
      $email_template->sendEmail();
    }
    
    function restock($orders_id, $orders_products_id, $products_id, $products_quantity){
      global $osC_Database;

      if (STOCK_LIMITED == '1') {
        $error = false;

        $osC_Database->startTransaction();

        $Qupdate = $osC_Database->query('update :table_products set products_quantity = products_quantity + :products_quantity, products_ordered = products_ordered - :products_ordered where products_id = :products_id');
        $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
        $Qupdate->bindInt(':products_quantity', $products_quantity);
        $Qupdate->bindInt(':products_ordered', $products_quantity);
        $Qupdate->bindInt(':products_id', $products_id);
        $Qupdate->setLogging($_SESSION['module'], $orders_id);
        $Qupdate->execute();

        if (!$osC_Database->isError()) {
          $Qcheck = $osC_Database->query('select products_quantity from :table_products where products_id = :products_id and products_status = 0');
          $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->execute();

          if (($Qcheck->numberOfRows() === 1) && ($products_quantity > 0)) {
            $Qstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id = :products_id');
            $Qstatus->bindTable(':table_products', TABLE_PRODUCTS);
            $Qstatus->bindInt(':products_id', $products_id);
            $Qstatus->setLogging($_SESSION['module'], $orders_id);
            $Qstatus->execute();

            if ($osC_Database->isError() === true) {
              $error = true;
            }
          }
        }

//restock products variant details
        if ($error === false) {
          $Qvariants = $osC_Database->query('select products_variants_groups_id,  products_variants_values_id from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id order by products_variants_groups_id');
          $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
          $Qvariants->bindInt(':orders_id', $orders_id);
          $Qvariants->bindInt(':orders_products_id', $orders_products_id);
          $Qvariants->execute();

          if($Qvariants->numberOfRows() > 0){
            $variants = array();
            while ($Qvariants->next()){
              $variants[$Qvariants->value('products_variants_groups_id')] = $Qvariants->value('products_variants_values_id');
            }
            $Qvariants->freeResult();

            $osC_Product = new osC_Product($products_id);
            $products_variants_id = $osC_Product->getProductVariantsId($variants);

            $QstockUpdate = $osC_Database->query('update :table_products_variants set products_quantity = products_quantity + :products_quantity where products_variants_id = :products_variants_id');
            $QstockUpdate->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $QstockUpdate->bindInt(':products_quantity', $products_quantity);
            $QstockUpdate->bindInt(':products_variants_id', $products_variants_id);
            $QstockUpdate->setLogging($_SESSION['module'], $orders_id);
            $QstockUpdate->execute();

            if ($osC_Database->isError() === true) {
              $error = true;
            }

            $Qcheck = $osC_Database->query('select products_quantity from :table_products_variants where products_variants_id = :products_variants_id and products_status = 0');
            $Qcheck->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $Qcheck->bindInt(':products_variants_id', $products_variants_id);
            $Qcheck->execute();

            if ($error === false) {
              if ( ($Qcheck->numberOfRows() === 1) && ($Qcheck->valueInt('products_quantity') > 0) ) {
                $QattribStatus = $osC_Database->query('update :table_products_variants set products_status = 1 where products_variants_id = :products_variants_id');
                $QattribStatus->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
                $QattribStatus->bindInt(':products_variants_id', $products_variants_id);
                $QattribStatus->setLogging($_SESSION['module'], $orders_id);
                $QattribStatus->execute();

                if ($osC_Database->isError() === true) {
                  $error = true;
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
      }else{
        return true;
      }
    }
  }
?>