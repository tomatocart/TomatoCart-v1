<?php
/*
  $Id: xml.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include(dirname(__FILE__) . '/../../ext/phpxml/xml.php');

  class osC_XML {
    var $_xml,
        $_encoding;

    function osC_XML($xml, $encoding = '') {
      $this->_xml = $xml;

      if (!empty($encoding)) {
        $this->_encoding = $encoding;
      }
    }

    function toArray() {
      return XML_unserialize($this->_xml);
    }

    function toXML() {
      return XML_serialize($this->_xml, $this->_encoding);
    }
  }
?>
