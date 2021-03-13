Ext.define('Cetera.window.FileEdit', {

    extend:'Ext.Window',

    maximizable: true, 
    closable:    true,
    resizable:   true,
    plain:       true,
    minHeight:   450,
    minWidth:    450,
	closeAction: 'hide',
	file:       '',
	isDirty: false,
	
	beautify: false,
	minify: false,
    
    initComponent : function(){
       
        this.html ='<pre id="editor-'+this.id+'"></pre>';
		
		this.saveButton = Ext.create('Ext.Button',{
					text: Config.Lang.save,
					scope: this,
					disabled: true,
					handler: function() {
						this.saveFile();
					}			
		});
		
        this.buttons = [
				this.saveButton,
				{
					text: Config.Lang.close,
					scope: this,
					handler: this.closeWindow
				}
		];
    
        this.callParent();
			

		this.on('resize', function(){
			if (this.editor) {
				this.editor.resize();
			}
		}, this);
        
    },
	
	checkDirty: function(  ) {
		var me = this;
		if (me.editor.session.getUndoManager().isClean()) {
			me.isDirty = false;
			me.saveButton.disable();
		} else {
			me.isDirty = true;
			me.saveButton.enable();
		}		
	},
	
	editFile: function( file ) {
		var me = this;
		
		this.file = file;
		this.setTitle( this.file );
		this.show();
		this.setLoading( true );
		
		if (!this.editor) {
			this.editor = ace.edit('editor-'+this.id);
			this.editor.getSession().setUseWrapMode(true);
			this.editor.on('input', function() {
				me.checkDirty();
			});		
		}
		this.editor.setValue( '' );
		this.editor.setReadOnly( false );
		
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'get_file',
				file: this.file
			},
            scope: this,
            success: function(resp) {
				this.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                if (obj.success) {
					
					me.setMode(obj.extension);
					
					if (me.beautify)
					{
						switch (obj.extension) {
							case 'css':
								me.editor.setValue(css_beautify(obj.data));
								break;
							case 'js':
								me.editor.setValue(js_beautify(obj.data));
								break;
							case 'twig':
							case 'html':
								me.editor.setValue(html_beautify(obj.data));
								break;
							default: 
								me.editor.setValue( obj.data );	
						}						
					}
					else
					{
						me.editor.setValue( obj.data );
					}
					
					me.editor.setReadOnly( obj.readonly );	
					me.editor.scrollToLine(0);
					me.editor.gotoLine(1);	
					me.editor.focus();	
					setTimeout(function(){
						me.editor.session.getUndoManager().reset();
						me.checkDirty();	
					}, 100);
					
				} else {
					
					if (obj.deny) {
						
						Ext.MessageBox.alert(Config.Lang.error, Config.Lang.accessDenied);
						this.close();
						
					} else {
					
						Ext.MessageBox.confirm(Config.Lang.fileNotFound, Config.Lang.fileCreate, function(btn) {
							if (btn == 'yes') {
								this.saveFile();
							} else {
								this.close();
							}
						}, this);
					
					}
					
				}
            }
        });
		
	},
	    
	saveFile: function( closeAfterSave ) {
		
		this.editor.session.getUndoManager().markClean();
		this.checkDirty();
		
		var res = this.fireEvent('beforeSave', this, this.editor);
		if (res == false) return false;
	
		this.setLoading( true );
		
		if (this.minify)
		{
			var minify = require('html-minifier').minify;
			var data = minify( this.editor.getValue(), {
				collapseWhitespace: true
			} );
		}
		else 
		{
			var data = this.editor.getValue();
		}
		
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'save_file',
				file: this.file,
				data: data
			},
            scope: this,
            success: function(resp) {
				this.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                if (!obj.success) {					
					if (obj.message) Ext.MessageBox.alert(Config.Lang.error, obj.message);					
				} else {
					this.setMode(obj.extension);
				}
				this.editor.focus();
				if ( closeAfterSave ) this.close();
            }
			
        });	
		
	},
	
	setMode: function( extension ) {
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
	},	
	
	closeWindow: function() {
		if (!this.isDirty) {
			this.close();
			return;
		}
		
		Ext.MessageBox.confirm(Config.Lang.fileChanged, Config.Lang.saveChanges, function(btn) {
			if (btn == 'yes') {
				this.saveFile( true );
			} else {
				this.close();
			}
		}, this);		
		
	}
	
});