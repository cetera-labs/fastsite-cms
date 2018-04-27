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
					root: 'rows'
				},
				extraParams: {
					'id'   : 0, 
					'type' : this.mat_type,
					'filter': this.field_name+'='+this.parent_id,
					limit  : Config.defaultPageSize
				}
			}		
		
		});
		
		this.editAction = new Ext.Action({
			tooltip: Config.Lang.edit,
			disabled: true,
			scope: this,
			handler: function () { this.edit(this.grid.getSelectionModel().getSelection()[0].getId()); },
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

		var tbarConfig = [this.editAction,this.deleteLinkAction];
		
		if (this.field_type == Config.fields.FIELD_MATERIAL) {
			tbarConfig = [
				{
					itemId:  'tb_mat_new',
					iconCls: 'icon-new',
					tooltip: Config.Lang.newMaterial,
					handler: function () { this.edit(0); },
					scope:   this
				},
				this.editAction,
				this.deleteAction			
			];	
		}
		
			 
        this.grid = Ext.create('Ext.grid.GridPanel', {

			tbar: tbarConfig,
		
			bbar: Ext.create('Ext.PagingToolbar', {
				store: this.store,
				items: [Config.Lang.filter + ': ', Ext.create('Cetera.field.Search', {
					store: this.store,
					paramName: 'query',
					width:200
				})]
			}),			
		
			store: this.store,
            multiSelect: false,
            hideHeaders: false, 
			loadMask: true,

			columns: [
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
						if (rec.get('locked'))
						{
							value += '<br><small>' + Ext.String.format(Config.Lang.materialLocked, rec.get('locked_login')) + '</small>';
						}
						return value;
					}			
				},
				{header: "Alias", width: 175, dataIndex: 'alias'},
				{header: Config.Lang.date, width: 105, dataIndex: 'dat', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')},
				{
					header: Config.Lang.catalog, 
					width: 100, 
					dataIndex: 'catalog',
					getSortParam: function(){ return 'E.name'; }
				}
			]
        });
		
        this.grid.getSelectionModel().on({
            'selectionchange' : function(sm){
                var hs = sm.hasSelection();               
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
	
	edit: function(id) {
		
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
            url: 'include/ui_material_edit.php?type='+me.mat_type+'&idcat=-1&id='+id,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+me.mat_type, {win: me.editWindow});
                if (cc) cc.show();
				if (!id) {
					cc.getForm().findField( me.field_name ).setValue( me.parent_id );
				}
            }
        });	
		
	},
	
    deleteMat: function() {
        Ext.MessageBox.confirm(Config.Lang.materialDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
	
    deleteLink: function() {
        Ext.MessageBox.confirm(_('Удалить связь материалов'), Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') {
				Ext.Ajax.request({
					url: 'include/action_materials.php',
					params: { 
						action: 'delete_link', 
						type: this.mat_type, 
						'sel[]': this.getSelected(),
						field: this.field_name
					},
					scope: this,
					success: function(resp) {
						this.store.load();
					}
				});				
			}
        }, this);
    },	
	
    call: function(action, cat) {
        Ext.Ajax.request({
            url: 'include/action_materials.php',
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

    getSelected: function() {
        var a = this.grid.getSelectionModel().getSelection();
        ret = [];
        for (var i=0; i<a.length; i++) ret[i] = a[i].getId();
        return ret;
    }	
    
});
