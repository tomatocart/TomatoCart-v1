<?php
/*
  $Id: slide_images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


class toC_Slide_Images_Admin {

  function getData($id) {
    global $osC_Database, $osC_Language;

    $Qimages = $osC_Database->query('select * from :table_slide_images where image_id = :image_id and language_id = :language_id');
    $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
    $Qimages->bindInt(':image_id', $id);
    $Qimages->bindInt(':language_id', $osC_Language->getID());
    $Qimages->execute();

    $data = $Qimages->toArray();

    $Qimages->freeResult();

    return $data;
  }

  function setStatus($id, $flag) {
    global $osC_Database;

    $Qimages = $osC_Database->query('update :table_slide_images set status= :status where image_id = :image_id');
    $Qimages->bindInt(':status', $flag);
    $Qimages->bindInt(':image_id', $id);
    $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
    $Qimages->setLogging($_SESSION['module'], $id);
    $Qimages->execute();
    
    if (!$osC_Database->isError()) {
      osC_Cache::clear('slide-images');
      
      return true;
    }

    return false;
  }

  function save($id = null, $data) {
    global $osC_Database, $osC_Language, $osC_Image;

    if (is_numeric($id)) {
      foreach ($osC_Language->getAll() as $l) {
        $image_upload = new upload('image' . $l['id'], DIR_FS_CATALOG . 'images/');

        if ( $image_upload->exists() && $image_upload->parse() && $image_upload->save()) {
          $Qdelete = $osC_Database->query('select image from :table_slide_images where image_id = :image_id and language_id=:language_id');
          $Qdelete->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
          $Qdelete->bindInt(':image_id', $id);
          $Qdelete->bindValue(':language_id', $l['id']);
          $Qdelete->execute();

          if($Qdelete->numberOfRows() > 0){
            @unlink(DIR_FS_CATALOG . 'images/' . $Qdelete->value('image'));
          }

          $Qimage = $osC_Database->query('update :table_slide_images set image = :image, description = :description, image_url = :image_url, sort_order = :sort_order, status = :status where image_id = :image_id and language_id=:language_id');
          $Qimage->bindValue(':image', $image_upload->filename);
        }else{
          $Qimage = $osC_Database->query('update :table_slide_images set description = :description, image_url = :image_url, sort_order = :sort_order, status = :status where image_id = :image_id and language_id=:language_id');
        }

        $Qimage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
        $Qimage->bindValue(':description', $data['description'][$l['id']]);
        $Qimage->bindValue(':image_url', $data['image_url'][$l['id']]);
        $Qimage->bindValue(':sort_order', $data['sort_order']);
        $Qimage->bindValue(':status', $data['status']);
        $Qimage->bindInt(':image_id', $id);
        $Qimage->bindValue(':language_id', $l['id']);
        $Qimage->execute();
      }
    }else{
      $Qmaximage = $osC_Database->query('select max(image_id) as image_id from :table_slide_images');
      $Qmaximage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
      $Qmaximage->execute();
      $image_id = $Qmaximage->valueInt('image_id') + 1;

      foreach ($osC_Language->getAll() as $l) {
        $products_image = new upload('image'.$l['id'], DIR_FS_CATALOG . 'images/');

        if ($products_image->exists() && $products_image->parse() && $products_image->save()) {
          $Qimage = $osC_Database->query('insert into :table_slide_images (image_id,language_id ,description,image ,image_url ,sort_order,status) values (:image_id,:language_id,:description ,:image,:image_url ,:sort_order,:status)');
          $Qimage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
          $Qimage->bindValue(':image_id', $image_id);
          $Qimage->bindValue(':language_id', $l['id']);
          $Qimage->bindValue(':description', $data['description'][$l['id']]);
          $Qimage->bindValue(':image', $products_image->filename);
          $Qimage->bindValue(':image_url', $data['image_url'][$l['id']]);
          $Qimage->bindValue(':sort_order', $data['sort_order']);
          $Qimage->bindValue(':status', $data['status']);
          $Qimage->execute();
        }
      }
    }

    if ( $osC_Database->isError() ) {
      return false;
    }else{
      osC_Cache::clear('slide-images');
      
      return true;
    }
  }

  function delete($id = null) {
    global $osC_Database;

    $Qimage = $osC_Database->query('select * from :table_slide_images where image_id = :image_id');
    $Qimage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
    $Qimage->bindInt(':image_id', $id);
    $Qimage->execute();

    if($Qimage->numberOfRows() > 0) {
      @unlink(DIR_FS_CATALOG.'images/'. $Qimage->value('image'));
    }

    $Qdelete = $osC_Database->query('delete from :table_slide_images where image_id = :image_id');
    $Qdelete->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
    $Qdelete->bindInt(':image_id', $id);
    $Qdelete->setLogging($_SESSION['module'], $id);
    $Qdelete->execute();
    if ( $osC_Database->isError() ) {
      return false;
    }else{
      osC_Cache::clear('slide-images');
      
      return true;
    }
  }
}
?>
