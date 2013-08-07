<?php
/*
  $Id: weight.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Weight {
    var $weight_classes = array(),
        $precision;

// class constructor
    function osC_Weight($precision = '2') {
      $this->precision = $precision;

      $this->prepareRules();
    }

    function getTitle($id) {
      global $osC_Database, $osC_Language;

      $Qweight = $osC_Database->query('select weight_class_title from :table_weight_class where weight_class_id = :weight_class_id and language_id = :language_id');
      $Qweight->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qweight->bindInt(':weight_class_id', $id);
      $Qweight->bindInt(':language_id', $osC_Language->getID());
      $Qweight->execute();

      return $Qweight->value('weight_class_title');
    }

    function prepareRules() {
      global $osC_Database, $osC_Language;

      $Qrules = $osC_Database->query('select r.weight_class_from_id, r.weight_class_to_id, r.weight_class_rule from :table_weight_class_rules r, :table_weight_class c where c.weight_class_id = r.weight_class_from_id');
      $Qrules->bindTable(':table_weight_class_rules', TABLE_WEIGHT_CLASS_RULES);
      $Qrules->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qrules->setCache('weight-rules');
      $Qrules->execute();

      while ($Qrules->next()) {
        $this->weight_classes[$Qrules->valueInt('weight_class_from_id')][$Qrules->valueInt('weight_class_to_id')] = $Qrules->value('weight_class_rule');
      }

      $Qclasses = $osC_Database->query('select weight_class_id, weight_class_key, weight_class_title from :table_weight_class where language_id = :language_id');
      $Qclasses->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qclasses->bindInt(':language_id', $osC_Language->getID());
      $Qclasses->setCache('weight-classes');
      $Qclasses->execute();

      while ($Qclasses->next()) {
        $this->weight_classes[$Qclasses->valueInt('weight_class_id')]['key'] = $Qclasses->value('weight_class_key');
        $this->weight_classes[$Qclasses->valueInt('weight_class_id')]['title'] = $Qclasses->value('weight_class_title');
      }

      $Qrules->freeResult();
      $Qclasses->freeResult();
    }

    function convert($value, $unit_from, $unit_to) {
      global $osC_Language;

      if ($unit_from == $unit_to) {
        return $value;
      } else {
        return $value * $this->weight_classes[(int)$unit_from][(int)$unit_to];
      }
    }

    function display($value, $class) {
      global $osC_Language;

      return number_format($value, (int)$this->precision, $osC_Language->getNumericDecimalSeparator(), $osC_Language->getNumericThousandsSeparator()) . $this->weight_classes[$class]['key'];
    }

    function getClasses() {
      global $osC_Database, $osC_Language;

      $weight_class_array = array();

      $Qclasses = $osC_Database->query('select weight_class_id, weight_class_title from :table_weight_class where language_id = :language_id order by weight_class_title');
      $Qclasses->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qclasses->bindInt(':language_id', $osC_Language->getID());
      $Qclasses->execute();

      while ($Qclasses->next()) {
        $weight_class_array[] = array('id' => $Qclasses->valueInt('weight_class_id'),
                                      'title' => $Qclasses->value('weight_class_title'));
      }

      return $weight_class_array;
    }
  }
?>
