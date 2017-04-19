Ext.define('Cetera.theme.Config', {

    extend:'Ext.form.Panel', 
    
    title: Config.Lang.themePrefs,  
    
    bodyStyle:'padding:5px 5px 0; background: none',
    defaults   : { anchor: '0' },
    defaultType: 'textfield',
    border: false,
	
	serverId: 0,
	
	setServer: function(rec)
	{
		this.serverId = rec.get('id');
		this.disable();
        this.getForm().setValues( rec.get('config') );
		var me = this;
        setTimeout(function(){
					me.enable();
		}, 500);		
	},
	
	getFileSuffix: function()
	{
		return '_server'+this.serverId;
	}
         
});