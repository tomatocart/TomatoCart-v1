/** 
 * $Id: Connection.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com

 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */
 
 /**
 * Override the handleResponse method of Ext.data.Connection 
 * When the system return session timeout error, force the user to login again
 */
 
 Ext.override(Ext.data.Connection, {
  handleResponse : function(response){
    
    if (response.responseText.indexOf('session_timeout') != -1) {
      Ext.MessageBox.alert(TocLanguage.msgWarningTitle, TocLanguage.msgSessionTimeout);
      window.location = "index.php?login&action=logoff";
    } else {
      this.transId = false;
      var options = response.argument.options;
      response.argument = options ? options.argument : null;
      this.fireEvent("requestcomplete", this, response, options);
      Ext.callback(options.success, options.scope, [response, options]);
      Ext.callback(options.callback, options.scope, [options, true, response]); 
    }
  }  
});