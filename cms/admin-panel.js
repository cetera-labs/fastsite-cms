var widgetEdit = [];
var tmo = null;
Ext.onReady(function(){
	
	Ext.Loader.setPath('Cetera', '/cms/app');
	Ext.Loader.setPath('Ext.ux', '/cms/app/ux');
	
	Config.setLocale(Config.locale);

    var tb = Ext.create('Ext.toolbar.Toolbar',{
        style: {
            position: 'fixed',
			right: '0',
			top: '0',
			left: '0',
			'z-index': '19000'
        },
		items: [
			{
				iconCls: 'icon-setup',
				text: 'Администрирование',
				handler: function() {
					document.location = '/cms/';
				}
			},
			'-',
			{
				iconCls: 'icon-edit',
				text: 'Режим правки',
				enableToggle: true,
				pressed: Config.foEditMode,
				handler: function(btn) {

					Config.foEditMode = btn.pressed;
					Ext.Ajax.request({
					  url: '/cms/include/ajax.php',
					  params: {
						  action: 'fo_edit_mode',
						  mode: btn.pressed?1:0
					  }
					});  					
				
				}
			},			
			'->',
			{
				xtype: 'tbtext',
				text: Config.user.name
			},'-',
			{
				text: 'Выход',
				handler: function() {
					document.location = '/cms/logout.php';
				}
			}
		]
	});
	var b = Ext.getBody();
    tb.render(b,0);
	b.setStyle('margin-top',tb.getHeight()+'px');
	b.removeCls('x-body');
	
	var widgets = Ext.select(".x-cetera-widget");
	
	widgets.on('mouseleave', function(e,t) {
				
		var widget = e.getTarget('.x-cetera-widget', 10, true);
		hideWidget(widget);		

	});
	
	widgets.on('mouseenter', function(e,t) {
		
		if (!Config.foEditMode) return;
		
		if (tmo) clearTimeout(tmo);
		var widget = e.getTarget('.x-cetera-widget', 10, true);
		if (!widget.getAttribute( 'data-class' )) return;
		
		widget.addCls('x-cetera-widget-active');
		
		var c = widget.getAttribute( 'data-class' );
		if (!widgetEdit[c])
		{
			widgetEdit[c] = Ext.create(c);
			widgetEdit[c].render( Ext.getBody() );
			widgetEdit[c].getEl().on('mouseenter', function(e,t) {
				if (tmo) clearTimeout(tmo);
			});
			widgetEdit[c].getEl().on('mouseleave', function(e,t) {
				hideWidget( Ext.select(".x-cetera-widget-active").first() );
			});
		}
		widgetEdit[c].widget = widget;		
		
		widgetEdit[c].setXY( [widget.getX(),widget.getY() - widgetEdit[c].getHeight()] );	
		widgetEdit[c].setWidth( widget.getWidth() );
				
	});
	
});

function hideWidget(widget)
{
	if (!widget) return;
	if (!widget.getAttribute( 'data-class' )) return;
	var c = widget.getAttribute( 'data-class' );
	
	if (widgetEdit[c]) tmo = setTimeout(function() { 
		widget.removeCls('x-cetera-widget-active');
		widgetEdit[c].setY(-100);
		widgetEdit[c].widget = null;
		tmo = null; 
	}, 200);	
}