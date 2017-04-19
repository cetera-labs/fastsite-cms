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
        this.editor.on('instanceReady', function(e) {  
            me.editorReady = true;        
            e.editor.resize('100%', ta.getHeight());
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
