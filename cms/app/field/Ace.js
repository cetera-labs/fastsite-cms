Ext.define('Cetera.field.Ace', {
	
	alias : 'widget.acefield',

    extend:'Ext.form.field.TextArea',
	
	initComponent: function() {
				
		this.callParent();
		this.on('boxready', function(){
			
			this.initEditor();		
		
		}, this);
	},

    initEditor : function(){
		var me = this;
		
		me.editor = ace.edit(me.id + '-inputEl');
		me.editor.getSession().setMode("ace/mode/html");	
		me.editor.getSession().setUseWrapMode(true);
       
		me.editor.on('change', function(){			
			me.setValue( me.editor.getValue(), true );
		});
    },
	
	setValue : function(value, internal){
		var me = this;
		this.callParent([value]);
		if (internal) return;
		if (me.editor)
		{
			me.editor.setValue(value);
			me.editor.scrollToLine(0);
			me.editor.gotoLine(1);				
		}
	},
	
	setMode: function( extension ) {
		if (!this.editor) return;
		switch (extension) {
			case 'css':
				this.editor.session.setMode("ace/mode/css");
				break;
			case 'php':
				this.editor.session.setMode("ace/mode/php");
				break;
			case 'js':
				this.editor.session.setMode("ace/mode/javascript");
				break;
			case 'twig':
				this.editor.session.setMode("ace/mode/twig");
				break;
			case 'html':
			default: 
				this.editor.session.setMode("ace/mode/html");	
		}
	}
	
});