Ext.define('Cetera.fo.Section', {
    extend: 'Ext.container.ButtonGroup',
	
	border: false,
    
    title: 'Виджет List',

	items: [
		{
			iconCls: 'icon-new',
			text: 'Добавить материал в раздел',
			handler: function(btn) { 
				var me = btn.up('buttongroup');
				var w = me.widget;
				if (!w) return;
				
				me.mat_type = w.getAttribute( 'data-type' );
				
				if (me.editWindow) me.editWindow.destroy();
				me.editWindow = Ext.create('Cetera.window.MaterialEdit', {
					listeners: {
						close: {
							fn: function(win){
								
								if (win.returnValue)
								{
									window.location.reload(false);
								}

								var b = Ext.getBody();
								b.setStyle('overflow','auto');								
								
							},
							scope: me
						}
					}
				});

				var b = Ext.getBody();
				b.setStyle('overflow','hidden');
				
				var win = me.editWindow;
				win.show();

				Ext.Loader.loadScript({
					url: '/cms/include/ui_material_edit.php?modal=1&type='+w.getAttribute( 'data-type' )+'&id=0&idcat='+w.getAttribute( 'data-id' )+'&height='+me.editWindow.height,
					scope: me,
					onLoad: function() { 
						var cc = Ext.create( me.editorClass() , {win: win});
						if (cc) cc.show(); 
					}
				});				
				
			}
		}
	],
	
	editorClass: function() {
		return 'MaterialEditor' + this.mat_type.charAt(0).toUpperCase() + this.mat_type.substr(1, this.mat_type.length-1 );
	},	
}); 