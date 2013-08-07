<?php
/*
  $Id: polls_main_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.polls.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';
  
  config.grdPollAnswer = new Toc.polls.PollsAnswersGrid({owner: config.owner});
  config.grdPolls = new Toc.polls.PollsGrid({owner: config.owner}); 
  
  config.grdPolls.on('selectchange', this.onGrdPollsSelectChange, this);
  config.grdPolls.getStore().on('load', this.onGrdPollsLoad, this);
  
  config.items = [config.grdPolls, config.grdPollAnswer];    
    
  Toc.polls.mainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.polls.mainPanel, Ext.Panel, {

  onGrdPollsLoad: function() {
    if (this.grdPolls.getStore().getCount() > 0) {
      this.grdPolls.getSelectionModel().selectFirstRow();
      record = this.grdPolls.getStore().getAt(0);
      
      this.onGrdPollsSelectChange(record);
    } else {
      this.grdPollAnswer.reset();
    }
  },

  onGrdPollsSelectChange: function(record) {
    this.grdPollAnswer.iniGrid(record);
  }
});