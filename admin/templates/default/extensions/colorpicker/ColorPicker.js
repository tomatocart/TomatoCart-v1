/**
 * Original code is taken from vtswingkid's (Ext JS Forum member) form.ux.ColorPickerField
 * URL: http://extjs.com/forum/showthread.php?t=47450
 * 
 * Additional modifications have been made to the Ext.ux.ColorDialog by Todd Murdock.
 */

/**
 * @class Ext.ux.ColorPicker
 * @extends Ext.BoxComponent
 * This is a color picker.
 * @license: LGPLv3
 * @author: Amon
 * @constructor
 * Creates a new ColorPicker
 * @param {Object} config Configuration options
 * @version 1.1.2
 */

Ext.namespace("Ext.ux", "Ext.ux.menu");

Ext.ux.ColorPicker = Ext.extend( Ext.BoxComponent, {
  // will need to update css 'height' if more than one color box is enabled
  showWebSafeColorbox : false,
  showInverseColorbox : false,
  showColorbox : true,
  
  initComponent: function() {
    this.applyDefaultsCP();
    Ext.ux.ColorPicker.superclass.initComponent.apply( this, arguments );
    this.addEvents('select');
  },
  
  onRender: function() {
    Ext.ux.ColorPicker.superclass.onRender.apply( this, arguments );
    // check if container, self-container or renderTo exists
    this.body = this.body || ( this.container || ( this.renderTo || Ext.DomHelper.append( Ext.getBody(), {}, true ) ) );
    if( !this.el ) {
      this.el = this.body;
      if( this.cls ) { Ext.get( this.el ).addClass( this.cls ); }
    }
    // render this component
    this.renderComponent();
  },
  
  applyDefaultsCP: function() {
    Ext.apply( this, {
      'cls': 'x-cp-mainpanel',
      'resizable': this.resizable || false,
      'HSV': {
        h: 0,
        s: 0,
        v: 0
      },
      updateMode: null
    });
  },
  
  renderComponent: function() {
    // create RGB Slider
    Ext.DomHelper.append( this.body, {
      'id': this.cpGetId( 'rgb' ),
      'cls': 'x-cp-rgbpicker'
    });
    
    // Create HUE Slider
    Ext.DomHelper.append( this.body, {
      'id': this.cpGetId( 'hue' ),
      'cls': 'x-cp-huepicker'
    });
    
    // Initialize HUE Picker DD
    this.huePicker = Ext.DomHelper.append( this.body, { 'cls': 'x-cp-hueslider' });
    this.hueDD = new Ext.dd.DD( this.huePicker, 'huePicker' );
    this.hueDD.constrainTo( this.cpGetId( 'hue' ), {'top':-7,'right':-3,'bottom':-7,'left':-3} );
    this.hueDD.onDrag = this.moveHuePicker.createDelegate( this );
    
    // initialize onclick on the rgb picker
    Ext.get( this.cpGetId( 'hue' ) ).on( 'mousedown', this.clickHUEPicker.createDelegate( this ) );
    
    // initialize start position
    Ext.get( this.huePicker ).moveTo( Ext.get( this.cpGetId( 'hue' ) ).getLeft() - 3, Ext.get( this.cpGetId( 'hue' ) ).getTop() - 7 );
    
    // Initialize RGB Picker DD
    this.rgbPicker = Ext.DomHelper.append( this.body, { 'cls': 'x-cp-rgbslider' });
    this.rgbDD = new Ext.dd.DD( this.rgbPicker, 'rgbPicker' );
    this.rgbDD.constrainTo( this.cpGetId( 'rgb' ), -7 );
    this.rgbDD.onDrag = this.moveRGBPicker.createDelegate( this );
    
    // initialize onclick on the rgb picker
    Ext.get( this.cpGetId( 'rgb' ) ).on( 'mousedown', this.clickRGBPicker.createDelegate( this ) );
    // initialize start position
    Ext.get( this.rgbPicker ).moveTo( Ext.get( this.cpGetId( 'rgb' ) ).getLeft() - 7, Ext.get( this.cpGetId( 'rgb' ) ).getTop() - 7 );
    
    // Create color divs and Form elements
    this.formPanel = new Ext.form.FormPanel({
      'border': false,
      'renderTo': Ext.DomHelper.append( this.body, {
              'id': this.cpGetId( 'fCont' ),
              'cls': 'x-cp-formcontainer'
            }, true ),
      'frame': false,
      'labelAlign': 'left',
      'labelWidth': 10,
      'items': [{ // Color box
        'border': false,
        'layout': 'form',
        'id': this.cpGetId( 'cCont' )
      },{
        'bodyStyle': 'padding-top:10px;',
        'border': false,
        'layout': 'column',
        'items': [{ // RGB fields
          'border': false,
          'columnWidth': .5,
          'layout': 'form',
          'defaultType': 'numberfield',
          'defaults': {
            'width': 30,
            'value': 0,
            'minValue': 0,
            'maxValue': 255,
            'allowBlank': false,
            'labelSeparator': '',
            'enableKeyEvents': true
          },
          'items': [{
            'fieldLabel': 'R',
            'id': this.cpGetId( 'iRed' )
          },{
            'fieldLabel': 'G',
            'id': this.cpGetId( 'iGreen' )
          },{
            'fieldLabel': 'B',
            'id': this.cpGetId( 'iBlue' )
          }]
        },{ // HSV Fields
          'border': false,
          'columnWidth': .5,
          'layout': 'form',
          'defaultType': 'numberfield',
          'defaults': {
            'width': 30,
            'value': 0,
            'minValue': 0,
            'maxValue': 255,
            'allowBlank': false,
            'labelSeparator': '',
            'enableKeyEvents': true
          },
          'items': [{
            'fieldLabel': 'H',
            'maxValue': 360,
            'id': this.cpGetId( 'iHue' )
          },{
            'fieldLabel': 'S',
            'id': this.cpGetId( 'iSat' )
          },{
            'fieldLabel': 'V',
            'id': this.cpGetId( 'iVal' )
          }]
        },{ // HEX field panel
        'border': false,
        'layout': 'form',
        'labelAlign': 'left',
        'items': [{
          'width': 88,
          'value': '000000',
          'labelSeparator': '',
          'allowBlank': false,
          'fieldLabel': '#',
          'id': this.cpGetId( 'iHexa' ),
          'value': '000000',
          'xtype': 'hexfield',
          'enableKeyEvents': true
          // 'regex': /^[0-9a-fA-F]{6}$/
        }]
      }]
      }]
    });
    
    Ext.getCmp( this.cpGetId( 'iRed' ) ).on( 'keyup', this.updateFromIRGB.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iGreen' ) ).on( 'keyup', this.updateFromIRGB.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iBlue' ) ).on( 'keyup', this.updateFromIRGB.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iHue' ) ).on( 'keyup', this.updateFromIHSV.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iSat' ) ).on( 'keyup', this.updateFromIHSV.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iVal' ) ).on( 'keyup', this.updateFromIHSV.createDelegate( this ), {buffer: 750} );
    Ext.getCmp( this.cpGetId( 'iHexa' ) ).on( 'keyup', this.updateFromIHexa.createDelegate( this ), {buffer: 750} );
    
    var cContBody = Ext.getCmp( this.cpGetId( 'cCont' ) ).body;
    
    if(this.showWebSafeColorbox === true){ // show web save color box
      Ext.DomHelper.append( cContBody, { 'cls': 'x-cp-colorbox', 'id': this.cpGetId( 'cWebSafe' ) }, true ).update( 'Websafe' );
      Ext.get( this.cpGetId( 'cWebSafe' ) ).on( 'click', this.updateFromBox.createDelegate( this ) );
    }
    if(this.showInverseColorbox === true){ // show inverse color box
      Ext.DomHelper.append( cContBody, { 'cls': 'x-cp-colorbox', 'id': this.cpGetId( 'cInverse' ) }, true ).update( 'Inverse' );
      Ext.get( this.cpGetId( 'cInverse' ) ).on( 'click', this.updateFromBox.createDelegate( this ) );
    }
    if(this.showColorbox === true){ // show color box
      Ext.DomHelper.append( cContBody, { 'cls': 'x-cp-colorbox', 'id': this.cpGetId( 'cColor' ) }, true );//.update( 'Pick Color' );
      Ext.get( this.cpGetId( 'cColor' ) ).on( 'click', this.selectColor.createDelegate( this ) );
    }
    
    Ext.DomHelper.append( this.body, {'tag':'br','cls':'x-cp-clearfloat'});
  },
  
  cpGetId: function( postfix ) {
    return this.getId() + '__' + ( postfix || 'cp' );
  },
  
  updateRGBPosition: function( x, y ) {
    this.updateMode = 'click';
    x = x < 0 ? 0 : x;
    x = x > 181 ? 181 : x;
    y = y < 0 ? 0 : y;
    y = y > 181 ? 181 : y;
    this.HSV.s = this.getSaturation( x );
    this.HSV.v = this.getValue( y );
    Ext.get( this.rgbPicker ).moveTo( Ext.get( this.cpGetId( 'rgb' ) ).getLeft() + x - 7, Ext.get( this.cpGetId( 'rgb' ) ).getTop() + y - 7, ( this.animateMove || true ) );
    this.updateColor();
  },
  
  updateHUEPosition: function( y ) {
    this.updateMode = 'click';
    y = y < 1 ? 1 : y;
    y = y > 181 ? 181 : y;
    this.HSV.h = Math.round( 360 / 181 * ( 181 - y ) );
    Ext.get( this.huePicker ).moveTo( Ext.get( this.huePicker ).getLeft(), Ext.get( this.cpGetId( 'hue' ) ).getTop() + y - 7, ( this.animateMove || true ) );
    this.updateRGBPicker( this.HSV.h );
    this.updateColor();
  },
  
  clickRGBPicker: function( event, element ) {
    this.updateRGBPosition( event.xy[0] - Ext.get( this.cpGetId( 'rgb' ) ).getLeft() , event.xy[1] - Ext.get( this.cpGetId( 'rgb' ) ).getTop() );
  },
  
  clickHUEPicker: function( event, element ) {
    this.updateHUEPosition( event.xy[1] - Ext.get( this.cpGetId( 'hue' ) ).getTop() );
  },
  
  moveRGBPicker: function( event ) {
    this.rgbDD.constrainTo( this.cpGetId( 'rgb' ), -7 );
    this.updateRGBPosition( Ext.get( this.rgbPicker ).getLeft() - Ext.get( this.cpGetId( 'rgb' ) ).getLeft() + 7 , Ext.get( this.rgbPicker ).getTop() - Ext.get( this.cpGetId( 'rgb' ) ).getTop() + 7 );
  },
  
  moveHuePicker: function( event ) {
    this.hueDD.constrainTo( this.cpGetId( 'hue' ), {'top':-7,'right':-3,'bottom':-7,'left':-3} );
    this.updateHUEPosition( Ext.get( this.huePicker ).getTop() - Ext.get( this.cpGetId( 'hue' ) ).getTop() + 7 );
  },
  
  updateRGBPicker: function( newValue ) {
    this.updateMode = 'click';
    Ext.get( this.cpGetId( 'rgb' ) ).setStyle({ 'background-color': '#' + this.rgbToHex( this.hsvToRgb( newValue, 1, 1 ) ) });
    this.updateColor();
  },
  
  updateColor: function() {
    var rgb = this.hsvToRgb( this.HSV.h, this.HSV.s, this.HSV.v );
    var websafe = this.websafe( rgb );
    var invert = this.invert( rgb );
    var wsInvert = this.invert( websafe );
    if( this.updateMode !== 'hexa' ) {
      Ext.getCmp( this.cpGetId( 'iHexa' ) ).setValue( this.rgbToHex( rgb ) );
    }
    if( this.updateMode !== 'rgb' ) {
      Ext.getCmp( this.cpGetId( 'iRed' ) ).setValue( rgb[0] );
      Ext.getCmp( this.cpGetId( 'iGreen' ) ).setValue( rgb[1] );
      Ext.getCmp( this.cpGetId( 'iBlue' ) ).setValue( rgb[2] );
    }
    if( this.updateMode !== 'hsv' ) {
      Ext.getCmp( this.cpGetId( 'iHue' ) ).setValue( Math.round( this.HSV.h ) );
      Ext.getCmp( this.cpGetId( 'iSat' ) ).setValue( Math.round( this.HSV.s * 100 ) );
      Ext.getCmp( this.cpGetId( 'iVal' ) ).setValue( Math.round( this.HSV.v * 100 ) );
    }
    
    if(this.showColorbox === true){
      Ext.get( this.cpGetId( 'cColor' ) ).setStyle({
        'background': '#' + this.rgbToHex( rgb ),
        'color': '#' + this.rgbToHex( invert )
      });
      //Ext.getDom( this.cpGetId( 'cColor' ) ).title = '#'+this.rgbToHex( rgb );
    }
    
    if(this.showInverseColorbox === true){
      Ext.get( this.cpGetId( 'cInverse' ) ).setStyle({
        'background': '#' + this.rgbToHex( invert ),
        'color': '#' + this.rgbToHex( rgb )
      });
      //Ext.getDom( this.cpGetId( 'cInverse' ) ).title = '#'+this.rgbToHex( invert );
    }
    
    if(this.showWebSafeColorbox === true){
      Ext.get( this.cpGetId( 'cWebSafe' ) ).setStyle({
        'background': '#' + this.rgbToHex( websafe ),
        'color': '#' + this.rgbToHex( wsInvert )
      });
      //Ext.getDom( this.cpGetId( 'cWebSafe' ) ).title = '#'+this.rgbToHex( websafe );
    }
    
    if( this.updateMode !== 'click' ) {
      Ext.get( this.huePicker ).moveTo( Ext.get( this.huePicker ).getLeft(), Ext.get( this.cpGetId( 'hue' ) ).getTop() + this.getHPos( Ext.getCmp( this.cpGetId( 'iHue' ) ).getValue() ) - 7, ( this.animateMove || true ) );
      Ext.get( this.rgbPicker ).moveTo( Ext.get( this.cpGetId( 'rgb' ) ).getLeft() + this.getSPos( Ext.getCmp( this.cpGetId( 'iSat' ) ).getValue() / 100 ) - 7, Ext.get( this.cpGetId( 'hue' ) ).getTop() + this.getVPos( Ext.getCmp( this.cpGetId( 'iVal' ) ).getValue() / 100 ) - 7, ( this.animateMove || true ) );
    }
    
    Ext.get( this.cpGetId( 'rgb' ) ).setStyle({ 'background-color': '#' + this.rgbToHex( this.hsvToRgb( Ext.getCmp( this.cpGetId( 'iHue' ) ).getValue(), 1, 1 ) ) });
  
    this.selectColor(); // fire the 'select' event now
  },
  
  setColor: function(c) {
    if(!/^[0-9a-fA-F]{6}$/.test(c))return;
    Ext.getCmp( this.cpGetId( 'iHexa' ) ).setValue(c);
    this.updateFromIHexa();
  },
  
  getColor: function(){
    return Ext.getCmp( this.cpGetId( 'iHexa' ) ).getValue();
  },
  
  updateFromIRGB: function( input, newValue, oldValue ) {
    this.updateMode = 'rgb';
    var temp = this.rgbToHsv( Ext.getCmp( this.cpGetId( 'iRed' ) ).getValue(), Ext.getCmp( this.cpGetId( 'iGreen' ) ).getValue(), Ext.getCmp( this.cpGetId( 'iBlue' ) ).getValue() );
    this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
    this.updateColor();
  },
  
  updateFromIHSV: function( input, newValue, oldValue ) {
    this.updateMode = 'hsv';
    this.HSV = { h: Ext.getCmp( this.cpGetId( 'iHue' ) ).getValue(), s:Ext.getCmp( this.cpGetId( 'iSat' ) ).getValue() / 100, v:Ext.getCmp( this.cpGetId( 'iVal' ) ).getValue() / 100};
    this.updateColor();
  },
  
  updateFromIHexa: function( input, e) {
    var value = Ext.getCmp( this.cpGetId( 'iHexa' ) ).getValue();
    if(!/^[0-9a-fA-F]{6}$/.test(value))return;
    
    this.updateMode = 'hexa';
    var temp = this.rgbToHsv( this.hexToRgb( value ) );
    this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
    this.updateColor();
  },
  
  updateFromBox: function( event, element ) {
    this.updateMode = 'click';
    var temp = this.rgbToHsv( this.hexToRgb( Ext.get( element ).getColor( 'backgroundColor', '', '' ) ) );
    this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
    this.updateColor();
  },

  selectColor: function( event, element ) {
    //this.fireEvent('select', this, Ext.get( element ).getColor( 'backgroundColor', '', '' ));
    this.fireEvent( 'select', this, this.getColor() );
  },
  /**
   * Convert HSV color format to RGB color format
   * @param {Integer/Array( h, s, v )} h
   * @param {Integer} s (optional)
   * @param {Integer} v (optional)
   * @return {Array}
   */
  hsvToRgb: function( h, s, v ) {
    if( h instanceof Array ) { return this.hsvToRgb.call( this, h[0], h[1], h[2] ); }
    var r, g, b, i, f, p, q, t;
      i = Math.floor( ( h / 60 ) % 6 );
      f = ( h / 60 ) - i;
      p = v * ( 1 - s );
      q = v * ( 1 - f * s );
      t = v * ( 1 - ( 1 - f ) * s );
      switch(i) {
          case 0: r=v; g=t; b=p; break;
          case 1: r=q; g=v; b=p; break;
          case 2: r=p; g=v; b=t; break;
          case 3: r=p; g=q; b=v; break;
          case 4: r=t; g=p; b=v; break;
          case 5: r=v; g=p; b=q; break;
      }
      return [this.realToDec( r ), this.realToDec( g ), this.realToDec( b )];
  },
  /**
   * Convert RGB color format to HSV color format
   * @param {Integer/Array( r, g, b )} r
   * @param {Integer} g (optional)
   * @param {Integer} b (optional)
   * @return {Array}
   */
  rgbToHsv: function( r, g, b ) {
    if( r instanceof Array ) { return this.rgbToHsv.call( this, r[0], r[1], r[2] ); }
        r = r / 255;
        g = g / 255;
        b = b / 255;
        var min, max, delta, h, s, v;
        min = Math.min( Math.min( r, g ), b );
        max = Math.max( Math.max( r, g ), b );
        delta = max - min;
        switch (max) {
            case min: h = 0; break;
            case r:   h = 60 * ( g - b ) / delta;
                      if ( g < b ) { h += 360; }
                      break;
            case g:   h = ( 60 * ( b - r ) / delta ) + 120; break;
            case b:   h = ( 60 * ( r - g ) / delta ) + 240; break;
        }
        s = ( max === 0 ) ? 0 : 1 - ( min / max );
        return [Math.round( h ), s, max];
  },
  /**
   * Convert a float to decimal
   * @param {Float} n
   * @return {Integer}
   */
  realToDec: function( n ) {
    return Math.min( 255, Math.round( n * 256 ) );
  },
  /**
   * Convert RGB color format to Hexa color format
   * @param {Integer/Array( r, g, b )} r
   * @param {Integer} g (optional)
   * @param {Integer} b (optional)
   * @return {String}
   */
  rgbToHex: function( r, g, b ) {
    if( r instanceof Array ) { return this.rgbToHex.call( this, r[0], r[1], r[2] ); }
    return this.decToHex( r ) + this.decToHex( g ) + this.decToHex( b );
  },
  /**
   * Convert an integer to hexa
   * @param {Integer} n
   * @return {String}
   */
  decToHex: function( n ) {
    var HCHARS = '0123456789ABCDEF';
        n = parseInt(n, 10);
        n = ( !isNaN( n )) ? n : 0;
        n = (n > 255 || n < 0) ? 0 : n;
        return HCHARS.charAt( ( n - n % 16 ) / 16 ) + HCHARS.charAt( n % 16 );
  },
  /**
   * Return with position of a character in this.HCHARS string
   * @private
   * @param {Char} c
   * @return {Integer}
   */
  getHCharPos: function( c ) {
    var HCHARS = '0123456789ABCDEF';
    return HCHARS.indexOf( c.toUpperCase() );
  },
  /**
   * Convert a hexa string to decimal
   * @param {String} hex
   * @return {Integer}
   */
  hexToDec: function( hex ) {
        var s = hex.split('');
        return ( ( this.getHCharPos( s[0] ) * 16 ) + this.getHCharPos( s[1] ) );
  },
  /**
   * Convert a hexa string to RGB color format
   * @param {String} hex
   * @return {Array}
   */
  hexToRgb: function( hex ) {
    return [ this.hexToDec( hex.substr(0, 2) ), this.hexToDec( hex.substr(2, 2) ), this.hexToDec( hex.substr(4, 2) ) ];
  },
  /**
   * Convert Y coordinate to HUE value
   * @private
   * @param {Integer} y
   * @return {Integer}
   */
  getHue: function( y ) {
    var hue = 360 - Math.round( ( ( 181 - y ) / 181 ) * 360 );
    return hue === 360 ? 0 : hue;
  },
  /**
   * Convert HUE value to Y coordinate
   * @private
   * @param {Integer} hue
   * @return {Integer}
   */
  getHPos: function( hue ) {
    return 181 - hue * ( 181 / 360 );
  },
  /**
   * Convert X coordinate to Saturation value
   * @private
   * @param {Integer} x
   * @return {Integer}
   */
  getSaturation: function( x ) {
    return x / 181;
  },
  /**
   * Convert Saturation value to Y coordinate
   * @private
   * @param {Integer} saturation
   * @return {Integer}
   */
  getSPos: function( saturation ) {
    return saturation * 181;
  },
  /**
   * Convert Y coordinate to Brightness value
   * @private
   * @param {Integer} y
   * @return {Integer}
   */
  getValue: function( y ) {
    return ( 181 - y ) / 181;
  },
  /**
   * Convert Brightness value to Y coordinate
   * @private
   * @param {Integer} value
   * @return {Integer}
   */
  getVPos: function( value ) {
    return 181 - ( value * 181 );
  },
  /**
   * Not documented yet
   */
  checkSafeNumber: function( v ) {
      if ( !isNaN( v ) ) {
          v = Math.min( Math.max( 0, v ), 255 );
          var i, next;
          for( i=0; i<256; i=i+51 ) {
              next = i + 51;
              if ( v>=i && v<=next ) { return ( v - i > 25 ) ? next : i; }
          }
      }
      return v;
  },
  /**
   * Not documented yet
   */
  websafe: function( r, g, b ) {
    if( r instanceof Array ) { return this.websafe.call( this, r[0], r[1], r[2] ); }
    return [this.checkSafeNumber( r ), this.checkSafeNumber( g ), this.checkSafeNumber( b )];
  },
  /**
   * Not documented yet
   */
  invert: function( r, g, b ) {
    if( r instanceof Array ) { return this.invert.call( this, r[0], r[1], r[2] ); }
    return [255-r,255-g,255-b];
  }
});
/**
 *
 */
