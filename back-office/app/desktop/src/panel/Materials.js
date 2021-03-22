Ext.define('Cetera.panel.Materials', {

    extend:'Ext.grid.Panel',
    alias : 'widget.materials',

    mat_type: 0,   
	
	requires: [
        'Cetera.model.Material',
        'Ext.Responsive'
    ],  
    
    border: false, 
    stripeRows: true, 
    multiSelect: true,                 
              
    columns: [
        {
            width: 25, 
            sortable: false, 
            dataIndex: 'icon', 
            renderer: function (value) {
                if (value == '1')
                    return '<span class="x-fas fa-eye"></span>';
                    else return '<span class="x-fas fa-eye-slash"></span>';
            }
        },
        {
            header: _('Сорт.'), width: 75, dataIndex: 'tag',
            responsiveConfig: {
                small: {
                    hidden: true
                },
                large: {
                    hidden: false
                }
            },            
        },
        {
            header: "ID", width: 75, dataIndex: 'id',
            responsiveConfig: {
                small: {
                    width: 50,
                    hidden: true
                },
                large: {
                    width: 75,
                    hidden: false
                }
            },              
        },
        {
			header: _('Заголовок'), width: 75, dataIndex: 'name', flex:1,
            renderer: function (value,meta,rec) {
				if (rec.get('locked')) value += '<br><small>' + Ext.String.format(Config.Lang.materialLocked, rec.get('locked_login')) + '</small>';
                return value;
            }, 			
		},
        {
            header: "Alias", width: 175, dataIndex: 'alias',
            responsiveConfig: {
                small: {
                    width: 100
                },
                large: {
                    width: 175
                }
            },            
        },
        {
            header: _('Дата'), width: 120, dataIndex: 'dat', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'),
            responsiveConfig: {
                small: {
                    width: 80,
                    renderer: Ext.util.Format.dateRenderer('d.m.y'),
                },
                large: {
                    width: 120,
                    renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'),
                }
            },             
        },
        {
            header: _('Автор'), width: 100, dataIndex: 'autor',
            visible: false,
            responsiveConfig: {
                small: {
                    hidden: true
                },
                large: {
                    hidden: false
                }
            },            
        },
		{
			header: _('Раздел'), 
			width: 100, 
			dataIndex: 'catalog',
			getSortParam: function(){ return 'E.name'; },
            responsiveConfig: {
                small: {
                    hidden: true
                },
                large: {
                    hidden: false
                }
            },            
		}
    ],
     
    reload: function() {
        this.store.load({params:{start: 0}});
    },
	
	editorClass: function() {
		return 'MaterialEditor' + this.mat_type.charAt(0).toUpperCase() + this.mat_type.substr(1, this.mat_type.length-1 );
	},
           
    edit: function(id) {
        if (this.editWindow) this.editWindow.destroy();
        this.editWindow = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        this.store.load();
                        this.stopInactivityTimer();
                    },
                    scope: this
                }
            }
        });

        var win = this.editWindow;
        win.show();
        
        cc = Ext.create( 'Cetera.panel.MaterialEdit' , {
            win: win,
            objectId: id,
            objectDefinitionId: this.mat_type,            
        }); 
        if (cc) { 
            cc.show(); 
            this.fireEvent('material_editor_ready', win, cc);
        }
       
        // Таймер неактивности
        this.clearInactivityTimeout();
        this.timeoutTask = Ext.TaskManager.start({
             run: function() {
             
                if (this.globalTimeout <= 0) {
                    // таймаут сработал, останавливаем таймер
                    this.stopInactivityTimer();
                    this.editWindow.materialForm.saveAction(0,0,1);
                } else {
                    this.globalTimeout = this.globalTimeout - 1;
                }
             
             },
             interval: 1000,
             scope: this
        });

        Ext.EventManager.addListener("main_body", 'click', this.clearInactivityTimeout, this);
        Ext.EventManager.addListener("main_body", 'keypress', this.clearInactivityTimeout, this);
        
    },
    
    clearInactivityTimeout: function() {
        this.globalTimeout = 28800;
    },
    
    stopInactivityTimer: function() {
         Ext.TaskManager.stop(this.timeoutTask);
    },
      
    deleteMat: function() {
        Ext.MessageBox.confirm(Config.Lang.materialDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
      
    call: function(action, cat) {
        Ext.Ajax.request({
            url: '/cms/include/action_materials.php',
            params: { 
                action: action, 
                type: this.mat_type, 
                'sel[]': this.getSelected(),
                cat: cat
            },
            scope: this,
            success: function(resp) {
                this.store.load();
            }
        });
    },
    
    move: function(action) {
    
        var title = Config.Lang.move;
        if (action == 'copy')
            title = Config.Lang.copy;
    
        if (!this.siteTree) {
            this.siteTree = Ext.create('Cetera.window.SiteTree', {
                expand   : '/root/item-0-1',
                nolink : 1,
                rule   : '5u6',
				norootselect: 1,
                only   : this.mat_type
            });
            this.siteTree.on('select', function(res) {
                this.call(this.siteTree.action, res.id)
            },this);
        }
        this.siteTree.action = action;
        this.siteTree.setTitle(title);
        this.siteTree.show();   
    },
    
    getSelected: function() {
        var a = this.getSelectionModel().getSelection();
        ret = [];
        for (var i=0; i<a.length; i++) ret[i] = a[i].getId();
        return ret;
    },  

	getToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {items: [
            {
                itemId:  'tb_mat_new',
                iconCls: 'x-far fa-file',
                tooltip: _('Создать'),
                handler: function () { this.edit(0); },
                scope:   this
            },
            {
                itemId: 'tb_mat_edit',
                disabled: true,
                iconCls: 'x-fa fa-edit',
                tooltip: _('Изменить'),
                handler: function () { this.edit(this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            {
                itemId: 'tb_mat_delete',
                disabled: true,
                iconCls: 'x-fa fa-trash',
                tooltip: _('Удалить'),
                handler: function () { this.deleteMat(); },
                scope: this
            },
            {
                itemId: 'tb_mat_pub',
                disabled: true,
                iconCls:'icon-pub',
                tooltip: Config.Lang.publish,
                handler: function() { this.call('pub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_unpub',
                disabled: true,
                iconCls:'icon-unpub',
                tooltip: Config.Lang.unpublish,
                handler: function() { this.call('unpub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_move',
                disabled: true,
                iconCls:'icon-move',
                tooltip: Config.Lang.move,
                handler: function () { this.move('move'); },
                scope: this
            },
            {
                itemId: 'tb_mat_copy',
                disabled: true,
                iconCls:'icon-copy',
                tooltip: Config.Lang.copy,
                handler: function () { this.move('copy'); },
                scope: this
            }			
        ]});		
	},
	
	viewConfig: {
		getRowClass: function(record){
			return record.get("locked") ? "locked" : "";
		}
	},	
	
	getStore: function() {
		return Ext.create('Ext.data.JsonStore', {   
	
			autoDestroy: true,
			remoteSort: true,

			model: Cetera.model.Material,	
			
			totalProperty: 'total',
			pageSize: Config.defaultPageSize,
			sorters: [{property: "dat", direction: "DESC"}],
			proxy: {
				type: 'ajax',
				url: '/cms/include/data_materials.php',
				simpleSortMode: true,
				reader: {
					type: 'json',
					rootProperty: 'rows'
				},
				extraParams: {
					'id'   : 0, 
					'type' : this.mat_type,
					limit  : Config.defaultPageSize
				}
			}		
		
		});
	},
	
	getBBar: function() {
		return Ext.create('Ext.PagingToolbar', {
            store: this.store,
            items: [Config.Lang.filter + ': ', Ext.create('Cetera.field.Search', {
                store: this.store,
                paramName: 'query',
                width:200
            })]
        });
	},
    
	onSelectionChange: function(sm){
		var hs = sm.hasSelection();
		var sf = this.store.sorters.first().property;
		
		this.toolbar.getComponent('tb_mat_edit').setDisabled(!hs);
		this.toolbar.getComponent('tb_mat_delete').setDisabled(!hs);
		this.toolbar.getComponent('tb_mat_pub').setDisabled(!hs);
		this.toolbar.getComponent('tb_mat_unpub').setDisabled(!hs);
		this.toolbar.getComponent('tb_mat_move').setDisabled(!hs);
		this.toolbar.getComponent('tb_mat_copy').setDisabled(!hs);
	},
	
	onBeforeSelect: function(t , record, index, eOpts) {
		if (record.get('disabled')) return false;
	},
	
	onCellDblclick: function() {
		this.edit(this.getSelectionModel().getSelection()[0].getId());
	},
	
    initComponent : function() {
              
		this.store = this.getStore();        
        this.bbar = this.getBBar();        
        this.tbar = this.getToolbar();
		this.toolbar = this.tbar;
                                       
        this.callParent();  
		
        this.getSelectionModel().on({
            'selectionchange' : this.onSelectionChange,
            'beforeselect' : this.onBeforeSelect,
            scope:this
        });
            		

		if (this.mat_type) {
			this.on({
				'celldblclick' : this.onCellDblclick,
				scope: this
			});  			
			this.reload();
		}
        
    }
                
});