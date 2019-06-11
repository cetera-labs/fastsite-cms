Ext.Loader.syncRequire('Cetera.widget.Panel'); 

function showWidgetWindow(editor, widgetName, sel) {
	
	if ( sel )  {

        var params = Ext.Object.fromQueryString(sel.getAttribute('widgetparams'));
		var widgetPanel = Cetera.widget.Panel.getWidgetPanel({
			widgetName  : sel.getAttribute('widgetname'),
            widgetTitle : params.widgetTitle,
			params      : params
		});

	} else {
		var widgetPanel = Cetera.widget.Panel.getWidgetPanel({
			widgetName : widgetName
		});
		//console.log(widgetName);
		widgetPanel.load( false );
	}

    var win = Ext.create('Ext.Window', {
        plain: true,
        width: 700,
        modal: true,
        layout: 'fit',
        border: false,
        items: widgetPanel,
        title: widgetPanel.widgetName ,
        icon : widgetPanel.icon,
        
        buttons : [
            {
                text: _('ОК'),
                handler: function() {
                
                    var window = this.up('window');
                    var widgetPanel = window.items.getAt(0);
                    
                    if (!widgetPanel.isValid()) return;
                    
            		if (sel) {
                        sel.setAttributes({
                            widgetparams: Ext.Object.toQueryString(widgetPanel.getParams())
                        });                       
                    } 
					else {
                		sel = new CKEDITOR.dom.element( 'cms' );
                        sel.setAttributes({
							action: 'widget',
                            class: 'widget-'+widgetPanel.widgetName,
                            widgetname: widgetPanel.widgetName,
                            widgetparams: Ext.Object.toQueryString(widgetPanel.getParams())
                        });
                        editor.insertElement( sel );
                    }
                    
                    window.close();

                }
            },{
                text: _('Отмена'),
                handler: function() {
                    this.up('window').close();
                }
            }                    
        ]
        
    }).show();
                
}

CKEDITOR.plugins.add( 'widgets',
{
  	init : function( editor )
  	{
    
      	CKEDITOR.addCss(
      				'cms' +
      				'{' +
                        'display: inline-block;' +
      					'background-image: url(' + CKEDITOR.getUrl( this.path + 'widget_placeholder.png' ) + ');' +
      					'background-position: center center;' +
      					'background-repeat: no-repeat;' +
      					'width: 130px;' +
      					'height: 130px;' +
      				'}'
      	);
      	CKEDITOR.addCss(
      				'cms:focus' +
      				'{' +
                        'border: 1px solid red;' +
      				'}'
      	);        
	    CKEDITOR.dtd['cms']={};
        CKEDITOR.dtd['p']['cms']=1;  
        CKEDITOR.dtd['div']['cms']=1;
        CKEDITOR.dtd['td']['cms']=1;
        CKEDITOR.dtd.$block['cms']=1;
        CKEDITOR.dtd.$object['cms']=1;
        
        Ext.Array.each(Config.widgets, function(item) {

            if (item.icon)
                  	CKEDITOR.addCss(
                  				'cms.widget-' + item.name +
                  				'{' +
                  					'background: url(' + item.icon + ') 10px 10px no-repeat, url(' + CKEDITOR.getUrl( this.path + 'widget_placeholder.png?1=1' ) + ') center center no-repeat;' +
                  				'}'
                  	);

            }, this);
    
			  editor.on( 'doubleclick', function( evt ) {
  					var element = evt.data.element;
  					if ( element.is( 'cms' ) ) {     
                        showWidgetWindow(editor, null, element);           
                    }
		});
  
    		editor.ui.addRichCombo( 'Widget', {
    			label : 'Виджет',
                toolbar: 'cetera',
      			panel: {
      				css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( editor.config.contentsCss ),
      				multiSelect: false
      			},
				init: function () {
                    
                    var me = this;
                    Config.widgets.forEach(function(item, i, arr) {
                        me.add(item.name,'<img align="absmiddle" src="'+item.icon+'" /> '+item.describ);
                    });                    
					
				},
				onClick: function (value) {
				
					showWidgetWindow(editor, value);

				}
    		});
      
  	}
    
});