/** 
 * $Id: tocWizard.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com

 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */

Ext.namespace("Toc.ux.Wiz");

Toc.ux.Wiz.Wizard = Ext.extend(Ext.ux.Wiz, {
  initPanels : function(){
    var cards = this.cards;
    var cardPanelConfig = this.cardPanelConfig;

    Ext.apply(this.headerConfig, {
      steps : cards.length
    });

    this.headPanel = new Toc.ux.Wiz.Header(this.headerConfig);

    Ext.apply(cardPanelConfig, {
      layout: new Ext.ux.layout.CardLayout(),
      items: cards
    });

    Ext.applyIf(cardPanelConfig, {
	  region     : 'center',
	  border     : false,
	  activeItem : 0
    });

    this.cardPanel = new Ext.Panel(cardPanelConfig);
  },
    
  onCardShow : function(card){
    var parent = card.ownerCt;
    var items = parent.items;

    for (var i = 0, len = items.length; i < len; i++) {
      if (items.get(i).id == card.id) {
        break;
      }
    }

    this.currentCard = i;
    this.headPanel.updateStep(i, card.title);
        
    this.headPanel.updateDescription(card.description);

    if (i == len-1) {
      this.nextButton.setText(this.finishButtonText);
    } else {
      this.nextButton.setText(this.nextButtonText);
    }

    if (card.isValid()) {
      this.nextButton.setDisabled(false);
    }

    if (i == 0) {
      this.previousButton.setDisabled(true);
    } else {
      this.previousButton.setDisabled(false);
    }
  }
});