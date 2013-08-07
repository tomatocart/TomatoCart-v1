/** 
 * $Id: overrides.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com

 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */

/**
 * Keep window in viewport and no shadows by default for IE performance
 */

Ext.Window.override({
  shadow : false,
  constrainHeader : true
});

Ext.grid.GridPanel.override({
  border: false,
  viewConfig: {
    emptyText: TocLanguage.gridNoRecords
  } 
});

/**
 * Finds the index of the first matching Record in this store by a specific field value.
 * @param {String} fieldName The name of the Record field to test.
 * @param {Mixed} value The value to match the field against.
 * @param {Number} startIndex (optional) The index to start searching at
 * @return {Number} The matched index or -1
 */
Ext.override(Ext.data.Store, {
	findExact: function(property, value, start){
		return this.data.findIndexBy(function(rec){
		  return rec.get(property) === value;
		}, this, start);
	}
});

/**
 * Override the renderItem method of FormLayout 
 * Makes labelStyle work independently of other config options
 */
 
 Ext.override(Ext.layout.FormLayout, {
  renderItem : function(c, position, target){
    if(c && !c.rendered && c.isFormField && c.inputType != 'hidden'){
      var args = [
           c.id, c.fieldLabel,
           (this.labelStyle||'') + ';' + (c.labelStyle||''),
           this.elementStyle||'',
           typeof c.labelSeparator == 'undefined' ? this.labelSeparator : c.labelSeparator,
           (c.itemCls||this.container.itemCls||'') + (c.hideLabel ? ' x-hide-label' : ''),
           c.clearCls || 'x-form-clear-left' 
      ];
      if(typeof position == 'number'){
        position = target.dom.childNodes[position] || null;
      }
      if (position) {
        this.fieldTpl.insertBefore(position, args);
      }else {
        this.fieldTpl.append(target, args);
      }
      c.render('x-form-el-'+c.id);
    } else {
      Ext.layout.FormLayout.superclass.renderItem.apply(this, arguments);
    }
  }
});



/**
 * BUG Fix for: When I click a cell of editable-grid, the gridBody will scroll to Left.
 *
 */
