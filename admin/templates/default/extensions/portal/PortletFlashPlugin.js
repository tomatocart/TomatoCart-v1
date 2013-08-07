Ext.ux.PortletFlashPlugin = function() {
    this.init = function(ct) {
        ct.flashTemplate = new Ext.XTemplate(
            '<div style="{style}">',
            '<object id="flash-{id}" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="{swfWidth}" height="{swfHeight}">',
            '<param name="movie" value="{swf}" />',
            '<param name="quality" value="high" />',
            '<param name="wmode" value="transparent" />',
            '<param name="flashvars" value="{computedflashvars}" />',
            '<param name="allowScriptAccess" value="domain" />',
            '<param name="align" value="t" />',
            '<param name="salign" value="TL" />',
            '<param name="swliveconnect" value="true" />',
            '<param name="scale" value="showall" />',
            '<embed name="flash-{id}" src="{swf}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="{computedflashvars}" type="application/x-shockwave-flash" width="{swfWidth}" height="{swfHeight}" wmode="transparent" allowScriptAccess="always" swliveconnect="true" align="t" salign="TL" scale="showall"></embed>',
            '</object>',
            '</div>'
        );
        ct.flashTemplate.compile();
        
        ct.renderFlash = function() {
          if (this.flashvars && (typeof this.flashvars == 'object')) {
              var tempflashvars = Ext.apply({}, this.flashvars);
              for (var key in tempflashvars) {
                  if (typeof tempflashvars[key] == 'function') {
                    tempflashvars[key] = tempflashvars[key].call(this, true);
                  } 
              };
              
              if (!Ext.isEmpty(tempflashvars.data) && !Ext.isEmpty(token)) {
                  tempflashvars.data += '&token=' + token;
              }
                
              this.computedflashvars = Ext.urlEncode(tempflashvars);
          }
          
          if (this.proxyWidth) {
            this.swfWidth = this.proxyWidth;              
          }else {
            this.swfWidth = this.body.getSize().width;
          }
                             
          this.swfHeight =  175;
          
          this.body.first() ? this.flashTemplate.overwrite(this.body.first(), this) : this.flashTemplate.insertFirst(this.body, this);
        };
        
        ct.loadFlash = function(config) {
            Ext.apply(this,config);
            this.renderFlash();
        };

        ct.on('render', ct.renderFlash, ct);       
    };
};