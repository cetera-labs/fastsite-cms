Ext.define('Cetera.Application', {
	extend: 'Ext.app.Application',
	name: 'Cetera',
	//requires: ['Cetera.*'],
    
    scriptsLoading: 0,
      
	launch: function () {
		Ext.ariaWarn = Ext.emptyFn
		Ext.getBody().removeCls('launching')
		var elem = document.getElementById("splash")
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
				expanded: true,
				iconCls: 'tree-folder-visible'
			}
		});		
        
        Ext.create({xtype: 'mainview', plugins: 'viewport'});
        
        if (window.location.hash) {
            this.openBoLink(window.location.hash.substr(1));
        }
        else {    
            var mainTree = Ext.getCmp('main_tree');
            mainTree.expandPath('/root/item-0-1', 'id', '/', function(bSuccess, oLastNode) {
                if (bSuccess && oLastNode.firstChild) {
                    mainTree.getSelectionModel().doSingleSelect(oLastNode.firstChild);
                    mainTree.expandNode(oLastNode.firstChild);
                }
            });
            
            this.activateModule('welcome');
        }          
    },

    buildBoLink: function() {
        
        var mainTree = Ext.getCmp('main_tree');
        var tabs = Ext.getCmp('main_tabs');
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
        var mainTree = Ext.getCmp('main_tree');
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
        var tabs = Ext.getCmp('main_tabs');
        var tab_id = 'tab-'+module;
        var tab = tabs.query('#'+tab_id);
    
        if (tab && tab != '') {
            tabs.setActiveTab(tab_id);
            if (callback) callback(Config.ui.modules[module]['object']);
        } 
		else if (Config.ui.modules[module]) {
            var tab = tabs.add({
                id: tab_id,
                layout: 'fit',
                title: Config.ui.modules[module]['name'],
                iconCls: Config.ui.modules[module]['icon']?'tab-'+module:Config.ui.modules[module]['iconCls']
            });
            tabs.setActiveTab(tab_id);
    
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
    }    
});