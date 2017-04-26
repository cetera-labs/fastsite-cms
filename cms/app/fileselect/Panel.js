Ext.define('Cetera.fileselect.Panel', {

    extend:'Ext.Panel',

    fileData: null,
    
  	onDestroy: function(){
    		this.uploadWindow.close();
        this.callParent(arguments);
  	},

    initComponent : function(){
        
        if (!this.activePanel) this.activePanel = 0;
        
        this.btnUpload = new Ext.Button({
            iconCls:'icon-upload',
            disabled: true,
            tooltip:'<b>' + Config.Lang.doUpload2 + '</b>',
            handler: function () { 
                if (this.tree.getSelectionModel().getLastSelected()) {
                    this.uploadWindow.setPath(this.path);
                    this.uploadWindow.show();
                }
            },
            scope: this
        });
                
        this.btnFolderCreate = new Ext.Button({
            iconCls:'icon-new_folder',
            disabled: true,
            tooltip:  Config.Lang.dirCreate,
            handler: this.createFolder,
            scope: this
        });
        
        this.btnFolderDelete = new Ext.Button({
            iconCls:'icon-folder_delete',
            disabled: true,
            tooltip:  Config.Lang.dirDelete,
            handler: this.deleteFolder,
            scope: this
        });
        
        this.btnDeleteFile = new Ext.Button({
            iconCls:'icon-delete',
            disabled: true,
            tooltip:  Config.Lang.fileDelete,
            handler: this.deleteFile,
            scope: this
        });
        
        this.tbar = new Ext.Toolbar({
            region: 'north',
            items: [{
                iconCls:'icon-reload',
                tooltip: Config.Lang.refresh,
                handler: function () { 
                    var sn = this.tree.getSelectionModel().getLastSelected();
                    if (sn) var path = sn.getPath();
					var store = this.tree.getStore();
                    store.load(store.getNodeById('|'),function() {
                        if (path) this.tree.selectPath(path, 'id');
                    }, this);
                
                },
                scope: this
            },'-',
            this.btnUpload,'-',
            this.btnDeleteFile,'-',
            this.btnFolderCreate,this.btnFolderDelete,'-',{
                iconCls: 'icon-table',
                toggleGroup: '1',
                pressed: (this.activePanel==0)?true:false,
                tooltip: Config.Lang.fvTable,
                scope: this,
                allowDepress: false,
                handler: function (b) {
                    if (b.pressed) this.filesSite.getLayout().setActiveItem(0);
                }
            },{
                iconCls: 'icon-thumbs',
                toggleGroup: '1',
                pressed: (this.activePanel==1)?true:false,
                tooltip: Config.Lang.fvPreview,
                scope: this,
                allowDepress: false,
                handler: function (b) {
                    if (b.pressed) this.filesSite.getLayout().setActiveItem(1);
                }
            }
            ]
        });
                
        this.uploadWindow = Ext.create('Cetera.window.Upload', { path: this.path});
        this.uploadWindow.on('successUpload', function(info){
           this.reloadFiles(info.file);
        }, this);
    
        this.tree = new Ext.tree.TreePanel({
            title: Config.Lang.directories,
            region:'center',
            rootVisible:true,
            lines:false,
            autoScroll:true,
            border: false,
            bodyBorder: false,
            store: {
                proxy: {
                    type: 'ajax',
                    url:  '/cms/include/data_folders.php?defaultExpand=' + this.defaultExpand
                },
                root: {
                    text: 'root',
                    iconCls: 'tree-folder-visible',
                    id: '|'
                }
            },
            
            listeners: {
                'selectionchange': {
                    fn: this.onTreeSelected,
                    scope: this
                }
            }
        });
               
        this.historyStore = new Ext.data.JsonStore({
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_folders_history.php'
            },
            root: 'rows',
            fields: ['path']
        });
        
        this.history = new Ext.list.ListView({
            store: this.historyStore,
            singleSelect: true,  
            hideHeaders: true,  
            border: false,
            bodyBorder: false,
            columns: [{
                dataIndex: 'path', flex: 1
            }],
            listeners: {
                'selectionchange': {
                    fn: this.onHistorySelected,
                    scope: this
                }
            }
        });
        
        this.filesStore = new Ext.data.JsonStore({
            autoDestroy: true,
            fields: [
                'name', 
                {name:'size', type: 'float'}, 
                {name:'lastmod', type:'date', dateFormat:'timestamp'},
                'type', 'width', 'height'
            ],
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_files.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    root: 'rows'
                },
                extraParams: {
                    extension: this.extension
                }
            }  
        });  
            
        this.files = new Ext.list.ListView({
            store: this.filesStore,
            loadingText: Config.Lang.wait,
            region: 'center',
            singleSelect: true,
            cls: 'x-panel-body',
            deferEmptyText: false,
            emptyText: '-- '+Config.Lang.noFiles+' --',            
            columns: [{
                header: Config.Lang.name,
                dataIndex: 'name',
                flex: .7
            },{
                header: Config.Lang.date,
                xtype: 'datecolumn',
                format: 'd-m-Y H:i',
                flex: .15, 
                dataIndex: 'lastmod'
            },{
                header: Config.Lang.size,
                dataIndex: 'size',
                tpl: '{size:fileSize}',
                flex: .15, 
                align: 'right'
            }],
            listeners: {
                'selectionchange': {
                    fn: this.onFileSelected,
                    scope: this
                }
            }
        });
        
        this.files2 = Ext.create('Ext.DataView', {
            store: this.filesStore,
            loadingText: Config.Lang.wait,
            cls: 'images-view',
            comp: this,
            tpl: new Ext.XTemplate(
        		'<tpl for=".">',
                    '<div class="thumb-wrap" id="{name}">',
            		    '<div class="thumb"><table width="100%" height="100%"><tr><td>{html}</td></tr></table></div>',
            		    '<span>{name}</span>',
            		    '<span>{size:fileSize}</span>',
            		    '<span>{dim}</span>',
                    '</div>',
                '</tpl>',
                '<div class="x-clear"></div>'
        	  ),
            prepareData: function(data){
                if (data.type == 13) {
                    data.dim = data.width+'x'+data.height+'px';
                	data.html = '';
                	data.html += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="170" height="145">\n';
                	data.html += '  <param name="movie" value="'+this.comp.path+data.name+'">\n';
                	data.html += '  <param name="quality" value="high">\n';
                	data.html += '  <param name="wmode" value="opaque">\n';
                	data.html += '  <embed src="'+this.comp.path+data.name+'" wmode="opaque" quality="high" width="170" height="145" type="application/x-shockwave-flash" pluginspace="http://www.macromedia.com/go/getflashplayer"></embed>\n';
                	data.html += '</object>';
                } else if (data.type > 0) {
                    data.url = '/cms/include/image.php?src='+this.comp.path+data.name+'&width=170&height=145&cache=1&dontenlarge=1&quality=90';
                    data.dim = data.width+'x'+data.height+'px';
                    data.html = '<img src="'+data.url+'" title="'+data.name+'">';
                } else {
                    data.url = 'images/file.png';
                    data.dim = '';
                    data.html = '<img src="'+data.url+'" title="'+data.name+'">';
                }
                return data;
            },
            singleSelect: true,
            overItemCls:'x-view-over',
            trackOver: true,
            itemSelector:'div.thumb-wrap',
            deferEmptyText: false,
            emptyText: '-- '+Config.Lang.noFiles+' --',
           
            listeners: {
            	selectionchange: {
            		fn: this.onFileSelected,
            		scope: this
            	}
            }
        });
		
		this.filesSite = new Ext.Panel({
		    region: 'center',
		    border: false,
            layout: 'card',
            activeItem: this.activePanel,
            items: [this.files, this.files2]
        });
        
        this.historyCnt = new Ext.Panel({
            collapsed: true, 
            collapsible: true,
            animFloat: false,
            animCollapse: false,
            region: 'south',
            height: 140,
            layout: 'fit',
            items: this.history
        });
		
        this.items = [
            {
                region: 'west',
                collapsed: (this.hideFolders)?true:false,
                width: 200,
                collapsible: true,
                animFloat: false,
                animCollapse: false,
                split:true,
                border: false,
                layout: 'border',
                items: [
                    this.tree,
                    this.historyCnt
                ]
            },
            this.filesSite
        ];
        this.layout = 'border';
        
        this.statusBar = Ext.create('Ext.ux.StatusBar', {text: '&nbsp;'});
        this.bbar = this.statusBar;
        
        this.historyStore.load();
        
        this.historyCnt.on('collapse', function() {
            var elDom = Ext.getDom(this.historyCnt.getId() + '-xcollapsed');
            if (elDom) {
                elDom.innerHTML = Config.Lang.recent;
                elDom.style.padding = '2px 0 0 5px';
            }
        }, this);

        this.on('render', function() {
            if (!this.tree.getRootNode().isExpanded())
                this.tree.getRootNode().expand(false, function(node) {
                    if (!this.dontLoadFiles)
                        this.selectLastExpanded(this.tree.getRootNode());
                }, this);
        } ,this);
           
        this.callParent();
    },
    
    selectLastExpanded: function(node){
        var found = false;
        node.eachChild(function(n) {
            if (n.isExpanded()) {
                found = true;
                this.selectLastExpanded(n);
            }
        }, this);
        if (!found) this.tree.getSelectionModel().doSingleSelect(node);
    },

    onTreeSelected: function(sm, node) {
        node = node[0];
        if (node) {
            var ro = node.get('readOnly');
            this.btnUpload.setDisabled(ro);
            this.btnFolderCreate.setDisabled(ro);
            this.btnFolderDelete.setDisabled(ro);
            var regexp = /\|/g;
            this.path = node.getId().replace(regexp,'/');
            this.filesStore.load({
                params:{ path: node.getId()},
                scope: this,
                callback: function() {
                    this.historyStore.load();
                }
            });
            this.statusBar.setStatus({text: this.path});
        } else {
            this.btnUpload.setDisabled(true);
            this.btnFolderCreate.setDisabled(true);
            this.btnFolderDelete.setDisabled(true);
            this.path = false;
            this.files2.path = this.path;
            this.statusBar.setStatus({text: '&nbsp;'});
        }
    },
    
    onHistorySelected: function(dv, sel) {
    		if(sel.length > 0){
            var folder = sel[0].get('path');
            var a = folder.split('/');
            var path = '';
            var item = '';
            for(var i=0; i<a.length; i++) {
                item += a[i] + '|';
                path += '/' + item;
            }
    		    this.tree.selectPath(path,'id');
    		}
    },

    onFileSelected: function(dv, sel) {
        this.btnDeleteFile.setDisabled(sel.length == 0);
		    if(sel.length > 0) {
		        this.files.getSelectionModel().doSelect(sel, false, true);
		        this.files2.getSelectionModel().doSelect(sel, false, true);
            var data = sel[0];
            this.fileData = data;
			      this.file = data.get('name');
			      if (this.path+this.file != this.url)
    			     this.url = this.path+this.file;
            
		    }else{
    		    this.files.getSelectionModel().clearSelections();
    		    this.files2.getSelectionModel().clearSelections();
    		    this.fileData = null;
    		    this.file = '';
    		    this.url = '';
		    }
		    this.fireEvent('select');
    },
    
    reloadFiles: function(fileName) {
        this.files.getStore().load({
            params:{
                path: this.tree.getSelectionModel().getLastSelected().getId()
            },
            callback: function() {
                if (fileName) {
                    var n = this.files.getStore().find('name', fileName);
                    if (n >= 0) {
                        this.files.getSelectionModel().select(n);
                        this.files2.getSelectionModel().select(n);
                    }
                }
            },
            scope: this
        });
    },
    
    createFolder: function() {
        Ext.MessageBox.prompt(Config.Lang.dirCreate, Config.Lang.name+':    ', function(btn, name) {
            if (btn == 'ok')
                Ext.Ajax.request({
                    url: '/cms/include/action_files.php',
                    params: { 
                        action: 'create_folder', 
                        path: this.path,
                        name: name
                    },
                    scope: this,
                    success: function(resp) {
                        var tree = this.tree;
                        var node = this.tree.getSelectionModel().getLastSelected();
                        var path = node.getPath() + '/' + node.getId() + name + '|';
                        this.reloadTreeNode(node, function(){
                              tree.selectPath(path, 'id', '/');
                        });
                    },
                    scope: this
                });
        }, this);
    },
    
    reloadTreeNode: function(node, callback) {
        var path = node.getPath();
        var tree = this.tree;
        tree.getStore().load({
            node: node,
            callback: function() {
                tree.selectPath(path, 'id', '/', function(bSuccess, oLastNode) {
                    if (bSuccess) oLastNode.expand();
                    if (callback) callback();
                });
            }
        });
    },
    
    deleteFolder: function() {
        Ext.MessageBox.confirm(Config.Lang.dirDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes')
                Ext.Ajax.request({
                    url: '/cms/include/action_files.php',
                    params: { 
                        action: 'delete_folder', 
                        path: this.path
                    },
                    scope: this,
                    success: function(resp) {
                        this.reloadTreeNode(this.tree.getSelectionModel().getLastSelected().parentNode);
                    }
                });
        }, this);
    },
    
    deleteFile: function () {
        Ext.MessageBox.confirm(Config.Lang.fileDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes')
                Ext.Ajax.request({
                    url: '/cms/include/action_files.php',
                    params: { 
                        action: 'delete', 
                        path: this.path+this.file
                    },
                    scope: this,
                    success: function(resp) {
                        this.reloadFiles();
                    }
                });
        }, this);
    }
});