<?php
/*
  $Id: departments.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class toC_Departments {
  
    function getData($id) {
      global $osC_Database;

      $Qdepartment = $osC_Database->query('select departments_email_address from :table_departments where departments_id = :id');
      $Qdepartment->bindTable(':table_departments', TABLE_DEPARTMENTS);
      $Qdepartment->bindInt(':id', $id);
      $Qdepartment->execute();

      $data = $Qdepartment->toArray();

      $Qdepartment->freeResult();
      
      $Qdescription = $osC_Database->query('select departments_title, departments_description, languages_id from :table_departmentsdescription where departments_id = :id');
      $Qdescription->bindTable(':table_departmentsdescription', TABLE_DEPARTMENTS_DESCRIPTION);
      $Qdescription->bindInt(':id', $id);
      $Qdescription->execute();
      
      while ($Qdescription->next()) {
        $data['departments_title[' . $Qdescription->valueInt('languages_id') . ']'] = $Qdescription->value('departments_title');
        $data['departments_description[' . $Qdescription->valueInt('languages_id') . ']'] = $Qdescription->value('departments_description');
      }

      $Qdescription->freeResult();

      return $data;
    }
  
    function save($id = null, $data) {
      global $osC_Database, $osC_Language;
      
      $error = false;
      
      $osC_Database->startTransaction();
      
      if ( is_numeric($id) ) {
        $Qdepartment = $osC_Database->query('update :table_departments set departments_email_address = :email_address where departments_id = :id');
        $Qdepartment->bindInt(':id', $id);
      } else {
        $Qdepartment = $osC_Database->query('insert into :table_departments (departments_email_address) values (:email_address)');
      }
      $Qdepartment->bindTable(':table_departments', TABLE_DEPARTMENTS);
      $Qdepartment->bindValue(':email_address', $data['email_address']);
      $Qdepartment->execute();
      
      if ($osC_Database->isError()) {
        $error = true;
      } else {
        if (is_numeric($id)) {
          $departments_id = $id;
        } else {
          $departments_id = $osC_Database->nextID();
        }
        
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qdescription = $osC_Database->query('update :table_departments_description set departments_title = :departments_title, departments_description = :departments_description where departments_id = :id and languages_id = :language_id');
          } else {
            $Qdescription = $osC_Database->query('insert into :table_departments_description (departments_id, languages_id, departments_title, departments_description) values (:id, :language_id, :departments_title, :departments_description)');
          }

          $Qdescription->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
          $Qdescription->bindInt(':id', $departments_id);
          $Qdescription->bindInt(':language_id', $l['id']);
          $Qdescription->bindValue(':departments_title', $data['title'][$l['id']]);
          $Qdescription->bindValue(':departments_description', $data['description'][$l['id']]);
          $Qdescription->setLogging($_SESSION['module'], $departments_id);
          $Qdescription->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }
      
      if ($error === false) {
        $osC_Database->commitTransaction();
        
        return true;
      }
      
      $osC_Database->rollbackTransaction();
      
      return false;
    }
    
    function delete($id) {
      global $osC_Database;
      
      $osC_Database->startTransaction();
      $error = false;

      $Qdep = $osC_Database->query('delete from :table_departments where departments_id = :id');
      $Qdep->bindTable(':table_departments', TABLE_DEPARTMENTS);
      $Qdep->bindInt(':id', $id);
      $Qdep->setLogging($_SESSION['module'], $id);
      $Qdep->execute();

      if ( !$osC_Database->isError() ) {
        $Qdep = $osC_Database->query('delete from :table_departments_description where departments_id = :id');
        $Qdep->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
        $Qdep->bindInt(':id', $id);
        $Qdep->setLogging($_SESSION['module'], $id);
        $Qdep->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      } else {
         $error = true;
      }
      
      if ($error === false) {
        $osC_Database->commitTransaction();
         
        return true;
      }
      
      $osC_Database->rollbackTransaction();
      
      return false;
    }
  }
?>