Ext.ux.ColorDialog = Ext.extend( Ext.Window, {
  constructor: function(config){
    config = config || {};
    Ext.applyIf(config, {
      buttons: [{
          handler: this.onOk,
          scope: this,
          text: 'Ok'
        },{
          handler: this[this.closeAction].createDelegate(this, []),
          scope: this,
          text: 'Cancel'
      }]
    });
    Ext.ux.ColorDialog.superclass.constructor.apply(this, [config]);
  },
  initComponent: function() {
    this.width = ( !this.width || this.width < 353 ) ? 353 : this.width;
    this.applyDefaultsCP();
    Ext.ux.ColorDialog.superclass.initComponent.apply( this, arguments );
  },
  onRender: function() {
    Ext.ux.ColorDialog.superclass.onRender.apply( this, arguments );
    this.renderComponent();
  },
  onOk: function(){
    //this.selectColor(); // will fire the 'select' event
    if(this.callback && this.scope){
      this.callback.call( this.scope, this.getColor() );
    }
    this[this.closeAction]();
  },
  show : function(hex, cb, scope){ // override the superclass show() so the callback is not called from show()
    if(!this.rendered){
            this.render(Ext.getBody());
        }
        if(this.hidden === false){
            this.toFront();
            return;
        }
        if(this.fireEvent("beforeshow", this) === false){
            return;
        }
        if(cb){
            this.callback = cb;
        }
        if(scope){
          this.scope = scope;
        }
        this.hidden = false;
        this.beforeShow();
        this.afterShow();
        hex = hex || '000000';
        this.setColor(hex);
  }
});
Ext.applyIf( Ext.ux.ColorDialog.prototype, Ext.ux.ColorPicker.prototype );
/**
 *
 */
