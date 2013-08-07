<?php
/*
  $Id: billing_address_details.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $address = $osC_ShoppingCart->getBillingAddress();
  
  if(isset($address['id']) && ($address['id'] != '-1')) {
    $address = array();
  }

  $email_address = isset($address['email_address']) ? $address['email_address'] : null;
  $gender = isset($address['gender']) ? $address['gender'] : null;
  $firstname = isset($address['firstname']) ? $address['firstname'] : null;
  $lastname = isset($address['lastname']) ? $address['lastname'] : null;
  $company = isset($address['company']) ? $address['company'] : null;
  $street_address = isset($address['street_address']) ? $address['street_address'] : null;
  $suburb = isset($address['suburb']) ? $address['suburb'] : null;
  $postcode = isset($address['postcode']) ? $address['postcode'] : null;
  $city = isset($address['city']) ? $address['city'] : null;
  $state = isset($address['state']) ? $address['state'] : null;
  $country_id = isset($address['country_id']) ? $address['country_id'] : STORE_COUNTRY;
  $telephone = isset($address['telephone_number']) ? $address['telephone_number'] : null;
  $fax = isset($address['fax']) ? $address['fax'] : null;
  $ship_to_this_address = (isset($address['ship_to_this_address']) && $address['ship_to_this_address'] == '1') ? true : false;
  
  $create_billing_address = false;
  if (isset($address['id']) && ($address['id'] == '-1')) {
    $create_billing_address = true;
  } else if ($osC_Customer->isLoggedOn() && (osC_AddressBook::numberOfEntries() == 0)) {
    $create_billing_address = true;
  }
?>
<div class="moduleBox">
  <div class="content">
    <form name="billingAddressDetailsForm">
    <ol>

    <?php
      if ($osC_Customer->isLoggedOn() === false) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('email_address'), null, 'billing_email_address', true) . osc_draw_input_field('billing_email_address', $email_address); ?> </li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password'), null, 'billing_password', true) . osc_draw_password_field('billing_password'); ?></li>
      <li style="margin-bottom: 10px"><?php echo osc_draw_label($osC_Language->get('field_customer_password_confirmation'), null, 'billing_confirm_password', true) . osc_draw_password_field('billing_confirm_password'); ?></li>
    <?php
      }
    ?>
    
    <?php
    if ($osC_Customer->isLoggedOn() && osC_AddressBook::numberOfEntries() > 0) {
    ?>
    <li>
      <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
        <?php echo '<b>' . $osC_Language->get('please_select') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_east_south.gif'); ?>
      </div>
  
      <p style="margin-top: 0px;"><?php echo $osC_Language->get('choose_billing_address'); ?></p>
    </li>    
    <li style="margin-bottom: 10px">
    <?php
      $Qaddresses = osC_AddressBook::getListing();
      $addresses = array();
      
      while ($Qaddresses->next()) {
        $address = array('id' => $Qaddresses->valueInt('address_book_id'), 'text' => osC_Address::format($Qaddresses->toArray(), ', '));
        
        if ($Qaddresses->valueInt('address_book_id') == $Qaddresses->valueInt('default_address_id')) {
           array_unshift($addresses, $address);
        }else {
           array_push($addresses, $address);
        }
      }
      
      if($create_billing_address == null) {
        $create_billing_address = false;
      }
      
      echo osc_draw_pull_down_menu('sel_billing_address', $addresses);
    ?>
    </li>
    <?php
      }
    ?>

    <div id="billingAddressDetails" style="display: <?php echo ($osC_Customer->isLoggedOn() & $create_billing_address == false) ? 'none' : ''; ?>">
    <?php
      if (ACCOUNT_GENDER > -1) {
        $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                              array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_gender'), null, 'fake', (ACCOUNT_GENDER > 0)) . osc_draw_radio_field('billing_gender', $gender_array, $gender); ?></li>
    <?php
      }
    ?>
    
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_first_name'), null, 'billing_firstname', true) . osc_draw_input_field('billing_firstname', $firstname); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_last_name'), null, 'billing_lastname', true) . osc_draw_input_field('billing_lastname', $lastname); ?></li>

    <?php
      if (ACCOUNT_COMPANY > -1) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_company'), null, 'billing_company', (ACCOUNT_COMPANY > 0)) . osc_draw_input_field('billing_company', $company); ?></li>
    <?php
      }
    ?>
    
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_street_address'), null, 'billing_street_address', true) . osc_draw_input_field('billing_street_address', $street_address); ?></li>
    
    <?php
      if (ACCOUNT_SUBURB > -1) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_suburb'), null, 'billing_suburb', (ACCOUNT_SUBURB > 0)) . osc_draw_input_field('billing_suburb', $suburb); ?></li>
    <?php
      }
    ?>

    <?php
      if (ACCOUNT_POST_CODE > -1) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_post_code'), null, 'billing_postcode', (ACCOUNT_POST_CODE > 0)) . osc_draw_input_field('billing_postcode', $postcode); ?></li>
    <?php
      }
    ?>
    
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_city'), null, 'billing_city', true) . osc_draw_input_field('billing_city', $city); ?></li>
    
      <li>
    <?php
      echo osc_draw_label($osC_Language->get('field_customer_country'), null, 'billing_country', true);
    
      $countries_array = array(array('id' => '',
                                     'text' => $osC_Language->get('pull_down_default')));
    
      foreach (osC_Address::getCountries() as $country) {
        $countries_array[] = array('id' => $country['id'],
                                   'text' => $country['name']);
      }
    
      echo osc_draw_pull_down_menu('billing_country', $countries_array, $country_id, "class=country");
    ?>
      </li>
    
    <?php
      if (ACCOUNT_STATE > -1) {
    ?>
      <li id="billing-state">
    <?php
        echo osc_draw_label($osC_Language->get('field_customer_state'), null, 'state_ship', (ACCOUNT_STATE > 0));
    
        $Qzones = $osC_Database->query('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_country_id', $country_id);
        $Qzones->execute();
  
        $zones_array = array();
        while ($Qzones->next()) {
          $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
        }
    
        if (sizeof($zones_array) > 0) {
          echo osc_draw_pull_down_menu('billing_state', $zones_array, $state);
        } else {
          echo osc_draw_input_field('billing_state', $state);
        }
    ?>    
      </li>
    <?php
      }
    ?>

    <?php
      if (ACCOUNT_TELEPHONE > -1) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_telephone_number'), null, 'billing_telephone', (ACCOUNT_TELEPHONE > 0)) . osc_draw_input_field('billing_telephone', $telephone); ?></li>
    <?php
      }
    ?>

    <?php
      if (ACCOUNT_FAX > -1) {
    ?>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_fax_number'), null, 'billing_fax', (ACCOUNT_FAX > 0)) . osc_draw_input_field('billing_fax', $fax); ?></li>
    <?php
      }
    ?>
    </div>
    
      <li style="height:10px;line-height:10px">&nbsp;</li>
    <?php          
    if ($osC_Customer->isLoggedOn()) {
    ?>
       <li>  
      <?php 
        echo osc_draw_checkbox_field('create_billing_address', array(array('id' => '1', 'text' => $osC_Language->get('create_new_billing_address'))) , $create_billing_address); 
      ?>
      </li>    
    <?php 
    }      
      
    if ($osC_ShoppingCart->isVirtualCart() == false) {
    ?>
      <li><?php echo osc_draw_checkbox_field('ship_to_this_address', array(array('id' => '1', 'text' => $osC_Language->get('ship_to_this_address'))), $ship_to_this_address);?></li>    
    <?php 
    }      
    ?>    
    </ol>
    <div class="submitFormButtons" style="text-align: right;">
    <?php echo osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'), 'id="btnSaveBillingInformation" style="cursor: pointer"'); ?>
    </div>
  </div>
</div>