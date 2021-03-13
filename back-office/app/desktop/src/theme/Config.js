Ext.define('Cetera.theme.Config', {

    extend:'Ext.form.Panel', 
    
    bodyStyle:'background: none',
    defaults   : { anchor: '0' },
    defaultType: 'textfield',
    border: false,
	
	serverId: 0,
		
	getFileSuffix: function()
	{
		return '_server'+this.serverId;
	}
         
});