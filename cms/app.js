Ext.Loader.setPath('Ext.ux', './app/ux');

var tabs = null;
var mainTree = null;
    
Ext.Ajax.on('requestexception', function(conn, resp, opt) {

    if (opt.failure) return;

    try {
        var obj = Ext.decode(resp.responseText);
    } catch (e) {
        obj = false;
    }
    
    var msg = Config.Lang.requestException;
    var ext_message = null;//'url: '+opt.url+'<br>method: '+opt.method+'<br>params: '+Ext.Object.toQueryString(opt.params, true);
    
    if (obj) if (obj.message) msg = obj.message;
        
    if (msg) {
        
        var win = Ext.create('Cetera.window.Error', {
            msg: msg,
            ext_msg: obj?obj.ext_message:ext_message
        });
        win.show();

    }
});

Ext.override(Ext.form.Basic, {
	findInvalid: function() {
		var me = this,
			invalid;
		Ext.suspendLayouts();
		invalid = me.getFields().filterBy(function(field) {
			var preventMark = field.preventMark, isValid;
			field.preventMark = true;
			isValid = field.isValid() && !field.hasActiveError();
			field.preventMark = preventMark;
			return !isValid;
		});
		
		Ext.resumeLayouts(true);
		return invalid;
	}
});

Ext.override(Ext.Component, {
    ensureVisible: function(stopAt) {
        var p;
        this.ownerCt.bubble(function(c) {
            if (p = c.ownerCt) {
                if (p instanceof Ext.TabPanel) {
                    p.setActiveTab(c);
                } else if (p.layout.setActiveItem) {
                    p.layout.setActiveItem(c);
                }
            }
            return (c !== stopAt);
        });
        this.el.scrollIntoView(this.el.up(':scrollable'));
        return this;
    }
});

Ext.DomQuery.pseudos.scrollable = function(c, t) {
    var r = [], ri = -1;
    for(var i = 0, ci; ci = c[i]; i++){
        var o = ci.style.overflow;
        if(o=='auto'||o=='scroll') {
            //if (ci.scrollHeight < Ext.fly(ci).getHeight(true)) 
				r[++ri] = ci;
        }
    }
    return r;
};

