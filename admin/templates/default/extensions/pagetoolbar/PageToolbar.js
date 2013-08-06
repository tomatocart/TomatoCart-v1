/*
 * Ext JS Library 2.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext.PageToolbar
 * @extends Ext.Toolbar
 * A specialized toolbar that is bound to a {@link Ext.data.Store} and provides automatic paging controls.
 * @constructor
 * Create a new PageToolbar
 * @param {Object} config The config object
 */
Ext.PageToolbar = Ext.extend(Ext.Toolbar, {
    /**
     * @cfg {Ext.data.Store} store The {@link Ext.data.Store} the paging toolbar should use as its data source (required).
     */
    /**
     * @cfg {Boolean} displayInfo
     * True to display the displayMsg (defaults to false)
     */
    /**
     * @cfg {Number} pageSize
     * The number of records to display per page (defaults to 20)
     */
    pageSize: 20,
    /**
     * @cfg {String} displayMsg
     * The paging status message to display (defaults to "Displaying {0} - {1} of {2}").  Note that this string is
     * formatted using the braced numbers 0-2 as tokens that are replaced by the values for start, end and total
     * respectively. These tokens should be preserved when overriding this string if showing those values is desired.
     */
    displayMsg : 'Displaying {0} - {1} of {2}',
    /**
     * @cfg {String} emptyMsg
     * The message to display when no records are found (defaults to "No data to display")
     */
    emptyMsg : 'No data to display',
    /**
     * Customizable piece of the default paging text (defaults to "Page")
     * @type String
     */
    beforePageText : "Page",
    /**
     * Customizable piece of the default paging text (defaults to "of %0")
     * @type String
     */
    afterPageText : "of {0}",
    /**
     * Customizable piece of the default paging text (defaults to "First Page")
     * @type String
     */
    firstText : "First Page",
    /**
     * Customizable piece of the default paging text (defaults to "Previous Page")
     * @type String
     */
    prevText : "Previous Page",
    /**
     * Customizable piece of the default paging text (defaults to "Next Page")
     * @type String
     */
    nextText : "Next Page",
    /**
     * Customizable piece of the default paging text (defaults to "Last Page")
     * @type String
     */
    lastText : "Last Page",
    /**
     * Customizable piece of the default paging text (defaults to "Refresh")
     * @type String
     */
    refreshText : "Refresh",

    prevStepText : "Previous Step",
    nextStepText : "Next Step",

    /**
     * Object mapping of parameter names for load calls (defaults to {start: 'start', limit: 'limit'})
     */
    paramNames : {start: 'start', limit: 'limit'},

  	/**
  	 * extends
  	 */
  	btnsConfig : [],
  	steps:5,
  	pageConfig : {first: true, prev: true, next:true, last: true, loading: true,field: true, prevstep: true, nextstep: true, pagepanel: true, dispalyNum: true},

  	initStep : function(){
  		//this.steps = this.steps%2?this.steps:this.steps+1;
  		this.steps = this.steps;
  	},
  	getHalfStep : function(){
  		return parseInt(this.steps/2);
  	},

    initComponent : function(){
        Ext.PageToolbar.superclass.initComponent.call(this);
        this.cursor = 0;
        this.bind(this.store);
    },

    // private
    onRender : function(ct, position){
        Ext.PageToolbar.superclass.onRender.call(this, ct, position);

		//
		this.initStep();

		for (var i=0;i<=this.btnsConfig.length-1 ;i++ ){
			this.btngroup = this.addButton({
				text:this.btnsConfig[i].text,
				tooltip: this.btnsConfig[i].tooltip,
				iconCls: this.btnsConfig[i].iconCls,
				handler: this.btnsConfig[i].handler
			});
			this.addSeparator();
		}

        this.first = this.addButton({
            tooltip: this.firstText,
            iconCls: "x-tbar-page-first",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["first"])
        });
        this.firstbar = this.addSeparator();

		// prev 10 
        this.prevstep = this.addButton({
            tooltip: this.prevStepText,
            iconCls: "x-tbar-page-prevstep",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["prevstep"])
        });
        this.prevstepbar = this.addSeparator();
		//end prev 10 

        this.prev = this.addButton({
            tooltip: this.prevText,
            iconCls: "x-tbar-page-prev",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["prev"])
        });
        this.prevbar = this.addSeparator();

		for (var n=1;n<=this.steps;n++ ){
			this.pagePanel = this.addButton({
				id: this.id+'_btn'+n,
				text:'',
				handler: this.onClick.createDelegate(this, ["jump",n])
			});
		}
        this.pagepanelbar = this.addSeparator();

		this.beforeTextEl = this.add(this.beforePageText);

        this.field = Ext.get(this.addDom({
           tag: "input",
           type: "text",
           size: "3",
           value: "1",
           cls: "x-tbar-page-number"
        }).el);
        this.field.on("keydown", this.onPagingKeydown, this);
        this.field.on("focus", function(){this.dom.select();});
        this.afterTextEl = this.addText(String.format(this.afterPageText, 1));
        this.field.setHeight(18);
        this.fieldbar = this.addSeparator();



        this.next = this.addButton({
            tooltip: this.nextText,
            iconCls: "x-tbar-page-next",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["next"])
        });
        this.nextbar = this.addSeparator();
		//nextstep
        this.nextstep = this.addButton({
            tooltip: this.nextStepText,
            iconCls: "x-tbar-page-nextstep",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["nextstep"])
        });
        this.nextstepbar = this.addSeparator();
		//end nextstep

        this.last = this.addButton({
            tooltip: this.lastText,
            iconCls: "x-tbar-page-last",
            disabled: true,
            handler: this.onClick.createDelegate(this, ["last"])
        });

        this.nextstepbar = this.addSeparator();        

        this.displayNum =this.addItem(new Ext.form.ComboBox({
          triggerAction: 'all',
          store: new Ext.data.SimpleStore({
            fields:['text'],
            data:[['5'],['10'],['20'],['30'],['40'],['50']]
          }),
          displayField: 'text',
          valueField: 'text',
          mode: 'local',
          readOnly: true,
          value: this.pageSize,
          width: 40,
          listeners: {
            select: this.onClick.createDelegate(this, ["displaynum"]),
            scope: this
          }
        }));
        
        this.lastbar = this.addSeparator();
        this.loading = this.addButton({
            tooltip: this.refreshText,
            iconCls: "x-tbar-loading",
            handler: this.onClick.createDelegate(this, ["refresh"])
        });

        if(this.displayInfo){
            this.displayEl = Ext.fly(this.el.dom).createChild({cls:'x-paging-info'});
        }
        if(this.dsLoaded){
            this.onLoad.apply(this, this.dsLoaded);
        }
    },

    // private
    updateInfo : function(){
        if(this.displayEl){
            var count = this.store.getCount();
            var msg = count == 0 ?
                this.emptyMsg :
                String.format(
                    this.displayMsg,
                    this.cursor+1, this.cursor+count, this.store.getTotalCount()
                );
            this.displayEl.update(msg);
        }
    },

    // private
    onLoad : function(store, r, o){
        if(!this.rendered){
            this.dsLoaded = [store, r, o];
            return;
        }
		//
		this.initStep();

       this.cursor = o.params ? o.params[this.paramNames.start] : 0;
       var d = this.getPageData(), ap = d.activePage, ps = d.pages;

		if (ps<=this.steps){
			for (var n=1;n<=ps;n++ ){
			   Ext.getCmp(this.id+'_btn'+n).setText(n);
			   if (ap==n){
				Ext.getCmp(this.id+'_btn'+n).el.child("button").setStyle({'font-weight': 'bold'});
			   }else{
				Ext.getCmp(this.id+'_btn'+n).el.child("button").setStyle({'background':'','font-weight': 'normal'});
			   }
			   Ext.getCmp(this.id+'_btn'+n).handler = this.onClick.createDelegate(this, ["jump",n]);
			}
		  for (var n=1;n<=this.steps;n++ ){
		    if (n <= ps) {
		      Ext.getCmp(this.id+'_btn'+n).setDisabled(false);
		    } else {
		      Ext.getCmp(this.id+'_btn'+n).setText('');
          Ext.getCmp(this.id+'_btn'+n).setDisabled(true);
		    }
      }	
		}else{
			for (var n=1,j=(ap-this.getHalfStep())>=1?(ap-this.getHalfStep()):1;n<=this.steps;n++,j++ ){
			   Ext.getCmp(this.id+'_btn'+n).setText(j);
			   if (ap==j){
				Ext.getCmp(this.id+'_btn'+n).el.child("button").setStyle({'font-weight': 'bold'});
			   }else{
				Ext.getCmp(this.id+'_btn'+n).el.child("button").setStyle({'background':'','font-weight': 'normal'});
			   }
			   Ext.getCmp(this.id+'_btn'+n).handler = this.onClick.createDelegate(this, ["jump",j]);
			   if (ap+n-this.getHalfStep()-2>=ps){
					Ext.getCmp(this.id+'_btn'+n).setDisabled(true);
					Ext.getCmp(this.id+'_btn'+n).setText('');
			   }else{
					Ext.getCmp(this.id+'_btn'+n).setDisabled(false);
			   }
			}
		}

       this.field.dom.value = ap;
       this.first.setDisabled(ap == 1);
       this.prev.setDisabled(ap == 1);
       this.next.setDisabled(ap == ps);
       this.last.setDisabled(ap == ps);
       this.prevstep.setDisabled(ap < this.steps);
       this.nextstep.setDisabled(ap > ps-this.steps);

       this.loading.enable();
       this.updateInfo();

	   this.first.setVisible(this.pageConfig.first);
	   this.firstbar.setVisible(this.pageConfig.first);

	   this.prev.setVisible(this.pageConfig.prev);
	   this.prevbar.setVisible(this.pageConfig.prev);

	   this.prevstep.setVisible(this.pageConfig.prev);
	   this.prevstepbar.setVisible(this.pageConfig.prev);

	   this.next.setVisible(this.pageConfig.next);
	   this.nextbar.setVisible(this.pageConfig.next);

	   this.last.setVisible(this.pageConfig.last);
	   this.lastbar.setVisible(this.pageConfig.last);

	   this.loading.setVisible(this.pageConfig.loading);

	   this.field.setVisible(this.pageConfig.field);
	   this.fieldbar.setVisible(this.pageConfig.field);

	   this.prevstep.setVisible(this.pageConfig.prevstep);
	   this.prevstepbar.setVisible(this.pageConfig.prevstep);

	   this.nextstep.setVisible(this.pageConfig.nextstep);
	   this.nextstepbar.setVisible(this.pageConfig.nextstep);
	   
     this.displayNum.setVisible(this.pageConfig.dispalyNum); 
     this.nextstepbar.setVisible(this.pageConfig.dispalyNum); 
	   
	   this.pagepanelbar.setVisible(this.pageConfig.pagepanel);
    },

    // private
    getPageData : function(){
        var total = this.store.getTotalCount();
        return {
            total : total,
            activePage : Math.ceil((this.cursor+this.pageSize)/this.pageSize),
            pages :  total < this.pageSize ? 1 : Math.ceil(total/this.pageSize)
        };
    },

    // private
    onLoadError : function(){
        if(!this.rendered){
            return;
        }
        this.loading.enable();
    },

    readPage : function(d){
        var v = this.field.dom.value, pageNum;
        if (!v || isNaN(pageNum = parseInt(v, 10))) {
            this.field.dom.value = d.activePage;
            return false;
        }
        return pageNum;
    },

    // private
    onPagingKeydown : function(e){
        var k = e.getKey(), d = this.getPageData(), pageNum;
        if (k == e.RETURN) {
            e.stopEvent();
            if(pageNum = this.readPage(d)){
                pageNum = Math.min(Math.max(1, pageNum), d.pages) - 1;
                this.doLoad(pageNum * this.pageSize);
            }
        }else if (k == e.HOME || k == e.END){
            e.stopEvent();
            pageNum = k == e.HOME ? 1 : d.pages;
            this.field.dom.value = pageNum;
        }else if (k == e.UP || k == e.PAGEUP || k == e.DOWN || k == e.PAGEDOWN){
            e.stopEvent();
            if(pageNum = this.readPage(d)){
                var increment = e.shiftKey ? 10 : 1;
                if(k == e.DOWN || k == e.PAGEDOWN){
                    increment *= -1;
                }
                pageNum += increment;
                if(pageNum >= 1 & pageNum <= d.pages){
                    this.field.dom.value = pageNum;
                }
            }
        }
    },

    // private
    beforeLoad : function(){
        if(this.rendered && this.loading){
            this.loading.disable();
        }
    },

    doLoad : function(start){
        var o = {}, pn = this.paramNames;
        o[pn.start] = start;
        o[pn.limit] = this.pageSize;
        this.store.load({params:o});
    },
    
    displaynum : function(start){
    	  this.pageSize = parseInt(this.displayNum.getValue());
        var o = {}, pn = this.paramNames;
        o[pn.start] = start;
        o[pn.limit] = this.pageSize;
        this.store.load({params:o});
    },    

    // private
    onClick : function(which,jumpto){
        var store = this.store;
        switch(which){
            case "first":
                this.doLoad(0);
            break;
            case "prev":
                this.doLoad(Math.max(0, this.cursor-this.pageSize));
            break;
            case "next":
                this.doLoad(this.cursor+this.pageSize);
            break;
            case "last":
                var total = store.getTotalCount();
                var extra = total % this.pageSize;
                var lastStart = extra ? (total - extra) : total-this.pageSize;
                this.doLoad(lastStart);
            break;
            case "refresh":
                this.doLoad(this.cursor);
            break;
      			case "jump":
      				this.doLoad(this.pageSize*(jumpto-1));
      			break;
      			case "prevstep":
      				this.doLoad(this.cursor-this.steps*this.pageSize);
      			break;
      			case "nextstep":
      				this.doLoad(this.cursor+this.steps*this.pageSize);
      			break;
            case "displaynum":
              this.displaynum(0);	
            break;
      
        }
    },

    /**
     * Unbinds the paging toolbar from the specified {@link Ext.data.Store}
     * @param {Ext.data.Store} store The data store to unbind
     */
    unbind : function(store){
        store = Ext.StoreMgr.lookup(store);
        store.un("beforeload", this.beforeLoad, this);
        store.un("load", this.onLoad, this);
        store.un("loadexception", this.onLoadError, this);
        this.store = undefined;
    },

    /**
     * Binds the paging toolbar to the specified {@link Ext.data.Store}
     * @param {Ext.data.Store} store The data store to bind
     */
    bind : function(store){
        store = Ext.StoreMgr.lookup(store);
        store.on("beforeload", this.beforeLoad, this);
        store.on("load", this.onLoad, this);
        store.on("loadexception", this.onLoadError, this);
        this.store = store;
    }
});

Ext.reg('paging', Ext.PageToolbar);