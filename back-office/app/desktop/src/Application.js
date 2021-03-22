Ext.define('Cetera.Application', {
	extend: 'Ext.app.Application',
	name: 'Cetera',
	requires: [
        'Cetera.*',
        'Ext.form.*',
        'Ext.Responsive'
    ],  
    
    scriptsLoading: 0,
    showAfterBuild: null,
    
    routes: {
        ':module/:params': 'showModule',
        ':module': 'showModule',
    },     
      
	launch: function () {
		Ext.ariaWarn = Ext.emptyFn
		var elem = document.getElementById("launching")
		elem.parentNode.removeChild(elem)

		var me = this;
	
		Config.setLocale(Config.locale, function(){
			
			if (Config.user) {
				me.launchBackOffice();
			}
            else {
                Ext.create('Cetera.window.Login');
            }
			
		});

	},
    
    reload: function() {
        document.location.reload();      
    },    
    
    launchBackOffice: function () {
        
        this.scriptsLoading = 0;
        Ext.Array.each(Config.ui.scripts, function(script) {
            var me = this;
            this.scriptsLoading++;
            Ext.Loader.loadScript({
                url: script,
                onLoad: me.scriptLoaded,
                onError: me.scriptLoaded,
                scope: me
            });  
        }, this);                
        
        if (!this.scriptsLoading) this.buildUI();
    },
    
	scriptLoaded: function() {	
		this.scriptsLoading--;
		if (this.scriptsLoading == 0) this.buildUI();
	},    
    
    buildUI: function () {
        Ext.Object.each(Config.extLoaderPath, function(key,value) {
            Ext.Loader.setPath(key, value);
        }, this);         
        
        Ext.Object.each(Config.ui.modules, function(key, value) {
            if (!value.icon) return;
          	if (Ext.isIE) {
          	    document.styleSheets[0].addRule(".tab-"+key,"background-image:url("+value.icon+")!important");
          	} else {
                document.styleSheets[0].insertRule('.tab-'+key+' { background-image:url('+value.icon+')!important;}',document.styleSheets[0].cssRules.length);
            }
        }, this);         
        
		Ext.create('Ext.data.TreeStore', {
			model: 'Cetera.model.SiteTree',
			storeId: 'structureMain',
			root: {
				text: 'root',
				id: 'root',
				expanded: true
			}
		});
        
		Ext.create('Ext.data.TreeStore', {
			storeId: 'navigationMain',
            root: {
                text: 'root',
                expanded: true,
                children: this.getNavigation()
            }
		});	        
        
        Ext.create({
            xtype: 'mainview', 
            plugins: 'viewport',
        });
        
        var mainTree = Ext.getCmp('main_tree');
        
        if (this.showAfterBuild)  {
            this.showModule(this.showAfterBuild.module, this.showAfterBuild.params, this.showAfterBuild.callback);
        }
        else {
            this.redirectTo('welcome');
        }
    },
        
    showModule: function(module, params, callback) {
               
        var tabs = Ext.getCmp('main_panel');
        
        if (!tabs) {
            this.showAfterBuild = {
                module: module,
                params: params,
                callnack: callback
            }
            return;
        }
        
        //console.log(module);
        
        var tab_id = 'tab-'+module;
        var tab = tabs.query('#'+tab_id);
    
        if (tab && tab != '') {
            tabs.setActiveItem(tab_id);
            if (callback) callback(Config.ui.modules[module]['object']);
        } 
		else if (Config.ui.modules[module]) {
            var tab = tabs.add({
                id: tab_id,
                layout: 'fit',
                closable: false,
                title: Config.ui.modules[module]['name'],
                iconCls: Config.ui.modules[module]['iconCls']?Config.ui.modules[module]['iconCls']:'tab-'+module
            });
            tabs.setActiveItem(tab_id);
    
            if (Config.ui.modules[module]['html']) {
                tab.add({
                    border: false,
                    loader: {
                        url:  Config.ui.modules[module]['html'],
                        autoLoad: true,
                        scripts : true
                    }
                });
            
            } 
			else if (Config.ui.modules[module]['loaded'] || !Config.ui.modules[module]['url']) {
                this.createModulePanel(tab, module, callback);
            } 
			else {
            
                Ext.Loader.loadScript({
                    url: Config.ui.modules[module]['url'],
                    scope: this,
                    onLoad: function() { 
                        this.createModulePanel(tab, module, callback);
                    }
                });
    
            }
            
        }        
    },
    
    createModulePanel: function(tab, module, callback) {
        if (!Config.ui.modules[module]['class']) return;

        var p = Ext.create(Config.ui.modules[module]['class']);
        
        if (!p) return;
        tab.add(p);
        tab.updateLayout();
        tab.content = p;
        Config.ui.modules[module]['object'] = p;

        if (callback) callback(p);			
    },

    editUser: function(id, callback, scope) {
        
		var win = Ext.create('Cetera.window.MaterialEdit', { 
			title: 'Пользователь',
			listeners: {
				close: {
					fn:  callback,
					scope: scope
				}
			}
		});
		
		win.show();
        
        var cc = Ext.create( 'Cetera.panel.MaterialEdit' , {
            win: win,
            objectId: id,
            sectionId: -2,          
        }); 
        cc.show();        
        
    },
    
    msg : function(title, format, delay){
            if(!this.msgCt){
               this.msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
            }
            var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(this.msgCt, '<div class="msg"><h3>' + title + '</h3><p>' + s + '</p></div>', true);
            m.hide();
			if (!delay) delay=1000;
            m.slideIn('t').ghost("t", { delay: delay, remove: true});
    },
    
    getNavigation: function() {
    
        return this.buildMenu(Config.ui.menu);

    },
    
    buildMenu: function(items) {
        
        var res = [];
        Ext.Object.each(items, function(key, value) {
            var item = {
                text    : value.name,
                iconCls : value.iconCls?value.iconCls:'tab-'+value.id,
                id      : value.id,
                leaf    : !value.items || value.items.length == 0,
                children: []            
            }     
            
            if (value.items && value.items.length) {
                Ext.Object.each(value.items, function(k, v) {
                    var id = v.id;
                    if (value.id) {
                        id = value.id + '-' + id;
                    }
                    item.children.push({
                        text    : v.name,
                        iconCls : v.iconCls?v.iconCls:'tab-'+id,
                        id      : id,
                        leaf    : true,          
                    });     
                }, this);              
            }
               
            res.push(item);
        }, this);  
        
        return res;  
    },    
});