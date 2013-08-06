<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
*/
?>

    <?php
        if (ACCOUNT_GENDER > -1) {
            $gender = (isset($Qentry) ? $Qentry->value('entry_gender') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getGender() : null));
            if (!isset($gender)) {
                $gender = isset($_POST['gender']) ? $_POST['gender'] : 'f';
            }
    ?>
    <div class="control-group">
        <label class="control-label" for="gender1"><?php echo $osC_Language->get('field_customer_gender') . ((ACCOUNT_GENDER > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<label class="radio inline" for="gender1"><input type="radio" value="m" id="gender1" name="gender" <?php echo (isset($gender) && $gender == 'm') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_male'); ?></label>
        	<label class="radio inline" for="gender2"><input type="radio" value="f" id="gender2" name="gender" <?php echo (isset($gender) && $gender == 'f') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_female'); ?></label>
        </div>
    </div>
    <?php
        }
    ?>

    <div class="control-group">
        <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_first_name'); ?><em>*</em></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('firstname', (isset($Qentry) ? $Qentry->value('entry_firstname') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getFirstName() : null))); ?>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_last_name'); ?><em>*</em></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('lastname', (isset($Qentry) ? $Qentry->value('entry_lastname') : (!$osC_Customer->hasDefaultAddress() ? $osC_Customer->getLastName() : null))); ?>
        </div>
    </div>

    <?php
        if (ACCOUNT_COMPANY > -1) {
    ?>

    <div class="control-group">
        <label class="control-label" for="company"><?php echo $osC_Language->get('field_customer_company') . ((ACCOUNT_COMPANY > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('company', (isset($Qentry) ? $Qentry->value('entry_company') : null)); ?>
        </div>
    </div>
    
    <?php
        }
    ?>
    
    <div class="control-group">
        <label class="control-label" for="street_address"><?php echo $osC_Language->get('field_customer_street_address'); ?><em>*</em></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('street_address', (isset($Qentry) ? $Qentry->value('entry_street_address') : null)); ?>
        </div>
    </div>

<?php
    if (ACCOUNT_SUBURB > -1) {
?>

    <div class="control-group">
        <label class="control-label" for="suburb"><?php echo $osC_Language->get('field_customer_suburb') . ((ACCOUNT_SUBURB > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('suburb', (isset($Qentry) ? $Qentry->value('entry_suburb') : null)); ?>
        </div>
    </div>
    
<?php
    }

    if (ACCOUNT_POST_CODE > -1) {
?>

    <div class="control-group">
        <label class="control-label" for="postcode"><?php echo $osC_Language->get('field_customer_post_code') . ((ACCOUNT_POST_CODE > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('postcode', (isset($Qentry) ? $Qentry->value('entry_postcode') : null)); ?>
        </div>
    </div>

<?php
    }
?>

    <div class="control-group">
        <label class="control-label" for="city"><?php echo $osC_Language->get('field_customer_city'); ?><em>*</em></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('city', (isset($Qentry) ? $Qentry->value('entry_city') : null)); ?>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="country"><?php echo $osC_Language->get('field_customer_country'); ?><em>*</em></label>
        <div class="controls">
            <?php
                $countries_array = array(array('id' => '',
                                             'text' => $osC_Language->get('pull_down_default')));
                
                foreach (osC_Address::getCountries() as $country) {
                    $countries_array[] = array('id' => $country['id'],
                                               'text' => $country['name']);
                }
                
                echo osc_draw_pull_down_menu('country', $countries_array, (isset($Qentry) ? $Qentry->valueInt('entry_country_id') : STORE_COUNTRY));
            ?>
        </div>
    </div>
  
<?php
    if (ACCOUNT_STATE > -1) {
?>
    <div class="control-group">
        <label class="control-label" for="state"><?php echo $osC_Language->get('field_customer_state') . ((ACCOUNT_STATE > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
            <?php
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
                        
                        echo osc_draw_pull_down_menu('state', $zones_array);
                    } else {
                        echo osc_draw_input_field('state');
                    }
                } else {
                    if (isset($Qentry)) {
                        $zone = $Qentry->value('entry_state');
                        
                        if ($Qentry->valueInt('entry_zone_id') > 0) {
                            $zone = osC_Address::getZoneName($Qentry->valueInt('entry_zone_id'));
                        }
                    }
                    
                    echo osc_draw_input_field('state', (isset($Qentry) ? $zone : null));
                }
            ?>
        </div>
    </div>
<?php
    }
?>

<?php
    if (ACCOUNT_TELEPHONE > -1) {
?>

    <div class="control-group">
        <label class="control-label" for="telephone"><?php echo $osC_Language->get('field_customer_telephone_number') . ((ACCOUNT_TELEPHONE > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('telephone', (isset($Qentry) ? $Qentry->value('entry_telephone') : null)); ?>
        </div>
    </div>

<?php
    }

    if (ACCOUNT_FAX > -1) {
?>

    <div class="control-group">
        <label class="control-label" for="fax"><?php echo $osC_Language->get('field_customer_fax_number') . ((ACCOUNT_FAX > 0) ? '<em>*</em>' : ''); ?></label>
        <div class="controls">
        	<?php echo osc_draw_input_field('fax', (isset($Qentry) ? $Qentry->value('entry_fax') : null)); ?>
        </div>
    </div>

<?php
    }

    if ($osC_Customer->hasDefaultAddress() && ((isset($_GET['edit']) && ($osC_Customer->getDefaultAddressID() != $_GET['address_book'])) || isset($_GET['new'])) ) {
?>

    <div class="control-group">
        <div class="controls">
        	<label class="checkbox"  for="privacy_conditions">
        	    <?php echo osc_draw_checkbox_field('primary', array(array('id' => 'on', 'text' => $osC_Language->get('set_as_primary'))), false); ?>
        	</label>
        </div>
    </div>

<?php
    }
?>