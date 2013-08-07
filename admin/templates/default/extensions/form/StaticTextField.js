/**
 * @class Ext.ux.form.StaticTextField
 * @extends Ext.BoxComponent
 * Base class to easily display static text in a form layout.
 * @constructor
 * Creates a new StaticTextField Field
 * @param {Object} config Configuration options
 * @author Based on MiscField by Nullity with modifications by Aparajita Fishman
 */
Ext.namespace('Ext.ux.form');

Ext.ux.form.StaticTextField = function(config){
    this.name = config.name || config.id;
    Ext.ux.form.StaticTextField.superclass.constructor.call(this, config);
};

Ext.extend(Ext.ux.form.StaticTextField, Ext.BoxComponent,  {
    /**
     * @cfg {String/Object} autoCreate A DomHelper element spec, or true for a default element spec (defaults to
     * {tag: "div"})
     */
    defaultAutoCreate : {tag: "div"},

    /**
     * @cfg {String} fieldClass The default CSS class for the field (defaults to "x-form-field")
     */
    fieldClass : "x-form-item-label",

    // private
    isFormField : true,
    
    /**
     * @cfg {Boolean} postValue True to create a hidden field that will post the field's value during a submit
     */
    submitValue : false,

    /**
     * @cfg {Mixed} value A value to initialize this field with.
     */
    value : undefined,
    
    /**
     * @cfg {Boolean} postValue True to use html encode to encode content
     */
    encodeHtml : false,
    
    /**
     * @cfg {Boolean} disableReset True to prevent this field from being reset when calling Ext.form.Form.reset()
     */
    disableReset: false,

    // private
    field: null,
    
    /**
     * Returns the name attribute of the field if available
     * @return {String} name The field name
     */
    getName: function(){
         return this.name;
    },

    // private
    onRender : function(ct, position){
        Ext.ux.form.StaticTextField.superclass.onRender.call(this, ct, position);
        if(!this.el){
            var cfg = this.getAutoCreate();
            this.el = ct.createChild(cfg, position);
        
            if (this.submitValue) {
                this.field = ct.createChild({tag:'input', type:'hidden', name: this.getName(), id: ''}, position);
            }
        }

        this.el.addClass([this.fieldClass, this.cls, 'ux-form-statictextfield']);
        this.initValue();
    },

    // private
    afterRender : function(ct, position){
        Ext.ux.form.StaticTextField.superclass.afterRender.call(this);
        this.initEvents();
    },

    // private
    initValue : function(){
        if(this.value !== undefined){
            this.setValue(this.value);
        }else if(this.el.dom.innerHTML.length > 0){
            this.setValue(this.el.dom.innerHTML);
        }
    },

    /**
     * Returns true if this field has been changed since it was originally loaded.
     */
    isDirty : function() {
        return false;
    },

    /**
     * Resets the current field value to the originally-loaded value
     * @param {Boolean} force Force a reset even if the option disableReset is true
     */
    reset : function(force){
        if(!this.disableReset || force === true){
            this.setValue(this.originalValue);
        }
    },

    // private
    initEvents : function(){
        // reference to original value for reset
        this.originalValue = this.getRawValue();
    },

    /**
     * Returns whether or not the field value is currently valid
     * Always returns true, not used in StaticTextField.
     * @return {Boolean} True
     */
    isValid : function(){
        return true;
    },

    /**
     * Validates the field value
     * Always returns true, not used in StaticTextField.  Required for Ext.form.Form.isValid()
     * @return {Boolean} True
     */
    validate : function(){
        return true;
    },

    processValue : function(value){
        return value;
    },

    // private
    // Subclasses should provide the validation implementation by overriding this
    validateValue : function(value){
        return true;
    },

    /**
     * Mark this field as invalid
     * Not used in StaticTextField.   Required for Ext.form.Form.markInvalid()
     */
    markInvalid : function(){
        return;
    },

    /**
     * Clear any invalid styles/messages for this field
     * Not used in StaticTextField.   Required for Ext.form.Form.clearInvalid()
     */
    clearInvalid : function(){
        return;
    },

    /**
     * Returns the raw field value.
     * @return {Mixed} value The field value
     */
    getRawValue : function(){
       return (this.rendered) ? this.value : null;
    },

    /**
     * Returns the clean field value.
     * @return {String} value The field value
     */
    getValue : function(){
        return this.getRawValue();
    },

    /**
     * Sets the raw field value. The display text is <strong>not</strong> HTML encoded.
     * @param {Mixed} value The value to set
     */
    setRawValue : function(v){
        this.value = v;
        if(this.rendered){
            this.el.dom.innerHTML = v;
            if(this.field){
                this.field.dom.value = v;
            }
        }
    },

    /**
     * Sets the field value. The display text is HTML encoded.
     * @param {Mixed} value The value to set
     */
    setValue : function(v){
        this.value = v;
        if(this.rendered){
        	  if (this.encodeHtml == true) {
        	    this.el.dom.innerHTML = Ext.util.Format.htmlEncode(v);
        	  } else {
        	  	this.el.dom.innerHTML = v;
        	  }
            if(this.field){
                this.field.dom.value = v;
            }
        }
    }
});

Ext.reg('statictextfield', Ext.ux.form.StaticTextField);