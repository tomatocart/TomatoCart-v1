  <?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/reviews.php');
  require_once('includes/classes/ratings.php');

  class toC_Json_Reviews {
  
    function listReviews() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qreviews = $osC_Database->query('select r.reviews_id, r.products_id, r.date_added, r.last_modified, r.reviews_rating, r.reviews_status, pd.products_name, l.code as languages_code from :table_reviews r left join :table_products_description pd on (r.products_id = pd.products_id and r.languages_id = pd.language_id), :table_languages l where r.languages_id = l.languages_id order by r.date_added desc');
      $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreviews->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qreviews->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qreviews->setExtBatchLimit($start, $limit);
      $Qreviews->execute();
  
      $record = array();
      while ( $Qreviews->next() ) {
        $record[] = array('reviews_id' => $Qreviews->value('reviews_id'),
                           'date_added' => osC_DateTime::getShort($Qreviews->value('date_added')),
                           'reviews_rating' => osc_image('../images/stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_from_5_stars'), $Qreviews->valueInt('reviews_rating'))),
                           'products_name' => $Qreviews->value('products_name'),
                           'reviews_status' => $Qreviews->valueInt('reviews_status'),
                           'code' => $osC_Language->showImage($Qreviews->value('languages_code')));         
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qreviews->getBatchSize(),
                        EXT_JSON_READER_ROOT => $record); 
                          
      echo $toC_Json->encode($response);
    }
    
    function loadReviews() {
      global $toC_Json;
      
      $data = osC_Reviews_Admin::getData( $_REQUEST['reviews_id'] );  
      $data['date_added'] = osC_DateTime::getShort($data['date_added']);
      
      $response = array('success' => true, 'data' => $data); 
     
      echo $toC_Json->encode($response);  
    }
    
    function saveReviews() {
      global $toC_Json, $osC_Language;
      
      $total = 0;
      $data = array('review' => $_REQUEST['reviews_text'], 'reviews_status' => $_REQUEST['reviews_status']);
      
      $ratings = array();
      foreach ($_REQUEST as $key => $value) {
        if (substr($key, 0, 13) == 'ratings_value') {
          $customers_ratings_id = substr($key, 13);
          
          $ratings[$customers_ratings_id] = $value;
          $total += $value;
        }
      }
      if (count($ratings) > 0) {
        $data['rating'] = $total / count($ratings);
        $data['ratings'] = $ratings;
      } else {
        $data['rating'] = $_REQUEST['detailed_rating'];
      }
      
      if ( osC_Reviews_Admin::save( $_REQUEST['reviews_id'], $data )) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteReview() {
      global $toC_Json, $osC_Language;
      
      $flag = 0;
      $total = 0;
      $a = array();
      $data = array('review' => $_REQUEST['reviews_text'], 'reviews_status' => $_REQUEST['reviews_status']);
      foreach ($_REQUEST as $key => $value) {
        if (substr($key,0,13) == 'ratings_value') {
          $a[$key] = $_REQUEST[$key];
          $total = $value+$total;
          $flag++;
        }
      }
      if ($flag > 0) {
        $data['rating'] = $total/count($a);
        $data['ratings_value'] = $a;
      } else {
        $data['rating'] = $_REQUEST['detailed_rating'];
      }
      
      if ( osC_Reviews_Admin::delete( $_REQUEST['reviews_id'] ) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
    
      echo $toC_Json->encode($response);
    }
    
    function deleteReviews() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $ids = explode(',', $_REQUEST['batch']);
     
      foreach ($ids as $id) {
        if (!osC_Reviews_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
     
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
        
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
 
      if ( isset($_REQUEST['reviews_id']) && osC_Reviews_Admin::setStatus($_REQUEST['reviews_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : 1)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
      
    function listRatings(){
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qratings = $osC_Database->query('select r.ratings_id, r.status, rd.ratings_text from :table_ratings r inner join :table_ratings_description rd on r.ratings_id = rd.ratings_id and rd.languages_id = :languages_id');
      $Qratings->bindTable(':table_ratings', TABLE_RATINGS);
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':languages_id', $osC_Language->getID());
      $Qratings->setExtBatchLimit($start, $limit);
      $Qratings->execute();
      
      $records = array();
      while ( $Qratings->next() ) {
        $records[] = array(
          'ratings_id' => $Qratings->valueInt('ratings_id'),
          'ratings_name' => $Qratings->value('ratings_text'),
          'status' => $Qratings->value('status')
        );
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qratings->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      $Qratings->freeResult();                  
     
      echo $toC_Json->encode($response);
    }
    
    function saveRatings(){
      global $toC_Json, $osC_Language;
      
      $ratings_id = isset($_REQUEST['ratings_id']) ? $_REQUEST['ratings_id'] : null;
      
      $data = array('status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : null);
      foreach ( $osC_Language->getAll() as $language ) {
        $data['ratings_text'][$language['id']] = $_REQUEST['ratings_text'][$language['id']];
      }
      
      if ( toC_Ratings_Admin::save($ratings_id, $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }
    
    function loadRatings(){
      global $toC_Json;
      
      $data = toC_Ratings_Admin::getData($_REQUEST['ratings_id']);
      
      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }
    
    function deleteRating(){
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['ratings_id']) && is_numeric($_REQUEST['ratings_id']) && toC_Ratings_Admin::delete($_REQUEST['ratings_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteRatings(){
      global $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ($batch as $ratings_id) {
        if ( !toC_Ratings_Admin::delete($ratings_id) ) {
          $error = true;
          
          break;
        }
      } 
      
      if ($error == false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }
    
    function setRatingStatus() {
      global $toC_Json, $osC_Language;
       
      if ( isset($_REQUEST['ratings_id']) && isset($_REQUEST['status']) && toC_Ratings_Admin::setStatus($_REQUEST['ratings_id'], $_REQUEST['status']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
  