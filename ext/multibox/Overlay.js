/*
---

script: Overlay.js

description: An overlay plugin

license: MIT-style license

authors:
- Samuel Birch

requires:
- core:1.2.4

provides: [Overlay]

...
*/


var Overlay = new Class({
	
	getOptions: function(){
		return {
			colour: '#000',
			opacity: 0.7,
			zIndex: 1,
			container: document.body,
			_onClick: $empty
		};
	},

	initialize: function(options){
		this.setOptions(this.getOptions(), options);
		
		this.options.container = $(this.options.container);
		
		this.container = new Element('div').setProperty('id', 'OverlayContainer').setStyles({
			position: 'absolute',
			left: '0px',
			top: '0px',
			width: '100%',
			zIndex: this.options.zIndex
		}).injectInside(this.options.container);
		
		this.iframe = new Element('iframe').setProperties({
			'id': 'OverlayIframe',
			'name': 'OverlayIframe',
			'src': 'javascript:void(0);',
			'frameborder': 0,
			'scrolling': 'no'
		}).setStyles({
			'position': 'absolute',
			'top': 0,
			'left': 0,
			'width': '100%',
			'height': '100%',
			'filter': 'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)',
			'opacity': 0,
			'zIndex': 1
		}).injectInside(this.container);
		
		this.overlay = new Element('div').setProperty('id', 'Overlay').setStyles({
			position: 'absolute',
			left: '0px',
			top: '0px',
			width: '100%',
			height: '100%',
			zIndex: 2,
			backgroundColor: this.options.colour
		}).injectInside(this.container);
		
		if(this.options._onClick){
			this.container.addEvent('click', function(){
				this.options._onClick.call(this)
			}.bind(this));
		}
		
		//this.fade = new Fx.Tween(this.container).set('opacity', 0);
		this.container.fade('hide');
		this.position();
		
		window.addEvent('resize', this.position.bind(this));
	},
	
	reset: function(){
		this.container.dispose();
		this.position();
		this.container.inject(this.options.container);
	},
	
	setOnClick: function(func){
		this.container.addEvent('click', func);
	},
	
	position: function(){ 
		if(this.options.container == document.body){ 
			var h = window.getScrollHeight()+'px'; 
			this.container.setStyles({top: '0px', height: h}); 
		}else{ 
			var myCoords = this.options.container.getCoordinates(); 
			this.container.setStyles({
				top: myCoords.top+'px', 
				height: myCoords.height+'px', 
				left: myCoords.left+'px', 
				width: myCoords.width+'px'
			}); 
		} 
	},
	
	show: function(){
		//this.fade.start(0,this.options.opacity);
		this.container.fade(this.options.opacity);
	},
	
	hide: function(){
		//this.fade.start(this.options.opacity,0);
		this.container.fade('out');
	}
	
});
Overlay.implement(new Options);

