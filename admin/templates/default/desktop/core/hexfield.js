/**
 * @author t.murdock
 *
 * @class Ext.ux.form.HexField
 * @extends Ext.form.TextField
 * 
 * Will only allow Hex values
 */

Ext.namespace("Ext.ux.form");

Ext.ux.form.HexField = Ext.extend(Ext.form.TextField,  {
  initEvents : function(){
    Ext.ux.form.HexField.superclass.initEvents.call(this);
    
    var keyPress = function(e){
      var k = e.getKey();
      
      if(!Ext.isIE && (e.isSpecialKey() || k == e.BACKSPACE || k == e.DELETE)){
        return;
      }
      
      // allowed text characters
      var allowed = '0123456789abcdefABCDEF';
      
      // get selected text length
      var selection = new Ext.ux.form.HexField.Selection( document.getElementById(this.id) );
      var s = selection.create();
      var selLength = s.end - s.start;
      
      // get text character keyed in
      var c = e.getCharCode();
      var c = String.fromCharCode(c);
      var value = c + this.getValue();
      
      if(allowed.indexOf(c) === -1 || (value.length > 6 && selLength === 0) ){
        e.stopEvent();
      }
    };
    this.el.on("keypress", keyPress, this);
  }
  
  , validateValue : function(value){
    if(!Ext.ux.form.HexField.superclass.validateValue.call(this, value)){
      return false;
    }
    if(!/^[0-9a-fA-F]{6}$/.test(value)){
      return false;
    }
    return true;
    }
});
Ext.reg('hexfield', Ext.ux.form.HexField);

Ext.ux.form.HexField.Selection = function(textareaElement) {
    this.element = textareaElement;
};

Ext.ux.form.HexField.Selection.prototype.create = function() {
    if (document.selection != null && this.element.selectionStart == null) {
        return this._ieGetSelection();
    } else {
        return this._mozillaGetSelection();
    }
}

Ext.ux.form.HexField.Selection.prototype._mozillaGetSelection = function() {
    return { 
        start: this.element.selectionStart, 
        end: this.element.selectionEnd 
    };
}

Ext.ux.form.HexField.Selection.prototype._ieGetSelection = function() {
    this.element.focus();

    var range = document.selection.createRange();
    var bookmark = range.getBookmark();

    var contents = this.element.value;
    var originalContents = contents;
    var marker = this._createSelectionMarker();
    while(contents.indexOf(marker) != -1) {
        marker = this._createSelectionMarker();
    }

    var parent = range.parentElement();
    if (parent == null || parent.type != "text") {
        return { start: 0, end: 0 };
    }
    range.text = marker + range.text + marker;
    contents = this.element.value;

    var result = {};
    result.start = contents.indexOf(marker);
    contents = contents.replace(marker, "");
    result.end = contents.indexOf(marker);

    this.element.value = originalContents;
    range.moveToBookmark(bookmark);
    range.select();
  
    return result;
}

Ext.ux.form.HexField.Selection.prototype._createSelectionMarker = function() {
    return "##SELECTION_MARKER_" + Math.random() + "##";
}