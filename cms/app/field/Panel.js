Ext.define('Cetera.field.Panel', {   
    extend: 'Ext.form.Field',
      
    onResize : function(w, h){
        this.callParent(arguments);
        this.panel.setSize(w - this.getLabelWidth(), h);
    },
    
    getPanel : function() {
    
        return new Ext.Panel({
            layout: 'border',
            border: false,
            bodyStyle:'background: none',
            html: 'Abstract'
        });    
    
    },
        
    initComponent : function(){
        this.panel = this.getPanel();
		this.initField();
        this.callParent(arguments);			
    },
    
    
    getSubTplMarkup: function() {
        // generateMarkup will append to the passed empty array and return it
        var buffer = Ext.DomHelper.generateMarkup(this.panel.getRenderTree(), []);
        // but we want to return a single string
        return buffer.join('');
    },
    
    finishRenderChildren: function() {
        this.callParent(arguments);
        this.panel.finishRender();
    },
    
    fieldSubTpl: [
        '<div id="{id}" class="{fieldCls}"></div>',
        {
            compiled: true,          
            disableFormats: true     
        } 
    ]
});