Ext.ux.ColorPanel = Ext.extend( Ext.Panel, {
  initComponent: function() {
    this.width = ( !this.width || this.width < 300 ) ? 300 : this.width;
    this.applyDefaultsCP();
    Ext.ux.ColorPanel.superclass.initComponent.apply( this, arguments );
  },
  onRender: function() {
    Ext.ux.ColorPanel.superclass.onRender.apply( this, arguments );
    this.renderComponent();
  }
});
Ext.applyIf( Ext.ux.ColorPanel.prototype, Ext.ux.ColorPicker.prototype );
/**
 * Register Color* for Lazy Rendering
 */
Ext.reg( 'colorpicker', Ext.ux.ColorPicker );
Ext.reg( 'colordialog', Ext.ux.ColorDialog );
Ext.reg( 'colorpanel', Ext.ux.ColorPanel );



/**
 * @class Ext.ux.form.ColorPickerField
 * @extends Ext.form.TriggerField
 * This class makes Ext.ux.ColorPicker available as a form field.
 * @license: BSD
 * @author: Robert B. Williams (extjs id: vtswingkid)
 * @constructor
 * Creates a new ColorPickerField
 * @param {Object} config Configuration options
 * @version 1.1.2
 */

Ext.ux.menu.ColorItem = function(config){
  if(!config)config={};
  config.style="width:350px;";
    Ext.ux.menu.ColorItem.superclass.constructor.call(this, new Ext.ux.ColorPicker(config), config);
    this.picker = this.component;
  this.addEvents('select');
    this.picker.on("render", function(picker){
        picker.getEl().swallowEvent("click");
    });
  this.picker.on("select", this.onSelect, this);  
};
Ext.extend(Ext.ux.menu.ColorItem, Ext.menu.Adapter, {
    // private
    onSelect : function(picker, color){
        this.fireEvent("select", this, color, picker);
        Ext.ux.menu.ColorItem.superclass.handleClick.call(this);
    }
});

Ext.ux.menu.ColorMenu = function(config){
    Ext.ux.menu.ColorMenu.superclass.constructor.call(this, config);
    this.plain = true;
    var ci = new Ext.ux.menu.ColorItem(config);
    this.add(ci);
    this.picker = ci.picker;
    this.relayEvents(ci, ["select"]); 
};
Ext.extend(Ext.ux.menu.ColorMenu, Ext.menu.Menu, {
    beforeDestroy : function() {
        this.picker.destroy();
    }
});