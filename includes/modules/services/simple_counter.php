<?php
/*
  $Id: simple_counter.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_simple_counter {
    function start() {
      global $osC_Database, $messageStack;

      $Qcounter = $osC_Database->query('select startdate, counter from :table_counter');
      $Qcounter->bindTable(':table_counter', TABLE_COUNTER);
      $Qcounter->execute();

      if ($Qcounter->numberOfRows()) {
        $counter_startdate = $Qcounter->value('startdate');
        $counter_now = $Qcounter->valueInt('counter') + 1;

        $Qcounterupdate = $osC_Database->query('update :table_counter set counter = counter+1');
        $Qcounterupdate->bindTable(':table_counter', TABLE_COUNTER);
        $Qcounterupdate->execute();

        $Qcounterupdate->freeResult();
      } else {
        $counter_startdate = osC_DateTime::getNow();
        $counter_now = 1;

        $Qcounterupdate = $osC_Database->query('insert into :table_counter (startdate, counter) values (:start_date, 1)');
        $Qcounterupdate->bindTable(':table_counter', TABLE_COUNTER);
        $Qcounterupdate->bindValue(':start_date', $counter_startdate);
        $Qcounterupdate->execute();

        $Qcounterupdate->freeResult();
      }

      $Qcounter->freeResult();

      return true;
    }

    function stop() {
      return true;
    }
  }
?>
