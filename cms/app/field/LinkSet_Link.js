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
					'filter': this.mat_filter,
					limit  : Config.defaultPageSize
				}
			}		
		
		});
			 
        return Ext.create('Ext.grid.GridPanel', {

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
    
    }, 
    
});
