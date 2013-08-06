<?php
/*
  $Id: departments.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Departments {
  
    function &getListing() {
      global $osC_Database, $osC_Language;

      $Qdepartments = $osC_Database->query('select d.departments_id, departments_title, departments_email_address, departments_description from :table_departments d inner join :table_departments_description pd on d.departments_id = pd.departments_id and pd.languages_id = :language_id order by departments_title');
      $Qdepartments->bindTable(':table_departments', TABLE_DEPARTMENTS);
      $Qdepartments->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
      $Qdepartments->bindInt(':language_id', $osC_Language->getID());
      $Qdepartments->execute();    
    
      return $Qdepartments; 
    }
  }
?>
