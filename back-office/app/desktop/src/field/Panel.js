Ext.define('Cetera.field.Panel', {   
    extend: 'Ext.form.field.Base',
    
    liquidLayout: false,
      
    onResize : function(w, h){
        this.callParent(arguments);
        this.panel.setSize(w - this.labelWidth, h);
    },
    
    getPanel : function() {
    
        return Ext.create('Ext.Panel',{
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
        
    fieldSubTpl: [
        '<div id="{id}" class="{fieldCls}"></div>',
        {
            compiled: true,          
            disableFormats: true     
        } 
    ],
    
    privates: {
        finishRenderChildren: function() {
            this.callParent(arguments);
            this.panel.finishRender();
        }            
    }
});
