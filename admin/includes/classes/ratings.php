<?php
/*
  $Id: ratings.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class toC_Ratings_Admin {
  	function getData($id) {
  		global $toC_Json, $osC_Language, $osC_Database;
  		
  		$Qrating = $osC_Database->query('select r.status, rd.ratings_text, rd.languages_id from :table_ratings r inner join :table_ratings_description rd on r.ratings_id = rd.ratings_id and r.ratings_id = :ratings_id');
  		$Qrating->bindTable(':table_ratings', TABLE_RATINGS);
  		$Qrating->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
  		$Qrating->bindInt(':ratings_id', $id);
  		$Qrating->execute();
  		
  		$data = array();
  		while( $Qrating->next() ) {
  			$data['status'] = $Qrating->valueInt('status');
  			$data['ratings_text[' . $Qrating->valueInt('languages_id') .']'] = $Qrating->value('ratings_text');
  		}

  		$Qrating->freeResult();
  		
      return $data;  		
  	}
  	
    function save($id = null, $data) {
      global $osC_Database, $osC_Language;
      
      $error = false;
      
      $osC_Database->startTransaction();
       
      if ( is_numeric($id) ) {
        $Qrating = $osC_Database->query('update :table_ratings set status = :status where ratings_id = :ratings_id');
        $Qrating->bindInt(':ratings_id', $id);
      }else {
        $Qrating = $osC_Database->query('insert into :table_ratings (status) values (:status)');
      }
      $Qrating->bindTable(':table_ratings', TABLE_RATINGS);
      $Qrating->bindInt(':status', $data['status']);
      $Qrating->execute();
      
      if ( !$osC_Database->isError() ) {
      	$ratings_id = is_numeric($id) ? $id : $osC_Database->nextID();
      	
      	foreach($osC_Language->getAll() as $l) {
      		if ( is_numeric($id) ) {
      			$Qrd = $osC_Database->query('update :table_ratings_description set ratings_text = :ratings_text where ratings_id = :ratings_id and languages_id = :languages_id');
      		}else {
      			$Qrd = $osC_Database->query('insert into :table_ratings_description (ratings_id, languages_id, ratings_text) values (:ratings_id, :languages_id, :ratings_text)');
      		}
      		
      		$Qrd->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      		$Qrd->bindInt(':ratings_id', $ratings_id);
      		$Qrd->bindInt(':languages_id', $l['id']);
      		$Qrd->bindValue(':ratings_text', $data['ratings_text'][$l['id']]);
      		$Qrd->execute();
      		
      		if ( $osC_Database->isError() ) {
      			$error =true;
      			
      			break;
      		}
      	}
      }
      
      if ( $error === false ) {
      	$osC_Database->commitTransaction();
      	
      	return true;
      }
      
      $osC_Database->rollbackTransaction();
      
      return false;
    }
    
    function delete($id) {
    	global $osC_Database, $osC_Language;
    	
    	$error = false;
      
      $osC_Database->startTransaction();
    	
      $Qdelete = $osC_Database->query('delete from :table_ratings where ratings_id = :ratings_id');
      $Qdelete->bindTable(':table_ratings', TABLE_RATINGS);
      $Qdelete->bindInt(':ratings_id', $id);
      $Qdelete->execute();
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }

    	if ($error === false){
        $Qrd = $osC_Database->query('delete from :table_ratings_description where ratings_id = :ratings_id');
        $Qrd->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
        $Qrd->bindInt(':ratings_id', $id);
        $Qrd->execute();
    	        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
    	}
    	
      if ($error === false){
        $Qcr = $osC_Database->query('delete from :table_categories_ratings where ratings_id = :ratings_id');
        $Qcr->bindTable(':table_categories_ratings', TABLE_CATEGORIES_RATINGS);
        $Qcr->bindInt(':ratings_id', $id);
        $Qcr->execute();
              
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
          
      if ($error === false){
        $Qratings = $osC_Database->query('delete from :table_customers_ratings where ratings_id = :ratings_id');
        $Qratings->bindTable(':table_customers_ratings', TABLE_CUSTOMERS_RATINGS);
        $Qratings->bindInt(':ratings_id', $id);
        $Qratings->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
    	if ($error == false) {
    		$osC_Database->commitTransaction();
        
        return true;
    	}
    	
      $osC_Database->rollbackTransaction();
      
      return false;
    }
    
    function setStatus($id, $status) {
    	global $toC_Json, $osC_Database, $osC_Language;
    	
      $Qupdate = $osC_Database->query('update :table_ratings set status = :status where ratings_id = :ratings_id');
      $Qupdate->bindTable(':table_ratings', TABLE_RATINGS);
      $Qupdate->bindInt(':ratings_id', $id);
      $Qupdate->bindInt(':status', $status);
      $Qupdate->execute();
     
      if (!$osC_Database->isError()) {
        
        return true;
      }
      
      return false;
    }
    
    function getText($ratings_id) {
      global $osC_Database, $osC_Language;
      
      $Qtext = $osC_Database->query('select ratings_text from :table_ratings_description where languages_id = :languages_id and ratings_id = :ratings_id');
      $Qtext->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qtext->bindInt(':languages_id', $osC_Language->getID());
      $Qtext->bindInt(':ratings_id', $ratings_id);
      $Qtext->execute();
      
      while ( $Qtext->next() ) {
	      $ratings_text = $Qtext->value('ratings_text');
      }
      $Qtext->freeResult();
      
      return $ratings_text;
    }
    
    function getAllText($ratings_id) {
      global $osC_Database, $osC_Language;
      
      $Qtext = $osC_Database->query('select ratings_text, languages_id from :table_ratings_description where ratings_id = :ratings_id');
      $Qtext->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qtext->bindInt(':ratings_id', $ratings_id);
      $Qtext->execute();
      
      $ratings_desc = array();
      while ( $Qtext->next() ) {
      	$languages_id = $Qtext->valueInt('languages_id');
        $ratings_desc[$languages_id] = $Qtext->value('ratings_text');
      }
      $Qtext->freeResult();
      
      return $ratings_desc;
    }
    
    function saveLanguage($ratings_id,$languages_id,$ratings_text) {
      global $osC_Database;
      
      $Qratings = $osC_Database->query('insert into :table_ratings_description (ratings_id, languages_id, ratings_text) values(:ratings_id, :languages_id, :ratings_text)');
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':ratings_id', $ratings_id);
      $Qratings->bindInt(':languages_id', $languages_id);
      $Qratings->bindValue(':ratings_text', $ratings_text);
      $Qratings->execute();
      
    }
    
    function updateLanguage($ratings_id,$languages_id,$ratings_text) {
      global $osC_Database;
      
      $Qratings = $osC_Database->query('update :table_ratings_description set languages_id = :languages_id, ratings_text = :ratings_text where ratings_id = :ratings_id');
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':ratings_id', $ratings_id);
      $Qratings->bindInt(':languages_id', $languages_id);
      $Qratings->bindValue(':ratings_text', $ratings_text);
      $Qratings->execute();
      
    }
    
    function deleteLanguage($ratings_id){
    	global $osC_Database;
    	
      $Qratings = $osC_Database->query('delete from :table_ratings_description where ratings_id = :ratings_id');
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':ratings_id', $ratings_id);
      $Qratings->execute();
    }
    
    function deleteCategoriesRating($ratings_id){
      global $osC_Database;
      
      $Qratings = $osC_Database->query('delete from :table_categories_ratings where ratings_id = :ratings_id');
      $Qratings->bindTable(':table_categories_ratings', TABLE_CATEGORIES_RATINGS);
      $Qratings->bindInt(':ratings_id', $ratings_id);
      $Qratings->execute();
    }
    
    function deleteReviewsRating($ratings_id){
      global $osC_Database;
      
      $Qratings = $osC_Database->query('delete from :table_customers_ratings where ratings_id = :ratings_id');
      $Qratings->bindTable(':table_customers_ratings', TABLE_CUSTOMERS_RATINGS);
      $Qratings->bindInt(':ratings_id', $ratings_id);
      $Qratings->execute();
    }
    
  }
?>