Ext.define('CKFieldBase', {

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
                url: '/<?=LIBRARY_PATH?>/vendor/ckeditor/ckeditor/ckeditor.js',
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
          <?php if (\Cetera\Application::getInstance()->getVar('htmleditor.config')) : ?>
          			customConfig : '<?php echo \Cetera\Application::getInstance()->getVar('htmleditor.config'); ?>?' + ( new Date() * 1 )
          <?php else : ?>
                customConfig : '/<?=CMS_DIR?>/include/editors/ckeditor/config.php?' + ( new Date() * 1 )
          <?php endif; ?>
        };
        
        editorConfig = Ext.Object.merge(editorConfig, this.editorConfig);
        
        this.editor = CKEDITOR.replace( this.id + '-inputEl', editorConfig);
        
        var ta = Ext.get(this.id + '-inputEl');
        var me = this;
        this.editor.on('instanceReady', function(e) {  
            me.editorReady = true;        
            e.editor.resize('100%', ta.getComputedHeight());
        });
        
        this.editor.on('contentDom', function(e) {
            e.editor.document.on('keyup', function(event) {
                modules['materials_0']['object'].clearInactivityTimeout();
            });
        });

        return;
										
    }
});