Ext.override(Ext.grid.GridView, {
  initTemplates : function(){
    var ts = this.templates || {};
    if(!ts.master){
      ts.master = new Ext.Template(
        '<div class="x-grid3" hidefocus="true">',
          '<div class="x-grid3-viewport">',
            '<div class="x-grid3-header"><div class="x-grid3-header-inner"><div class="x-grid3-header-offset" style="{ostyle}">{header}</div></div><div class="x-clear"></div></div>',
            '<div class="x-grid3-scroller"><div class="x-grid3-body" style="{bstyle}">{body}</div><a href="#" class="x-grid3-focus" tabIndex="-1"></a></div>',
          '</div>',
          '<div class="x-grid3-resize-marker">&nbsp;</div>',
          '<div class="x-grid3-resize-proxy">&nbsp;</div>',
        '</div>'
      );
    }
    if(!ts.header){
      ts.header = new Ext.Template(
        '<table border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
        '<thead><tr class="x-grid3-hd-row">{cells}</tr></thead>',
        '</table>'
      );
    }
    if(!ts.hcell){
      ts.hcell = new Ext.Template(
        '<td class="x-grid3-hd x-grid3-cell x-grid3-td-{id} {css}" style="{style}"><div {tooltip} {attr} class="x-grid3-hd-inner x-grid3-hd-{id}" unselectable="on" style="{istyle}">', this.grid.enableHdMenu ? '<a class="x-grid3-hd-btn" href="#"></a>' : '',
        '{value}<img class="x-grid3-sort-icon" src="', Ext.BLANK_IMAGE_URL, '" />',
        '</div></td>'
      );
    }
    if(!ts.body){
      ts.body = new Ext.Template('{rows}');
    }
    if(!ts.row){
      ts.row = new Ext.Template(
        '<div class="x-grid3-row {alt}" style="{tstyle}"><table class="x-grid3-row-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
        '<tbody><tr>{cells}</tr>',
        (this.enableRowBody ? '<tr class="x-grid3-row-body-tr" style="{bodyStyle}"><td colspan="{cols}" class="x-grid3-body-cell" tabIndex="0" hidefocus="on"><div class="x-grid3-row-body">{body}</div></td></tr>' : ''),
        '</tbody></table></div>'
      );
    }
    if(!ts.cell){
      ts.cell = new Ext.Template(
        '<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}" tabIndex="0" {cellAttr}>',
        '<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on" {attr}>{value}</div>',
        '</td>'
      );
    }
    for(var k in ts){
      var t = ts[k];
      if(t && typeof t.compile == 'function' && !t.compiled){
        t.disableFormats = true;
        t.compile();
      }
    }
    this.templates = ts;
    this.colRe = new RegExp("x-grid3-td-([^\\s]+)", "");
  },
  updateAllColumnWidths : function(){
    var tw = this.getTotalWidth();
    var clen = this.cm.getColumnCount();
    var ws = [];
    for(var i = 0; i < clen; i++){
      ws[i] = this.getColumnWidth(i);
    }
    this.innerHd.firstChild.style.width = this.getOffsetWidth();
    this.innerHd.firstChild.firstChild.style.width = tw;
    this.mainBody.dom.style.width = tw;
    for(var i = 0; i < clen; i++){
      var hd = this.getHeaderCell(i);
      hd.style.width = ws[i];
    }
    var ns = this.getRows(), row, trow;
    for(var i = 0, len = ns.length; i < len; i++){
      row = ns[i];
      row.style.width = tw;
      if(row.firstChild){
        row.firstChild.style.width = tw;
        trow = row.firstChild.rows[0];
        for (var j = 0; j < clen; j++) {
          trow.childNodes[j].style.width = ws[j];
        }
      }
    }
    this.onAllColumnWidthsUpdated(ws, tw);
  },
  updateColumnWidth : function(col, width){
    var w = this.getColumnWidth(col);
    var tw = this.getTotalWidth();
    this.innerHd.firstChild.style.width = this.getOffsetWidth();
    this.innerHd.firstChild.firstChild.style.width = tw;
    this.mainBody.dom.style.width = tw;
    var hd = this.getHeaderCell(col);
    hd.style.width = w;
    var ns = this.getRows(), row;
    for(var i = 0, len = ns.length; i < len; i++){
      row = ns[i];
      row.style.width = tw;
      if(row.firstChild){
        row.firstChild.style.width = tw;
        row.firstChild.rows[0].childNodes[col].style.width = w;
      }
    }
    this.onColumnWidthUpdated(col, w, tw);
  },
  updateColumnHidden : function(col, hidden){
    var tw = this.getTotalWidth();
    this.innerHd.firstChild.style.width = this.getOffsetWidth();
    this.innerHd.firstChild.firstChild.style.width = tw;
    this.mainBody.dom.style.width = tw;
    var display = hidden ? 'none' : '';
    var hd = this.getHeaderCell(col);
    hd.style.display = display;
    var ns = this.getRows(), row;
    for(var i = 0, len = ns.length; i < len; i++){
      row = ns[i];
      row.style.width = tw;
      if(row.firstChild){
        row.firstChild.style.width = tw;
        row.firstChild.rows[0].childNodes[col].style.display = display;
      }
    }
    this.onColumnHiddenUpdated(col, hidden, tw);
    delete this.lastViewWidth;
    this.layout();
  },
  afterRender: function(){
    this.mainBody.dom.innerHTML = this.renderRows() || '&nbsp;';
    this.processRows(0, true);
    if(this.deferEmptyText !== true){
      this.applyEmptyText();
    }
  },
  renderUI : function(){
    var header = this.renderHeaders();
    var body = this.templates.body.apply({rows: '&nbsp;'});
    var html = this.templates.master.apply({
      body: body,
      header: header,
      ostyle: 'width:'+this.getOffsetWidth()+';',
      bstyle: 'width:'+this.getTotalWidth()+';'
    });
    var g = this.grid;
    g.getGridEl().dom.innerHTML = html;
    this.initElements();
    Ext.fly(this.innerHd).on("click", this.handleHdDown, this);
    this.mainHd.on("mouseover", this.handleHdOver, this);
    this.mainHd.on("mouseout", this.handleHdOut, this);
    this.mainHd.on("mousemove", this.handleHdMove, this);
    this.scroller.on('scroll', this.syncScroll, this);
    if(g.enableColumnResize !== false){
      this.splitZone = new Ext.grid.GridView.SplitDragZone(g, this.mainHd.dom);
    }
    if(g.enableColumnMove){
      this.columnDrag = new Ext.grid.GridView.ColumnDragZone(g, this.innerHd);
      this.columnDrop = new Ext.grid.HeaderDropZone(g, this.mainHd.dom);
    }
    if(g.enableHdMenu !== false){
      if(g.enableColumnHide !== false){
        this.colMenu = new Ext.menu.Menu({id:g.id + "-hcols-menu"});
        this.colMenu.on("beforeshow", this.beforeColMenuShow, this);
        this.colMenu.on("itemclick", this.handleHdMenuClick, this);
      }
      this.hmenu = new Ext.menu.Menu({id: g.id + "-hctx"});
      this.hmenu.add(
        {id:"asc", text: this.sortAscText, cls: "xg-hmenu-sort-asc"},
        {id:"desc", text: this.sortDescText, cls: "xg-hmenu-sort-desc"}
      );
      if(g.enableColumnHide !== false){
        this.hmenu.add('-',
          {id:"columns", text: this.columnsText, menu: this.colMenu, iconCls: 'x-cols-icon'}
        );
      }
      this.hmenu.on("itemclick", this.handleHdMenuClick, this);
    }
    if(g.trackMouseOver){
      this.mainBody.on("mouseover", this.onRowOver, this);
      this.mainBody.on("mouseout", this.onRowOut, this);
    }
    if(g.enableDragDrop || g.enableDrag){
      this.dragZone = new Ext.grid.GridDragZone(g, {
        ddGroup : g.ddGroup || 'GridDD'
      });
    }
    this.updateHeaderSortState();
  },
  onColumnWidthUpdated : function(col, w, tw){
    // empty
  },
  onAllColumnWidthsUpdated : function(ws, tw){
    // empty
  },
  onColumnHiddenUpdated : function(col, hidden, tw){
    // empty
  },
  getOffsetWidth: function() {
    return (this.cm.getTotalWidth() + this.scrollOffset) + 'px';
  },
  renderBody : function(){
    var markup = this.renderRows() || '&nbsp;';
    return this.templates.body.apply({rows: markup});
  },
  hasRows : function(){
    var fc = this.mainBody.dom.firstChild;
    return fc && fc.nodeType == 1 && fc.className != 'x-grid-empty';
  },
  updateHeaders : function(){
    this.innerHd.firstChild.innerHTML = this.renderHeaders();
    this.innerHd.firstChild.style.width = this.getOffsetWidth();
    this.innerHd.firstChild.firstChild.style.width = this.getTotalWidth();
  }
});

