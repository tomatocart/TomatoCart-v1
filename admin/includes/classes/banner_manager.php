<?php
/*
  $Id: banner_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_BannerManager_Admin {
    function getData($id) {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select * from :table_banners where banners_id = :banners_id');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      $data = $Qbanner->toArray();

      $Qbanner->freeResult();

      return $data;
    }
    
    function getStatistics($id) {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select banners_shown, banners_clicked from :table_banners_history where banners_id = :banners_id');
      $Qbanner->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();
      
      $statistics = 0;
      if($Qbanner->next()) {
        if ($Qbanner->value('banners_clicked') > 0) {
          $statistics = $Qbanner->value('banners_shown') / $Qbanner->value('banners_clicked');
        }
      }

      $Qbanner->freeResult();

      return $statistics;
    }
    
    function setStatus($id, $flag) {
      global $osC_Database;

      $Qbanners = $osC_Database->query('update :table_banners set status = :status where banners_id = :banners_id');
      $Qbanners->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanners->bindInt(':status', $flag);
      $Qbanners->bindInt(':banners_id', $id);
      $Qbanners->setLogging($_SESSION['module'], $id);
      $Qbanners->execute();

      return true;
    }
    
    function save($id = null, $data) {
      global $osC_Database;

      $error = false;

      $image_location = '';
      if ( $data['banner_type'] == 'image' ) {
        $image = null;
        $old_image = null;
        $new_image = false;
        
        if ( is_numeric($id) ) {
          $Qimage = $osC_Database->query('select banners_image from :table_banners where banners_id = :banners_id');
          $Qimage->bindTable(':table_banners', TABLE_BANNERS);
          $Qimage->bindInt(':banners_id', $id);
          $Qimage->execute();
          
          $old_image = $Qimage->value('banners_image');
        }
      
        if ( !empty($data['image']) ) {
          $image = new upload($data['image'], realpath('../images/'));
  
          if ( $image->exists() && $image->parse() && $image->save() ) {
            $new_image = true;
          }
        }
        
        if ($new_image === true) {
          $image_location = $image->filename;
          
          if ( !empty($old_image) && is_file('../images/' . $old_image) && is_writeable('../images/' . $old_image) ) {
            @unlink('../images/' . $old_image);
          }
        } else if ( ($new_image === false) && !empty($old_image) ) {
          $image_location = $old_image;
        } else {
          $error = true;
        }
      }
      
      if ( is_numeric($id) ) {
	      if( ( ($new_image == true) && !empty($old_image) ) || ( ($data['banner_type'] == 'text') && !empty($old_image) )) {
          $Qimage = $osC_Database->query('select count(*) as image_count from :table_banners where banners_image = :banners_image and banners_id <> :banners_id');
          $Qimage->bindTable(':table_banners', TABLE_BANNERS);
          $Qimage->bindInt(':banners_id', $id);
          $Qimage->bindValue(':banners_image', $old_image);
          $Qimage->execute();
                    
          if(($Qimage->value('image_count')) == 0) {
            if ( !empty($old_image) && is_file('../images/' . $old_image) && is_writeable('../images/' . $old_image) ) {
              @unlink('../images/' . $old_image);
            }
          }
	      }
      }
      
      if ( $error === false ) {
        if ( is_numeric($id) ) {
          $Qbanner = $osC_Database->query('update :table_banners set banners_title = :banners_title, banners_url = :banners_url, banners_image = :banners_image, banners_group = :banners_group, banners_html_text = :banners_html_text, expires_date = :expires_date, expires_impressions = :expires_impressions, date_scheduled = :date_scheduled, status = :status where banners_id = :banners_id');
          $Qbanner->bindInt(':banners_id', $id);
        } else {
          $Qbanner = $osC_Database->query('insert into :table_banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_date, expires_impressions, date_scheduled, status, date_added) values (:banners_title, :banners_url, :banners_image, :banners_group, :banners_html_text, :expires_date, :expires_impressions, :date_scheduled, :status, now())');
        }

        $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
        $Qbanner->bindValue(':banners_title', $data['title']);
        $Qbanner->bindValue(':banners_url', $data['url']);
        $Qbanner->bindValue(':banners_image', $image_location);
        $Qbanner->bindValue(':banners_group', (!empty($data['group_new']) ? $data['group_new'] : $data['group']));
        $Qbanner->bindValue(':banners_html_text', $data['html_text']);

        if ( empty($data['date_expires']) ) {
          $Qbanner->bindRaw(':expires_date', 'null');
          $Qbanner->bindInt(':expires_impressions', $data['expires_impressions']);
        } else {
          $Qbanner->bindValue(':expires_date', $data['date_expires']);
          $Qbanner->bindInt(':expires_impressions', 0);
        }

        if ( empty($data['date_scheduled']) ) {
          $Qbanner->bindRaw(':date_scheduled', 'null');
          $Qbanner->bindInt(':status', (($data['status'] === true) ? 1 : 0));
        } else {
          $Qbanner->bindValue(':date_scheduled', $data['date_scheduled']);
          $Qbanner->bindInt(':status', ($data['date_scheduled'] > date('Y-m-d') ? 0 : (($data['status'] === true) ? 1 : 0)));
        }

        $Qbanner->setLogging($_SESSION['module'], $id);
        $Qbanner->execute();

        if ( !$osC_Database->isError() ) {
          return true;
        }
      }

      return false;
    }

    function delete($id, $delete_image = false) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      if ( $delete_image === true ) {
        $Qimage = $osC_Database->query('select banners_image from :table_banners where banners_id = :banners_id');
        $Qimage->bindTable(':table_banners', TABLE_BANNERS);
        $Qimage->bindInt(':banners_id', $id);
        $Qimage->execute();
        
        $image = $Qimage->value('banners_image');
      }

      $Qdelete = $osC_Database->query('delete from :table_banners where banners_id = :banners_id');
      $Qdelete->bindTable(':table_banners', TABLE_BANNERS);
      $Qdelete->bindInt(':banners_id', $id);
      $Qdelete->setLogging($_SESSION['module'], $id);
      $Qdelete->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false) {
        $Qdelete = $osC_Database->query('delete from :table_banners_history where banners_id = :banners_id');
        $Qdelete->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
        $Qdelete->bindInt(':banners_id', $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ( $error === false ) {
        if ( ($delete_image === true) && isset($image) && !empty($image) ) {
          if ( is_file('../images/' . $image) && is_writeable('../images/' . $image) ) {
            @unlink('../images/' . $image);
          }
        }

        $image_extension = osc_dynamic_image_extension();

        if ( !empty($image_extension) ) {
          if ( is_file('images/graphs/banner_yearly-' . $id . '.' . $image_extension) && is_writeable('images/graphs/banner_yearly-' . $id . '.' . $image_extension) ) {
            @unlink('images/graphs/banner_yearly-' . $id . '.' . $image_extension);
          }

          if ( is_file('images/graphs/banner_monthly-' . $id . '.' . $image_extension) && is_writeable('images/graphs/banner_monthly-' . $id . '.' . $image_extension) ) {
            @unlink('images/graphs/banner_monthly-' . $id . '.' . $image_extension);
          }

          if ( is_file('images/graphs/banner_daily-' . $id . '.' . $image_extension) && is_writeable('images/graphs/banner_daily-' . $id . '.' . $image_extension) ) {
            unlink('images/graphs/banner_daily-' . $id . '.' . $image_extension);
          }
        }

        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  }
?>