Ext.application({

    name: 'Cetera',
    
    viewport: null,
	
	msgCt: null,
    
    views: ['ModalLoadingAnimation'],
	
	scriptsLoading: 0,
        
    init: function() {
    
        var me = this;
        me.loading = Ext.widget('loading');    
          
        Ext.require('Cetera.window.Error');
		Ext.require('Cetera.main.Tree');
		Ext.require('Cetera.main.Header');
		//Ext.require('Cetera.Viewport');
		Ext.require('Cetera.field.RichMatsetMaterialAbstract');
		
		Ext.state.Manager.setProvider(Ext.create('Ext.state.CookieProvider'));
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
    
    reload: function() {
    
        this.loading.show();        
        document.location.reload();    
  
    },   

    launch: function() {
    
		var me = this;
	
		Config.setLocale(Config.locale, function(){
			
			if (!Config.user) {
			
				me.login = Ext.create('Cetera.login.Login', {
					listeners: {
						login: {
							fn: function(data){
								Config.user = data.user;
								me.login.destroy();  
								if (data.locale && Config.locale != data.locale) {
									Config.locale = data.locale;
								}
								me.launch();
							}
						}
					}            
				});
			
			} else {
			
				me.showUI();
			
			}			
			
		});

    },
    
    showUI: function() {
    
        this.loading.show();  
        //return;
        
        Ext.Ajax.request({
            url: 'include/data_ui.php',
            scope: this,
            success: function(response, opts) {
                var res = Ext.decode(response.responseText);
                Config.modules = res.modules;
                Config.menu = res.menu;
                
				this.scriptsLoading = 0;
                Ext.Array.each(res.scripts, function(script) {
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
            }
        });  
    }, 
	
	scriptLoaded: function() {	
		this.scriptsLoading--;
		if (this.scriptsLoading == 0) this.buildUI();
	},
    
    buildUI: function() {
    
        Ext.Object.each(Config.modules, function(key, value) {
            if (!value.icon) return;
          	if (Ext.isIE) {
          	    document.styleSheets[0].addRule(".tab-"+key,"background-image:url("+value.icon+")!important");
          	} else {
                document.styleSheets[0].insertRule('.tab-'+key+' { background-image:url('+value.icon+')!important;}',document.styleSheets[0].cssRules.length);
            }
        }, this);    
        
        tabs = Ext.create('Ext.TabPanel', {
            region:'center',
            deferredRender:false,
            activeTab:0,
            enableTabScroll:true,
            defaults: {
                autoScroll:true,
                closable:true
            },
            //plugins: Ext.create('Ext.ux.TabCloseMenu'),
            listeners: {
                tabchange : function( tp , tab ) {
                    tab.doLayout();
                    Cetera.getApplication().buildBoLink();
                },
                remove : function() {
                    Cetera.getApplication().buildBoLink();
                }
            }
        }); 
               
        tabs.on('beforetabchange', function(tp, newTab, currentTab) { 
            if (currentTab && currentTab.content) currentTab.content.fireEvent('deactivate');
            if (newTab && newTab.content) newTab.content.fireEvent('activate');
        }); 

		Ext.create('Ext.data.TreeStore', {
			model: 'Cetera.model.SiteTree',
			storeId: 'structureMain',
			rootProperty: {
				text: 'root',
				id: 'root',
				expanded: true,
				iconCls: 'tree-folder-visible'
			},
			root: {
				text: 'root',
				id: 'root',
				expanded: true,
				iconCls: 'tree-folder-visible'
			}			
		});		
    
		mainTree = Ext.create('Cetera.main.Tree');
        this.viewport = Ext.create('Cetera.Viewport');
        
        if (window.location.hash)
            this.openBoLink(window.location.hash.substr(1));
            else {            
                mainTree.expandPath('/root/item-0', 'id', '/', function(bSuccess, oLastNode) {
                    if (bSuccess && oLastNode.firstChild) {
                        mainTree.getSelectionModel().doSingleSelect(oLastNode.firstChild);
                        mainTree.expandNode(oLastNode.firstChild);
                    }
                });
                
                this.activateModule('welcome');
            }           
        
        this.loading.hide();
		
    },
    
    buildBoLink: function() {

        var link = 'catalog:' + mainTree.getSelectedPath();
        if (tabs.items.getCount()) {
            m = [];
            var active = '';
            var act = tabs.getActiveTab();
            for (var i = 0; i < tabs.items.getCount(); i++) {
                var a = tabs.items.getAt(i).id.split('-');
                m[i] = a[1];
                if (act && tabs.items.getAt(i).id == act.id) active = a[1];
            }
            
            link += '$modules:' + m + '$active:' + active;
        }
        window.location.hash = link;

    },
    
    openBoLink: function(link){
        var actions = link.split('$');
        for(var i=0; i<actions.length; i++) {
            var data = actions[i].split(':');
            
            if (data[0] == 'catalog') {
                mainTree.selectPath(data[1], 'id');
            }
            
            if (data[0] == 'modules') {
                var m = data[1].split(',');
                for(var j=0; j<m.length; j++) this.activateModule(m[j]);
            }
            
            if (data[0] == 'active') {
                this.activateModule(data[1]);
            }
            
            if (data[0] == 'user') {
                this.activateModule('users', function(obj) {
                    //obj.filter.setValue(data[1]);
                    // obj.filter.onTrigger2Click();
				            obj.edit(data[1]);
                });
            }
			
			if (data[0] == 'material') {
                this.activateModule('materials', function(obj) {
				            obj.edit(0,data[1],data[2]);
                });
            }
            
        }
    },
    
    activateModule: function(module, callback) {
        var tab_id = 'tab-'+module;
        var tab = tabs.query('#'+tab_id);
    
        if (tab && tab != '')
		{
        
            tabs.setActiveTab(tab_id);
            if (callback) callback(Config.modules[module]['object']);
            
        } 
		else if (Config.modules[module])
		{
        
            var tab = tabs.add({
                id: tab_id,
                layout: 'fit',
                title: Config.modules[module]['name'],
                iconCls: Config.modules[module]['icon']?'tab-'+module:Config.modules[module]['iconCls']
            });
            tabs.setActiveTab(tab_id);
    
            if (Config.modules[module]['html'])
			{
            
                tab.add({
                    border: false,
                    loader: {
                        url:  Config.modules[module]['html'],
                        autoLoad: true,
                        scripts : true
                    }
                });
            
            } 
			else if (Config.modules[module]['loaded'] || !Config.modules[module]['url'])
			{
            
                this.createModulePanel(tab, module, callback);
                
            } 
			else
			{
            
                Ext.Loader.loadScript({
                    url: Config.modules[module]['url'],
                    scope: this,
                    onLoad: function() { 
                        this.createModulePanel(tab, module, callback);
                    }
                });
    
            }
            
        }
    },
    
    createModulePanel: function(tab, module, callback) {
        if (!Config.modules[module]['class']) return;
    
       // try {
            var p = Ext.create(Config.modules[module]['class']);
            
            if (!p) return;
            tab.add(p);
            tab.doLayout();
            tab.content = p;
            Config.modules[module]['object'] = p;

            if (callback) callback(p);			
            
       /* } catch (e) {
            tabs.remove(tab);
    
            Ext.Msg.show({
                title:   Config.Lang.error,
                msg:     e.message,
                icon:    Ext.MessageBox.ERROR,
                buttons: Ext.Msg.OK
            });
        }   */
        
    }       
}); 