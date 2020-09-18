var widgetEdit = [];
var tmo = [];
Ext.onReady(function(){
    
    Ext.themeName = 'neptune';
	
	Ext.Loader.setPath('Cetera', '/cms/app');
	Ext.Loader.setPath('Ext.ux', '/cms/app/ux');
	
	Config.setLocale(Config.locale);

    var tb = Ext.create('Ext.toolbar.Toolbar',{
        id: 'admin-toolbar',
        cls: 'front-office',
        style: {
            position: 'fixed',
			right: '0',
			top: '0',
			left: '0',
			'z-index': '19000'
        },
		items: [
            {
                xtype: 'buttongroup',
                title: 'Контент',
                items: [
                    {
                        iconCls: 'icon-edit',
                        text: 'Режим правки',
                        scale: 'medium',
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
                    {
                        iconCls: 'icon-setup',
                        scale: 'medium',
                        text: 'Администрирование',
                        handler: function() {
                            document.location = '/cms/';
                        }
                    }                    
                ]
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
	
	Ext.EventManager.onWindowResize(function(w, h){
		tb.updateLayout();
	});	
		
	var widgets = Ext.select(".x-cetera-widget");

    // пропускаем вложеные виджеты
    widgets.each(function(el, widgets){
        
        if (el.parent(".x-cetera-widget")) {
            widgets.removeElement(el);
        }
        
    });
	
	widgets.on('mouseleave', function(e,t) {
				
		var widget = e.getTarget('.x-cetera-widget', 10, true);
		hideWidget(widget);		

	});
	
	widgets.on('mouseenter', widgetEnter);
	
});

function widgetEnter(e,t) {    
    if (!Config.foEditMode) return;
    
    var widget = e.getTarget('.x-cetera-widget', 10, true);
   
    if (!widget.getAttribute( 'data-class' )) return;
    
    widget.addCls('x-cetera-widget-active');
    if (tmo[widget.id]) clearTimeout(tmo[widget.id]);
    
    var c = widget.getAttribute( 'data-class' );
    if (!widgetEdit[widget.id])
    {            
        var group = Ext.create(c, {
            widget: widget
        });
        
        var items = [
            group
        ];
        
        // ищем вложеные виджеты, и добавляем их на тулбар
        var subwidgets = widget.select(".x-cetera-widget");
        subwidgets.each(function(el){
            
            if (!el.getAttribute( 'data-class' )) return;
            
            var group = Ext.create( el.getAttribute( 'data-class' ), {
                widget: el
            } );
            items.push(group);
            
        });            
        
        widgetEdit[widget.id] = Ext.create('Ext.Toolbar', {
            border: false,
            style: {
                position: 'absolute'
            },
            items: items
        });
        
        widgetEdit[widget.id].render( Ext.getBody() );
        widgetEdit[widget.id].getEl().set( {'data-parent': widget.id} );
        widgetEdit[widget.id].getEl().on('mouseenter', function(e,el) {
            var wid = el.getAttribute( 'data-parent' );
            if (tmo[wid]) clearTimeout(tmo[wid]);
        });
        widgetEdit[widget.id].getEl().on('mouseleave', function(e,el) {
            var widget = Ext.get( el.getAttribute( 'data-parent' ) );
            hideWidget( widget );
        });
    }        
    
    widgetEdit[widget.id].setXY( [widget.getX(),widget.getY() - widgetEdit[widget.id].getHeight()] );	
    widgetEdit[widget.id].setWidth( widget.getWidth() );
            
}

function hideWidget(widget)
{
	if (!widget) return;
    if (!widget.id) return;
	if (!widget.getAttribute( 'data-class' )) return;
	var c = widget.getAttribute( 'data-class' );
	
	if (widgetEdit[widget.id]) tmo[widget.id] = setTimeout(function() { 
		widget.removeCls('x-cetera-widget-active');
		widgetEdit[widget.id].setY(-100);
		widgetEdit[widget.id].widget = null;
		tmo[widget.id] = null; 
	}, 200);	
}