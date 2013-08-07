<?php
/*
  $Id: checkout_one_page.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

$step = 1;
?>

<h1><?php echo $osC_Language->get('checkout')?></h1>

<?php
  if ($messageStack->size('payment_error_msg') > 0) {
    echo $messageStack->output('payment_error_msg');
  }
?>

<?php
  if ($messageStack->size('checkout') > 0) {
    echo $messageStack->output('checkout');
  }
?>


<ul id="checkoutForm"> 
  <?php if ($osC_Customer->isLoggedOn() === false) { ?>
    <li id="checkoutMethodForm">
      <h3 class="formHeader">
         <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_method') . '</a>';?><span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_down.png'); ?> </span>
      </h3>
      <div class="formBody"></div>
    </li>
  <?php } ?>
  
  <li id="billingInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_billing_information') . '</a>'; ?> <span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_up.png'); ?></span>
    </h3>
    <div class="formBody"></div>
  </li>  
  
  <li id="shippingInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_shipping_information') . '</a>';?> <span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_up.png'); ?></span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="shippingMethodForm">
    <h3 class="formHeader">
       <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_shipping_method') . '</a>'; ?> <span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_up.png'); ?></span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="paymentInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_payment_information') . '</a>'; ?> <span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_up.png'); ?></span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="orderConfirmationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '<a onclick="javascript:void(0);">' . $osC_Language->get('checkout_order_review') . '</a>'; ?> <span><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/checkout_up.png'); ?></span>
    </h3>
    <div class="formBody"></div>
  </li>
</ul>

<script type="text/javascript">
  var tocCheckout = new Class({
    Extends: Checkout,
    
    iniCheckoutForms: function() {
      if (this.options.isLoggedOn == false) {
        this.loadCheckoutMethodForm();
      } else {
        this.loadBillingInformationForm();
      }
      
      $$('.formHeader').each( function(formHeader, i) {
        formHeader.addEvent('click', function(e){
          var formName = formHeader.getParent().id;
          
          if (this.shipToBillingAddress == true) {
            if ((formName == 'shippingInformationForm')) {
              return;
            }
          }
          
          if (this.options.isVirtualCart == true) {
            if ((formName == 'shippingInformationForm') || (formName == 'shippingMethodForm')) {
              return;
            }
          }
          
          if (this.isTotalZero == true) {
            if (formName == 'paymentInformationForm') {
              return;
            }
          }
          
          if (this.steps[formName] < this.steps[this.openedForm]) { 
            this.gotoPanel(formName);
          }
        }.bind(this));
        
        if (i != 0) {
          formHeader.getParent().addClass('collapse');
          formHeader.getNext().setStyle('display', 'none');
        } else {
          this.openedForm = formHeader.getParent().id;
        }
      }.bind(this));  
    },
    
    gotoPanel: function(formName) {
      this.openedForm = formName;
    
      $$('.formHeader').each( function(formHeader) {
        var form_name = formHeader.getParent().id,
            form_body = formHeader.getNext(),
            span = formHeader.getElement('span');
        
        if (formName != form_name) {
          if (!formHeader.getParent().hasClass('collapse')) {
            formHeader.getParent().addClass('collapse');
          }
          
          form_body.setStyle('display', 'none');
          
          span.getElement('img').setProperty('src', 'templates/' + '<?php echo $osC_Template->getCode();?>' + '/images/checkout_up.png');
        } else {
          if (formHeader.getParent().hasClass('collapse')) {
            formHeader.getParent().removeClass('collapse');
          }
          
          form_body.setStyle('display', 'block');
          span.getElement('img').setProperty('src', 'templates/' + '<?php echo $osC_Template->getCode(); ?>' + '/images/checkout_down.png');
        }
      });
    }
  });
  
  window.addEvent('domready', function() {
    checkout = new tocCheckout({
      remoteUrl: '<?php echo osc_href_link('json.php', null, 'SSL', false, false, true); ?>',
      isLoggedOn: <?php echo ($osC_Customer->isLoggedOn() === true) ? 'true' : 'false';?>,
      sessionName: '<?php echo $osC_Session->getName(); ?>',
      template: '<?php echo $osC_Template->getCode(); ?>',
      languageCode: '<?php echo $osC_Language->getCode(); ?>',
      sessionId: '<?php echo $osC_Session->getID(); ?>',
      <?php
        $default_image_file = 'includes/languages/' . $osC_Language->getCode() . '/images/buttons/button_continue.gif';
        $image_file = 'templates/' . $_SESSION['template']['code'] . '/images/buttons/languages/' . $osC_Language->getCode() . '/button_continue.gif';
        
        if(file_exists($image_file)){
      ?>
      continueBtn: '<?php echo $image_file; ?>',
      <?php   
        }else {
      ?>
      continueBtn: '<?php echo $default_image_file; ?>',
      
      <?php 
        }
        
        if ( isset($_GET['view']) && !empty($_GET['view']) ) {
      ?>      
            view: '<?php echo $_GET['view']; ?>',
      <?php 
        }else if ($messageStack->size('payment_error_msg') > 0) {
      ?>
            view: 'paymentInformationForm',
      <?php
        }
      ?>      
      isVirtualCart: <?php echo ($osC_ShoppingCart->isVirtualCart() ? 'true' : 'false'); ?>,
      isTotalZero: <?php echo ($osC_ShoppingCart->isTotalZero() ? 'true' : 'false'); ?>
    });
  });  
</script>