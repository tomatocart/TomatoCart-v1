/*
 * Ext JS Library 2.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */
Ext.namespace("Ext.ux");

Ext.ux.Gadget = function(config) {
	config = config || {};
	
	config.anchor = '100%';
	config.cls = 'x-gadget';
	
	Ext.ux.Gadget.superclass.constructor.call(this, config);
}

Ext.extend(Ext.ux.Gadget, Ext.Panel, {
  task: Ext.emptyFn
});

Ext.reg('gadget', Ext.ux.Gadget);