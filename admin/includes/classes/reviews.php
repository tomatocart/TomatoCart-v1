<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Reviews_Admin {
  
    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qreview = $osC_Database->query('select r.*, pd.products_name from :table_reviews r left join :table_products_description pd on (r.products_id = pd.products_id and r.languages_id = pd.language_id) where r.reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->execute();

      $data = $Qreview->toArray();
      
      $data['reviews_rating'] = osc_image('../images/stars_' . $Qreview->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_from_5_stars'), $Qreview->valueInt('reviews_rating')));
      $data['detailed_rating'] = $Qreview->valueInt('reviews_rating');
      
      $Qaverage = $osC_Database->query('select (avg(reviews_rating) / 5 * 100) as average_rating from :table_reviews where products_id = :products_id');
      $Qaverage->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qaverage->bindInt(':products_id', $Qreview->valueInt('products_id'));
      $Qaverage->execute();

      $data['average_rating'] = $Qaverage->value('average_rating');
      
      $ratings = self::getCustomersRatings($id);
      if ( is_array($ratings) && !empty($ratings) ) {
        $data['ratings'] = $ratings;
      } else {
        $data['ratings'] = null;
      }
      
      $Qaverage->freeResult();
      $Qreview->freeResult();

      return $data;
    }

    function save($id, $data) {
      global $osC_Database;

      $error = false;
      
      $osC_Database->startTransaction();
      
      $Qreview = $osC_Database->query('update :table_reviews set reviews_text = :reviews_text, reviews_rating = :reviews_rating, reviews_status = :reviews_status, last_modified = now() where reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindValue(':reviews_text', $data['review']);
      $Qreview->bindInt(':reviews_rating', $data['rating']);
      $Qreview->bindInt(':reviews_status', $data['reviews_status']);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->setLogging($_SESSION['module'], $id);
      $Qreview->execute();

      if ( !$osC_Database->isError() ) {
        if ( isset($data['ratings']) && !empty($data['ratings']) ){
          foreach ($data['ratings'] as $customers_ratins_id => $value) {
            $Qupdate = $osC_Database->query('update :table_customers_ratings set ratings_value = :value where customers_ratings_id = :customers_ratings_id');
            $Qupdate->bindTable(':table_customers_ratings', TABLE_CUSTOMERS_RATINGS);
            $Qupdate->bindInt(':customers_ratings_id', $customers_ratins_id);
            $Qupdate->bindValue(':value', $value);
            $Qupdate->setLogging($_SESSION['module'], $id);
            $Qupdate->execute();
            
            if ($osC_Database->isError()) {
              $error = true;
              
              break;
            }
          }
        }
      } else {
        $error = true;
      } 
      

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('reviews');

        return true;
      }

      $osC_Database->rollbackTransaction();
      
      return true;
    }

    function delete($id) {
      global $osC_Database;
      
      $osC_Database->startTransaction();
      
      $Qreview = $osC_Database->query('delete from :table_reviews where reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->setLogging($_SESSION['module'], $id);
      $Qreview->execute();

      if ( !$osC_Database->isError() ) {
        $Qratings = $osC_Database->query('delete from :table_customers_ratings where reviews_id = :reviews_id');
        $Qratings->bindTable(':table_customers_ratings', TABLE_CUSTOMERS_RATINGS);
        $Qratings->bindInt(':reviews_id', $id);
        $Qratings->setLogging($_SESSION['module'], $id);
        $Qratings->execute();
        
        if ( !$osC_Database->isError() ) {
          $osC_Database->commitTransaction();
          
          return true;
        }
      }
      
      $osC_Database->rollbackTransaction();

      return false;
    }
    
  
    function setStatus($id, $flag) {
      global $osC_Database;
    
      $Qstatus = $osC_Database->query('update :table_reviews set reviews_status = :reviews_status where reviews_id = :reviews_id');
      $Qstatus->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qstatus->bindInt(":reviews_id", $id);
      $Qstatus->bindValue(":reviews_status", $flag);
      $Qstatus->execute();
      
      if(!$osC_Database->isError()) {
        osC_Cache::clear('reviews');
        return true;
      }
      return false;
    }
    
    function getCustomersRatings($reviews_id) {
      global $osC_Database, $osC_Language;

      $Qratings = $osC_Database->query('select r.customers_ratings_id, r.ratings_id, r.ratings_value, rd.ratings_text from :table_customers_ratings r inner join :table_ratings_description rd on r.ratings_id = rd.ratings_id where r.reviews_id = :reviews_id and rd.languages_id = :languages_id order by r.customers_ratings_id');
      $Qratings->bindTable(':table_customers_ratings', TABLE_CUSTOMERS_RATINGS);
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':reviews_id', $reviews_id);
      $Qratings->bindInt(':languages_id', $osC_Language->getID());
      $Qratings->execute();
      
      $ratings = array();
      while ($Qratings->next()) {
        $ratings[] = array('customers_ratings_id' => $Qratings->ValueInt('customers_ratings_id'),
                           'ratings_id' => $Qratings->ValueInt('ratings_id'),
                           'name'  => $Qratings->Value('ratings_text'),
                           'value' => $Qratings->Value('ratings_value')); 
      }
      
      return $ratings;
    }
  }
?>
