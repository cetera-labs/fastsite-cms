Ext.define('Cetera.grid.Fields', {

	extend: 'Ext.grid.Panel',

	viewConfig: {
		getRowClass: function(record, rowIndex, rowParams, store){
			return record.get("fixed") ? "fixed" : "";
		}
	},			
	loadMask: true,
	title: Config.Lang.fields,
	border: false,

    initComponent: function() {
        this.cellEditing = new Ext.grid.plugin.CellEditing({
            clicksToEdit: 2
        });
		
		Ext.apply(this, {
			
			plugins: [this.cellEditing],
			
			columns: [
				{header: 'ID', width: 40, dataIndex: 'id'},
				{
					header: Config.Lang.srt, 
					width: 50, 
					dataIndex: 'tag',
					editor: {
						xtype: 'numberfield',
						allowBlank: false
					}					
				},
				{
					xtype: 'checkcolumn', 
					align: 'center', 
					header: Config.Lang.showField, 
					width: 75, 
					dataIndex: 'shw'
				},
				{
					xtype: 'checkcolumn', 
					align: 'center', 
					header: Config.Lang.requiredField, 
					width: 85, 
					dataIndex: 'required'
				},
				{
					header: Config.Lang.name, 
					width: 150, 
					dataIndex: 'name',
					editor: {
						allowBlank: false,
						regex: /[a-z0-9\_]+$/i
					}					
				},
				{
					header: Config.Lang.description, 
					dataIndex: 'describ', 
					flex: 1,
					renderer: function(value,m,rec) {
						return rec.get('describDisplay');
					},					
					editor: {
						allowBlank: false
					}					
				},
				{
					header: Config.Lang.dateType, 
					width: 180, 
					dataIndex: 'type_name'
				},
				{
					header: Config.Lang.page, 
					width: 150, 
					dataIndex: 'page',
					editor: new Ext.form.field.ComboBox({
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						store: new Ext.data.JsonStore({
							autoDestroy: true,										
							fields: ['page'],							
							proxy: {
								type: 'ajax',
								reader: {
									type: 'json',
									successProperty: 'success',
									root: 'rows',
									messageProperty: 'message'
								},
								api: {
									read: 'include/data_types.php?mode=pages',
								}
							}
						}),
						displayField: 'page',
						valueField: 'page',
						lazyRender: true
					})				
				}
			]			
			
		});
		
		this.callParent();
	}
	
});