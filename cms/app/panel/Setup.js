Ext.define('Cetera.panel.Setup', {
	
	extend: 'Ext.tab.Panel',
	requires: [
		'Cetera.panel.Cache'
	],

	border: false,	
	layout: 'border',
	padding: '5 0',
	bodyCls: 'x-window-body-default',        
    cls: 'x-window-body-default',
	style: 'border: none',
	
	items: [
		{
			xtype: 'cache'
		}	
	]
	
});