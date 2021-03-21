Ext.define('Cetera.fileselect.Panel', {

    extend:'Ext.Panel',
	
    requires: ['Cetera.Ajax','Cetera.model.Folder'],

    fileData: null,
    
  	onDestroy: function(){
		if (this.cropWindow) this.cropWindow.destroy();
        this.callParent(arguments);
  	},

    initComponent : function(){
        
        if (!this.activePanel) this.activePanel = 0;
        
        this.btnUpload = Ext.create('Ext.form.field.File',{
			disabled: true,
			buttonOnly: true,
			style: {
				overflow: 'hidden'
			},		
			buttonConfig: {
				iconCls:'icon-upload',
				text: _('Загрузить файл')
			},
            disabled: true,
            listeners: {
				afterrender: function(el) {
					el.fileInputEl.set({ multiple: 'multiple' });					
				},
				change: function(el, value) {										
					var input = el.fileInputEl.dom;	
					for(var i=0;i<input.files.length;i++){						
						this.uploadFile(input.files[i], i == input.files.length-1);												
					}							
				},
				scope: this
			}
        });
                
        this.btnFolderCreate = new Ext.Button({
            iconCls:'x-fa fa-folder',
            disabled: true,
            tooltip:  _('Создать каталог'),
            handler: this.createFolder,
            scope: this
        });
        
        this.btnFolderDelete = new Ext.Button({
            iconCls:'x-fa fa-folder-minus',
            disabled: true,
            tooltip:  _('Удалить каталог'),
            handler: this.deleteFolder,
            scope: this
        });
        
        this.btnDeleteFile = new Ext.Button({
            iconCls:'x-fa fa-trash',
            disabled: true,
            text:  _('Удалить файл'),
            handler: this.deleteFile,
            scope: this
        });
		
        this.btnCropFile = new Ext.Button({
            iconCls:'x-fa fa-crop-alt',
            disabled: true,
            text:  _('Кадрировать'),
            handler: this.cropFile,
            scope: this
        });		
        
        this.tbar = new Ext.Toolbar({
            region: 'north',
            items: [{
                iconCls:'x-fa fa-sync',
                tooltip: _('Обновить'),
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
            this.btnCropFile, this.btnDeleteFile,'-',
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
             
        this.tree = new Ext.tree.TreePanel({
            title: Config.Lang.directories,
            region:'center',
            rootVisible:true,
            lines:false,
            autoScroll:true,
            border: false,
            bodyBorder: false,
            store: {
				model: 'Cetera.model.Folder',
                proxy: {
                    type: 'ajax',
                    url:  '/cms/include/data_folders.php?defaultExpand=' + this.defaultExpand
                },
                root: {
                    text: 'root',
                    iconCls: 'tree-folder-visible',
                    id: '|',
					readOnly: true
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
            rootProperty: 'rows',
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
                    rootProperty: 'rows'
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
                if (data.type == 13) { // swf
                    data.dim = data.width+'x'+data.height+'px';
                	data.html = '';
                	data.html += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="170" height="145">\n';
                	data.html += '  <param name="movie" value="'+this.comp.path+data.name+'">\n';
                	data.html += '  <param name="quality" value="high">\n';
                	data.html += '  <param name="wmode" value="opaque">\n';
                	data.html += '  <embed src="'+this.comp.path+data.name+'" wmode="opaque" quality="high" width="170" height="145" type="application/x-shockwave-flash" pluginspace="http://www.macromedia.com/go/getflashplayer"></embed>\n';
                	data.html += '</object>';
                } else if (data.type == 99) { // svg
					data.url = this.comp.path+data.name;
					data.html = '<img src="'+data.url+'" title="'+data.name+'" height="145" width="170">';
				} else if (data.type > 0) { // bitmap
                    data.url = '/imagetransform/width_170_height_145_enlarge_0'+this.comp.path+data.name;
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
		    flex: 1,
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
		
		this.centerPanel = new Ext.Panel({
			region: 'center',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			border: false,
			items: [
				this.filesSite
			]
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
			this.centerPanel
            
        ];
        this.layout = 'border';
        
        this.statusBar = Ext.create('Ext.ux.statusbar.StatusBar', {text: '&nbsp;'});
        this.bbar = this.statusBar;
		
		this.btnOkResize = Ext.create('Ext.Button', {
			text: _('Выбрать уменьшенный'),
			scope: this,
			disabled: true,
			menu: {
				plain: true,
				items: [{
					text: _('Оригинальный размер'),
					scope: this,
					handler: function() {
						this.fireEvent('select', this.url, this.file, this.path);
					}
				},{
					text: _('Маленький') + ' (427x320)',
					scope: this,
					handler: function() {
						this.fireEvent('select', '/imagetransform/width_427_height_320'+this.url, this.file, this.path);
					}
				},{
					text: _('Средний') + ' (600x450)',
					scope: this,
					handler: function() {
						this.fireEvent('select', '/imagetransform/width_600_height_450'+this.url, this.file, this.path);
					}
				},{
					text: _('Большой') + ' (800x600)',
					scope: this,
					handler: function() {
						this.fireEvent('select', '/imagetransform/width_800_height_600'+this.url, this.file, this.path);
					}
				},{
					text: _('пользовательский:'),
					scope: this,
					handler: function() {
						var s = this.btnOkResize.menu.getComponent('size');
						var w = s.getComponent('width').getValue();
						var h = s.getComponent('height').getValue();
						this.fireEvent('select', '/imagetransform/width_'+w+'_height_'+h+this.url, this.file, this.path);
					}
				},{
					xtype: 'fieldcontainer',
					itemId: 'size',
					cls: 'x-field',
					layout: 'hbox',
					items: [{
						xtype: 'numberfield',
						width: 50,
						itemId: 'width',
						flex: 1,
						value: 1600,
						step: 100,
						minValue: 0						
					},{ 
						xtype: 'displayfield',
						margin: '0 5',
						value: 'x'
					},{
						xtype: 'numberfield',
						width: 50,
						itemId:  'height',
						flex: 1,
						value: 1200,
						step: 100,
						minValue: 0	
					}]
				}]
			}
		});		
		
		this.btnOk = Ext.create('Ext.Button', {
			text: _('Ok'),
			scope: this,
			disabled: true,
			handler: function() {
				this.fireEvent('select', this.url, this.file, this.path);
			}
		});
		
        this.buttons = [
			this.btnOkResize,
            this.btnOk,
            {
				text: _('Отмена'), 
				scope: this,
				handler: function() {
					this.fireEvent('cancel');
				}
			}
        ]			
        
        this.historyStore.load();
        
        this.historyCnt.on('collapse', function() {
            var elDom = Ext.getDom(this.historyCnt.getId() + '-xcollapsed');
            if (elDom) {
                elDom.innerHTML = Config.Lang.recent;
                elDom.style.padding = '2px 0 0 5px';
            }
        }, this);
		
		//this.files.on('dblclick', selectedHandler);
		//this.files2.on('dblclick', selectedHandler);		

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
		
		this.isImage = false;
		
		if(sel.length > 0) {
		    this.files.getSelectionModel().doSelect(sel, false, true);
		    this.files2.getSelectionModel().doSelect(sel, false, true);
            var data = sel[0];
            this.fileData = data;
			this.file = data.get('name');
			if (this.path+this.file != this.url)
    		this.url = this.path+this.file;  
			var ext = this.file.split('.').pop().toLowerCase();
			if (ext == 'jpeg' ||ext == 'jpg' ||ext == 'gif' ||ext == 'png')
				this.isImage = true;
		}
		else{
    		    this.files.getSelectionModel().clearSelections();
    		    this.files2.getSelectionModel().clearSelections();
    		    this.fileData = null;
    		    this.file = '';
    		    this.url = '';
		}
			
        this.btnDeleteFile.setDisabled(sel.length == 0);
		this.btnOk.setDisabled(sel.length == 0);
		this.btnCropFile.setDisabled(!this.isImage);
		this.btnOkResize.setDisabled(!this.isImage);		
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
    },
	
    cropFile: function () {
		this.getCropWindow().show().setValue( this.path+this.file );
    },
	
    getCropWindow : function() {
		if (!this.cropWindow) {
			this.cropWindow = Ext.create('Cetera.window.ImageCrop');
			this.cropWindow.on('crop',function(value){
				this.reloadFiles( value.split('/').pop() );	
			},this);
        }
        return this.cropWindow;      
    },

	uploadFile: function(file, files_reload) {
		var me = this;
		var path = this.path;
			
		var formData = new FormData();
		formData.append('file', file); 
		
		var prefix = _('Загрузка')+': '+file.name;
		
		var progress = Ext.create('Ext.ProgressBar', {
			bodyCls: 'x-window-body-default',        
			cls: 'x-window-body-default',
			text: prefix + ' - ' + _('ожидание'),
			margin: 3
		});
		me.centerPanel.insert(0,progress);

		Cetera.Ajax.request({
			url: '/cms/include/action_files.php?action=upload&path='+path,
			timeout: 1000000,
			method: 'POST',
			rawData: formData,
			ignoreHeaders: true,
			success: function(resp) {
				var obj = Ext.decode(resp.responseText);
				if (obj.success) {								
					if (files_reload && path == me.path) me.reloadFiles(obj.file);								
				}
				setTimeout(function(){
					me.centerPanel.remove(progress, true);
				},500);				
			},
			failure: function(resp) {
				if (files_reload && path == me.path) me.reloadFiles();
				
				setTimeout(function(){
					me.centerPanel.remove(progress, true);
				},5000);
				
				var msg = _('Ошибка!');
				var o = Ext.decode(resp.responseText);
				if (o.message) msg += ' '+o.message;				
				progress.updateProgress( 0, prefix + ' - ' + msg );
			},
			uploadProgress: function(e) {
				progress.updateProgress( e.loaded/e.total, prefix + ' - ' + parseInt(e.loaded*100/e.total)+'%' );
			},
			scope: me
		});							
		
	}
});