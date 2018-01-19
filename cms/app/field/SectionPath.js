Ext.define('Cetera.field.SectionPath', {

    extend:'Ext.form.field.Trigger',
    
    alias: 'widget.sectionpath',
	
	triggerCls: 'icon-folder',
	
	regex: /^[\/\.\-\_A-Z0-9А-Я]+$/i,
    
    initComponent : function(){    
    
        this.window = Ext.create('Cetera.window.SiteTree', {
            exclude: this.exclude,
            nolink : this.nolink,
            from   : this.from,
            rule   : this.rule,
            only   : this.only,
            path   : this.path,
			url    : this.url,
            materials   : 0,
            nocatselect : 0,
            norootselect: 1

        });
        
        this.window.on('select', function(res) {
            this.setValue(res.url);  
            this.fireEvent('select', res.url);
        }, this); 
    
        this.callParent();
    },
	        
    onTriggerClick: function() {
        this.window.show();
    },
         
	onDestroy: function(){
		this.window.close();
        this.callParent();
	}
});