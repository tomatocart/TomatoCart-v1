<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.reviews");';
  
  include('ratings_grid.php');
  include('ratings_dialog.php');
  include('reviews_grid.php');
  include('reviews_edit_dialog.php');
?>

Ext.override(TocDesktop.ReviewsWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow(this.id);
     
    if(!win){
      if (this.params.set == 'reviews') {
        var title = '<?php echo $osC_Language->get('heading_title_reviews'); ?>';
        var grd = new Toc.reviews.ReviewsGrid({owner: this});
      } else {
        var title = '<?php echo $osC_Language->get('heading_title_ratings'); ?>';
        var grd = new Toc.reviews.RatingsGrid({owner: this});
      }
      
      win = desktop.createWindow({
        id: this.id,
        title: title,
        width: 800,
        height: 400,
        iconCls: this.iconCls,
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
    
  createReviewsEditDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('reviews-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.reviews.ReviewsEditDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
      
    return dlg;
  },
  
  createRatingsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('ratings-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.reviews.RatingsDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
      
    return dlg;
  }
});
