Ext.define('Cetera.window.MailTemplate', {
    extend:'Ext.Window',

    modal: true,
    autoShow: true,
    width: '80%',
    minWidth: 400,
    minHeight: 400,
	layout: {
		type: 'hbox',
		pack: 'start',
		align: 'stretch'
	},
	title: 'Почтовый шаблон',
	lastFocus: null,
	
    items: [
		{
			flex: 1,
			xtype: 'form',		
			itemId: 'form',
			layout: 'anchor',
			defaults: {
				anchor: '100%',
				labelWidth: 200,
				hideEmptyLabel: false
			},
			border: false,
			defaultType: 'textfield',
			bodyPadding: 10,		
			bodyCls: 'x-window-body-default', 

			items: [
				{
					itemId: 'event',
					fieldLabel: 'Событие',
					name: 'event',
					xtype: 'combobox',
					allowBlank: false,
					displayField: 'name',
					valueField: 'id',
					editable: false,				
					store: 'mail_events',
					listeners: {
						change: {
							fn: function(elm, value) {
								var p = elm.up('window').getComponent('parameters');
								var rec = this.getStore().getById(value);
								if (rec) p.update( rec.get('parameters') );
									else p.update('');
							}
						}						
					}
				},			
				{
					fieldLabel: 'Активность',
					name: 'active',
					xtype: 'checkboxfield'
				},	
				{
					fieldLabel: 'Тип письма',
					name: 'content_type',
					xtype: 'combobox',
					allowBlank: false,
					displayField: 'name',
					valueField: 'id',
					editable: false,				
					store: {
						fields: ['id', 'name'],
						data: [
							{id: 'text/plain', name:"Текст"},
							{id: 'text/html', name:"HTML"}
						],
						proxy: {
							type: 'memory'
						}	
					}
				},			
				{
					fieldLabel: 'От кого',
					name: 'mail_from_email',
					listeners: {
						focus: function(elm) {
							elm.up('window').lastFocus = elm;
						}
					}
				},			
				{
					fieldLabel: 'Кому',
					name: 'mail_to',
					allowBlank: false,
					listeners: {
						focus: function(elm) {
							elm.up('window').lastFocus = elm;
						}
					}
				},			
				{
					fieldLabel: 'Тема',
					name: 'mail_subject',
					allowBlank: false,
					listeners: {
						focus: function(elm) {
							elm.up('window').lastFocus = elm;
						}
					}
				},			
				{
					fieldLabel: 'Сообщение',
					name: 'mail_body',
					allowBlank: false,
					xtype: 'textarea',
					height: 400,
					listeners: {
						focus: function(elm) {
							elm.up('window').lastFocus = elm;
						}
					}
				}
			],
			
			buttons: [
				{
					text    : 'OK',
					handler : function() {
						var f = this.up('form').getForm();
						if (!f.isValid()) return;
						f.updateRecord();
						if (!f.getRecord().getId()) this.up('window').fireEvent('recordcreated', f.getRecord());
						this.up('window').destroy();
					}
				},{
					text    : 'Отмена',
					handler : function() {
						this.up('window').destroy();
					}
				}
			]
		},
		{
			title: 'Параметры',
			itemId: 'parameters',
			width: 300,
			data: null,
			padding: 5,
			bodyPadding: 5,	
			overflowY: 'auto',						
			tpl: [
				'<tpl foreach=".">', 
					'<p><a href="javascript:Ext.WindowManager.getActive().insertParameter(\'{{{$}}}\');"><b>{{{$}}}</b></a> {.}</p>', 
				'</tpl>'
			]
		}		
	],
	
    initComponent: function(){
		this.callParent();
		var evt = this.getComponent('form').getComponent('event');
		if (!this.record)
		{
			this.record = Ext.create('Cetera.model.MailTemplate');
			evt.enable();
		}
		else
		{
			evt.disable();
		}
		this.getComponent('form').getForm().loadRecord( this.record );
	},
	
	insertParameter: function(value) {
		if (this.lastFocus)
		{
			this.insertAtCursor(this.lastFocus.inputEl.dom, value);
			this.lastFocus.focus();
		}
	},
	
	insertAtCursor: function(myField, myValue) { 

    	//IE support 
    	if (document.selection) { 
    		myField.focus(); 
    		sel = document.selection.createRange(); 
    		sel.text = myValue; 
    	}
    		//Mozilla/Firefox/Netscape 7+ support 
    	else if (myField.selectionStart || myField.selectionStart == '0'){  
    		var startPos = myField.selectionStart; 
    		var endPos = myField.selectionEnd; 
    		myField.value = myField.value.substring(0, startPos)+ myValue 
                 + myField.value.substring(endPos, myField.value.length); 
    		} else { 
    			myField.value += myValue; 
    		} 
	}	
	  
});     