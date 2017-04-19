<?php
header('Content-Type: application/javascript; charset=UTF-8');
chdir('../../../../'); 
include('common_bo.php'); 
?>

Ext.Loader.syncRequire('Cetera.widget.Panel'); 

function showWidgetWindow(editor, widgetName, sel) {

    var widgetNode = fakeImage  = false;    
	
	if ( sel && sel.data( 'cke-real-element-type' ) && ( sel.data( 'cke-real-element-type' ) == 'cms_widget' || sel.data( 'cke-real-element-type' ) == 'cms' ))  {

		fakeImage = sel;
		widgetNode = editor.restoreRealElement( fakeImage );
		var widgetPanel = Cetera.widget.Panel.getWidgetPanel({
			widgetName  : widgetNode.getAttribute('widgetname'),
			params      : Ext.Object.fromQueryString(widgetNode.getAttribute('widgetparams'))
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
                text: '<?=$translator->_('ОК')?>',
                handler: function() {
                
                    var window = this.up('window');
                    var widgetPanel = window.items.getAt(0);
                    
                    if (!widgetPanel.isValid()) return;
                    
            		if (widgetNode)
					{
                        widgetNode.setAttributes({
                            widgetparams: Ext.Object.toQueryString(widgetPanel.getParams())
                        });
                    } 
					else
					{
                		widgetNode = new CKEDITOR.dom.element( 'cms' );
                        widgetNode.setAttributes({
							action: 'widget',
                            widgetname: widgetPanel.widgetName,
                            widgetparams: Ext.Object.toQueryString(widgetPanel.getParams())
                        });
                    }
                
            		var newFakeImage = editor.createFakeElement( widgetNode, 'cke_widget cke_widget-' + widgetNode.getAttribute('widgetname'),  'cms', false );

            		if ( fakeImage )
					{
            			newFakeImage.replace( fakeImage );
            			editor.getSelection().selectElement( newFakeImage );
            		}
            		else 
					{
            			editor.insertElement( newFakeImage );
                    }
                    
                    window.close();

                }
            },{
                text: '<?=$translator->_('Отмена')?>',
                handler: function() {
                    this.up('window').close();
                }
            }                    
        ]
        
    }).show();
                
}

CKEDITOR.plugins.add( 'widgets',
{
  	requires : [ 'fakeobjects' ],
  	init : function( editor )
  	{
    
      	CKEDITOR.addCss(
      				'img.cke_widget' +
      				'{' +
      					'background-image: url(' + CKEDITOR.getUrl( this.path + 'widget_placeholder.png' ) + ');' +
      					'background-position: center center;' +
      					'background-repeat: no-repeat;' +
      					'width: 130px;' +
      					'height: 130px;' +
      				'}'
      	);
        
        Ext.Array.each(Config.widgets, function(item) {

            if (item.icon)
                  	CKEDITOR.addCss(
                  				'img.cke_widget-' + item.name +
                  				'{' +
                  					'background: url(' + item.icon + ') 10px 10px no-repeat, url(' + CKEDITOR.getUrl( this.path + 'widget_placeholder.png?1=1' ) + ') center center no-repeat;' +
                  				'}'
                  	);

            }, this);
    
			  editor.on( 'doubleclick', function( evt ) {
  					var element = evt.data.element;
  					if ( element.is( 'img' ) && element.data( 'cke-real-element-type' ) == 'cms' ) {     
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

					<?php
					foreach ($application->getRegisteredWidgets() as $item)
					{
						if ($item['not_placeable']) continue;
						print "this.add('".$item['name']."','".($item['icon']?'<img align="absmiddle" src="'.$item['icon'].'" /> ':'').$item['describ']."');\n";
					}
					?>
					
				},
				onClick: function (value) {
				
					showWidgetWindow(editor, value);

				}
    		});
        
        editor.lang.fakeobjects['cms'] = 'Виджет';
      
  	},

 		afterInit : function( editor )
		{
			  var dataProcessor = editor.dataProcessor,
				dataFilter = dataProcessor && dataProcessor.dataFilter;

  			if ( dataFilter )	{
    				dataFilter.addRules(
    				{
      					elements :
      					{ 
      						'cms_widget' : function( element ) {
      							  return editor.createFakeParserElement( element, 'cke_widget cke_widget-' + element.attributes.widgetname, 'cms', false );
      						},
							'cms' : function( element ) {
      							  return editor.createFakeParserElement( element, 'cke_widget cke_widget-' + element.attributes.widgetname, 'cms', false );
      						}							
      					}
    				});
  			} 
		}
    
});