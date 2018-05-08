Ext.define('Cetera.field.ck.Base', {

    extend:'Ext.form.TextArea',
    editor: null,
    editorReady: false,
       
    onDestroy : function() {
        if (this.editor) this.editor.destroy();
        this.callParent();
    },
    
    afterRender: function() {
        this.callParent();
        if (typeof CKEDITOR === "undefined") {

            
            Ext.Loader.loadScript({
                url: '/library/vendor/ckeditor/ckeditor/ckeditor.js',
                onLoad: this.initEditor,
                scope: this
            });
            

        } else {
            this.initEditor();
        }    
    },
    
    onResize : function(w, h){
        this.callParent(arguments);
        if (!this.editorReady) return;
        if (!this.hideLabel) w = w - this.labelWidth;
        this.editor.resize(w, h);
    },

    initEditor: function() { 

        var editorConfig = {
              customConfig : '/cms/include/editors/ckeditor/config.php?' + ( new Date() * 1 )
        };
        
        editorConfig = Ext.Object.merge(editorConfig, this.editorConfig);
        
        this.editor = CKEDITOR.replace( this.id + '-inputEl', editorConfig);
        
        var ta = Ext.get(this.id + '-inputEl');
        var me = this;
        this.editor.on('instanceReady', function(ev) {  
            me.editorReady = true;        
            ev.editor.resize('100%', ta.getComputedHeight());
			
			ev.editor.dataProcessor.htmlFilter.addRules( {
				elements : {
					img: function( el ) {
				
						// Remove inline "height" and "width" styles and
						// replace them with their attribute counterparts.
						// This ensures that the 'img-responsive' class works
						var style = el.attributes.style;
				
						if (style) {
							// Get the width from the style.
							var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec(style),
								width = match && match[1];
				
							// Get the height from the style.
							match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec(style);
							var height = match && match[1];
				
							// Replace the width
							if (width) {
								el.attributes.style = el.attributes.style.replace(/(?:^|\s)width\s*:\s*(\d+)px;?/i, '');
								el.attributes.width = width;
							}
				
							// Replace the height
							if (height) {
								el.attributes.style = el.attributes.style.replace(/(?:^|\s)height\s*:\s*(\d+)px;?/i, '');
								el.attributes.height = height;
							}
						}
				
						// Remove the style tag if it is empty
						if (!el.attributes.style)
							delete el.attributes.style;
					}
				}
			});
			
        });
        
        this.editor.on('contentDom', function(e) {
            e.editor.document.on('keyup', function(event) {
                modules['materials_0']['object'].clearInactivityTimeout();
            });
        });

        return;
										
    },
	
	getValue: function() {
		
		if (!this.editor) return '';
		return this.editor.getData();
		
	}
});
