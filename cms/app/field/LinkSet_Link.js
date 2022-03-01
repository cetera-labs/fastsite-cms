Ext.define('Cetera.field.LinkSet_Link', {

    extend: 'Cetera.field.Panel',
	requires: 'Cetera.model.Material',
	
	hideEmptyLabel: true,
      
    onResize : function(w, h){
        this.callParent(arguments);
        this.panel.setSize(w - this.getLabelWidth(), h);
    },
        
    getPanel : function() {
		     
		this.store = Ext.create('Ext.data.JsonStore', {
			autoLoad: true,
			autoDestroy: true,
			remoteSort: true,

            fields: [
                'id','name','alias','catalog','icon','type_id','field_name','field_describ'
            ],
			
			totalProperty: 'total',
			proxy: {
				type: 'ajax',
				url: '/cms/include/data_materials_linked.php',
				simpleSortMode: true,
				reader: {
					type: 'json',
					root: 'rows'
				},
				extraParams: {
					'mat_type'    : this.mat_type,
					'field_name'  : this.field_name,
					'parent_type' : this.parent_type,
					'parent_id'   : this.parent_id,
					'limit'       : 0
				}
			}		
		
		});
        
		this.reloadAction = new Ext.Action({
			tooltip: Config.Lang.edit,
			scope: this,
			handler: function () { this.store.load(); },
			iconCls:'icon-reload'
		});	        
		
		this.editAction = new Ext.Action({
			tooltip: Config.Lang.edit,
			disabled: true,
			scope: this,
			handler: function () { 
                var rec = this.grid.getSelectionModel().getSelection()[0];
                this.edit(rec.getId(),rec.get('type_id')); 
            },
			iconCls:'icon-edit'
		});	

		this.deleteAction = new Ext.Action({
			tooltip: Config.Lang.delete,
			disabled: true,
			scope: this,
			handler: function () { this.deleteMat(); },
			iconCls:'icon-delete',
		});	
		
		this.deleteLinkAction = new Ext.Action({
			tooltip: _('Удалить связь материалов'),
			disabled: true,
			scope: this,
			handler: function () { this.deleteLink(); },
			iconCls:'icon-delete2',
		});			

		var tbarConfig = [this.reloadAction,this.editAction,this.deleteLinkAction, this.deleteAction];
		
        var columnConfig = [
            {
                header: "", 
                width: 25, 
                sortable: false, 
                dataIndex: 'icon', 
                renderer: function (value) {
                    if (value == '1')
                        return '<img src="images/globe_s.gif" title="'+Config.Lang.published+'" width="14" height="14" />';
                        else return '<img src="images/globe_c_s.gif" title="'+Config.Lang.unpublished+'" width="14" height="14" />';
                }
            },
            {header: "ID", width: 50, dataIndex: 'id'},
            {
                header: Config.Lang.title, width: 75, dataIndex: 'name', flex:1,
                renderer: function (value,meta,rec) {
                    if (rec.get('locked')) {
                        value += '<br><small>' + Ext.String.format(Config.Lang.materialLocked, rec.get('locked_login')) + '</small>';
                    }
                    return value;
                }			
            },
            {
                header: Config.Lang.catalog, 
                flex:1,
                dataIndex: 'catalog',
            },
            {
                header: _('Связь'), 
                width: 100, 
                dataIndex: 'field_describ',
            }
        ];
        
		if (this.field_type == Config.fields.FIELD_MATERIAL) {
			tbarConfig = [
				{
					itemId:  'tb_mat_new',
					iconCls: 'icon-new',
					tooltip: Config.Lang.newMaterial,
					handler: function () { this.edit(0,this.mat_type); },
					scope:   this
				},
				this.editAction,
				this.deleteAction			
			];

            columnConfig = [
                {header: "ID", width: 50, dataIndex: 'id'},
                {
                    header: Config.Lang.title, width: 75, dataIndex: 'name', flex:1,
                    renderer: function (value,meta,rec) {
                        if (rec.get('locked')) {
                            value += '<br><small>' + Ext.String.format(Config.Lang.materialLocked, rec.get('locked_login')) + '</small>';
                        }
                        return value;
                    }			
                }
            ];
		}
		
			 
        this.grid = Ext.create('Ext.grid.GridPanel', {

			tbar: tbarConfig,
		
			store: this.store,
            multiSelect: false,
            hideHeaders: false, 
			loadMask: true,

			columns: columnConfig
        });
		
        this.grid.getSelectionModel().on({
            'selectionchange' : function(sm){
                var hs = sm.hasSelection();    
                var record = sm.getSelection()[0];
                this.editAction.setDisabled(!hs);
                this.deleteAction.setDisabled(!hs);	
				this.deleteLinkAction.setDisabled(!hs);	
            },
            'beforeselect' : function(t , record, index, eOpts) {
                if (record.get('disabled')) return false;
            },
            scope:this
        });		

		return this.grid;
    
    }, 
	
	edit: function(id,type) {
		
		var me = this;
		
		if (me.editWindow) me.editWindow.destroy();
        me.editWindow = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        me.store.load();
                    },
                    scope: this
                }
            }
        });	
		        
        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?type='+type+'&idcat=-1&id='+id,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+type, {win: me.editWindow});
                if (cc) cc.show();
				if (!id) {
					cc.getForm().findField( me.field_name ).setValue( me.parent_id );
				}
            }
        });	
		
	},
	
    deleteMat: function() {
        Ext.MessageBox.confirm(Config.Lang.materialDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') {
                var rec = this.grid.getSelectionModel().getSelection()[0];
                Ext.Ajax.request({
                    url: 'include/action_materials.php',
                    params: { 
                        action: 'delete', 
                        type: rec.get('type_id'), 
                        'sel[]': rec.getId()
                    },
                    scope: this,
                    success: function(resp) {
                        this.store.load();
                    }
                });                
            }
        }, this);
    },
	
    deleteLink: function() {
        Ext.MessageBox.confirm(_('Удалить связь материалов'), Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') {
                var rec = this.grid.getSelectionModel().getSelection()[0];
				Ext.Ajax.request({
					url: 'include/action_materials.php',
					params: { 
						action:   'delete_link', 
						type:     rec.get('type_id'),
						'sel[]':  rec.getId(),
						field:    rec.get('field_name'),
						src_id:   this.parent_id,
						src_type: this.parent_type
					},
					scope: this,
					success: function(resp) {
						this.store.load();
					}
				});				
			}
        }, this);
    }
    
});
