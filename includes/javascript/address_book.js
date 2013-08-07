/*
  $Id: address_book.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


var AddressBook = new Class({
  Implements: Options,
  
  options: {
    remoteUrl: 'json.php',
    sessionName: 'sid',
    sessionId: null,
    
    //the id of the country combox
    countryId: 'country',
    //the id of th state combox
    stateId: 'state-container'
  },
  
  /**
   * Responsible for sending the ajax request
   *
   * @access  private
   * @object  the data will be sent by ajax
   * @function the callback fuction
   * @return void
   */
  sendRequest: function(data, fnSuccess) {
    data.module = 'account';
    data[this.options.sessionName] = this.options.sessionId;
    
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: fnSuccess.bind(this)
    }).send();
  },
  
  /**
   * Intialize
   *
   * @access  public
   * @object  the configurations
   * @return void
   */
  initialize: function(options) {
    this.setOptions(options);
    
    this.attachEvents();
  },
  
  /**
   * Attach events for elements
   *
   * @access  private
   * @return void
   */
  attachEvents: function() {
    $(this.options.countryId).addEvent('change', function(e) {
      this.countryChange();
    }.bind(this));
  },
  
  /**
   * handle the country change event
   *
   * @access  private
   * @return void
   */
  countryChange: function() {
    var country_id = $(this.options.countryId).get('value');
    
    if (country_id > 0) {
      var params = {
        action: 'country_change',
        country_id: country_id
      };
    
      this.sendRequest(params, function(response) {
        var result = JSON.decode(response);
        
        if (result.success == true) {
        	$(this.options.stateId).empty();
          $(this.options.stateId).set('html', result.html);
        }
      }.bind(this));
    }
  }
});
