<?php
/*
  $Id: address_book_details.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<ol>

<?php
  if (ACCOUNT_GENDER > -1) {
    $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                          array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_gender'), 'gender1', 'fake', (ACCOUNT_GENDER > 0)) . osc_draw_radio_field('gender', $gender_array, (isset($Qentry) ? $Qentry->value('entry_gender') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getGender() : null))); ?></li>

<?php
  }
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_first_name'), 'firstname', 'firstname', true) . osc_draw_input_field('firstname', (isset($Qentry) ? $Qentry->value('entry_firstname') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getFirstName() : null))); ?></li>
  <li><?php echo osc_draw_label($osC_Language->get('field_customer_last_name'), 'lastname', 'lastname', true) . osc_draw_input_field('lastname', (isset($Qentry) ? $Qentry->value('entry_lastname') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getLastName() : null))); ?></li>

<?php
  if (ACCOUNT_COMPANY > -1) {
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_company'), 'company', 'company', (ACCOUNT_COMPANY > 0)) . osc_draw_input_field('company', (isset($Qentry) ? $Qentry->value('entry_company') : null)); ?></li>

<?php
  }
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_street_address'), 'street_address', 'street_address', true) . osc_draw_input_field('street_address', (isset($Qentry) ? $Qentry->value('entry_street_address') : null)); ?></li>

<?php
  if (ACCOUNT_SUBURB > -1) {
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_suburb'), 'suburb', 'suburb', (ACCOUNT_SUBURB > 0)) . osc_draw_input_field('suburb', (isset($Qentry) ? $Qentry->value('entry_suburb') : null)); ?></li>

<?php
  }

  if (ACCOUNT_POST_CODE > -1) {
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_post_code'), 'postcode', 'postcode', (ACCOUNT_POST_CODE > 0)) . osc_draw_input_field('postcode', (isset($Qentry) ? $Qentry->value('entry_postcode') : null)); ?></li>

<?php
  }
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_city'), 'city', 'city', true) . osc_draw_input_field('city', (isset($Qentry) ? $Qentry->value('entry_city') : null)); ?></li>

  <li>

<?php
  echo osc_draw_label($osC_Language->get('field_customer_country'), 'country', 'country', true);

  $countries_array = array(array('id' => '',
                                 'text' => $osC_Language->get('pull_down_default')));

  foreach (osC_Address::getCountries() as $country) {
    $countries_array[] = array('id' => $country['id'],
                               'text' => $country['name']);
  }
  
  echo osc_draw_pull_down_menu('country', $countries_array, (isset($Qentry) ? $Qentry->valueInt('entry_country_id') : STORE_COUNTRY));
?>

  </li>
  
<?php
  if (ACCOUNT_STATE > -1) {
?>

  <li>

<?php
    echo osc_draw_label($osC_Language->get('field_customer_state'), 'state', 'state', (ACCOUNT_STATE > 0));

    if ( (isset($_GET['new']) && ($_GET['new'] == 'save')) || (isset($_GET['edit']) && ($_GET['edit'] == 'save')) || (isset($_GET[$osC_Template->getModule()]) && ($_GET[$osC_Template->getModule()] == 'process')) ) {
      if ($entry_state_has_zones === true) {
        $Qzones = $osC_Database->query('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_country_id', $_POST['country']);
        $Qzones->execute();

        $zones_array = array();
        while ($Qzones->next()) {
          $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
        }

        echo '<span id="state-container">' . osc_draw_pull_down_menu('state', $zones_array) . '</span>';
      } else {
        echo '<span id="state-container">' . osc_draw_input_field('state') . '</span>';
      }
    } else {
      if (isset($Qentry)) {
        $zone = $Qentry->value('entry_state');

        if ($Qentry->valueInt('entry_zone_id') > 0) {
          $zone = osC_Address::getZoneName($Qentry->valueInt('entry_zone_id'));
        }
      }else {
        $Qzones = $osC_Database->query('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_country_id', STORE_COUNTRY);
        $Qzones->execute();
  
        $zones_array = array();
        while ($Qzones->next()) {
          $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
        }
        
        $Qzones->freeResult();
      }
      
      if (isset($zones_array) && !empty($zones_array)) {
        echo '<span id="state-container">' . osc_draw_pull_down_menu('state', $zones_array) . '</span>';
      }else {
        echo '<span id="state-container">' . osc_draw_input_field('state', (isset($Qentry) ? $zone : null)) . '</span>';
      }
    }
?>

  </li>

<?php
  }
?>

<?php
  if (ACCOUNT_TELEPHONE > -1) {
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_telephone_number'), 'telephone', 'telephone', (ACCOUNT_TELEPHONE > 0)) . osc_draw_input_field('telephone', (isset($Qentry) ? $Qentry->value('entry_telephone') : null)); ?></li>

<?php
  }

  if (ACCOUNT_FAX > -1) {
?>

  <li><?php echo osc_draw_label($osC_Language->get('field_customer_fax_number'), 'fax', 'fax', (ACCOUNT_FAX > 0)) . osc_draw_input_field('fax', (isset($Qentry) ? $Qentry->value('entry_fax') : null)); ?></li>

<?php
  }

  if ($osC_Customer->hasDefaultAddress() && ((isset($_GET['edit']) && ($osC_Customer->getDefaultAddressID() != $_GET['address_book'])) || isset($_GET['new'])) ) {
?>

  <li><?php echo osc_draw_checkbox_field('primary', array(array('id' => 'on', 'text' => $osC_Language->get('set_as_primary'))), false); ?></li>

<?php
  }
?>

</ol>

<script type="text/javascript">
  window.addEvent('domready', function() {
    var addressBook = new AddressBook({
      remoteUrl: '<?php echo osc_href_link('json.php', null, 'SSL', false, false, true); ?>',
      sessionName: '<?php echo $osC_Session->getName(); ?>',
      sessionId: '<?php echo $osC_Session->getID(); ?>'
    });
  });
</script>