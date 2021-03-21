Ext.define('Cetera.window.ImageCrop', {

    extend:'Ext.Window',  

	requires: 'Cetera.Ajax',
    resizable: false,  
    bodyBorder: false,
    bodyStyle:'background: none',
    border: false,
    modal: true,
    width: '70%',
	height: '80%',
    minHeight: 300,
	minwidth: 300,
    plain: true,
    padding: 10,
	closeAction: 'hide',
	layout: 'fit',
	
	buttons: [
		{
			text: 'OK',
			handler: function() {				
				this.up('window').doCrop();				
			}
		},{
			text: _('Отмена'),
			handler: function() {
				this.up('window').close();
			}
		}
	],
	
	tbar: [
		{
			xtype: 'checkbox',
			boxLabel: _('пропорции'),
			padding: '0 0 0 5',
			itemId: 'aspect_check',
			stateId: 'stateCropAspect',
			stateful: true,		
			listeners: {
				change: {
					fn: function() { if (this.up('window')) this.up('window').aspectChange(); }
				}
			}
		},
		{
			xtype: 'numberfield',
			fieldLabel: _('Шир.'),
			value: 16,
			minValue: 1,
			padding: '0 0 0 5',
			labelWidth: 30,
			width: 150,
			itemId: 'aspect_width',
			stateId: 'stateCropWidth',
			stateful: true,				
			listeners: {
				change: {
					fn: function() { if (this.up('window')) this.up('window').aspectChange(); }
				}
			}
		},	
		{
			xtype: 'numberfield',
			fieldLabel: _('Выс.'),
			value: 9,
			minValue: 1,
			padding: '0 0 0 5',
			labelWidth: 30,
			width: 150,
			itemId: 'aspect_height',
			stateId: 'stateCropHeight',
			stateful: true,					
			listeners: {
				change: {
					fn: function() { if (this.up('window')) this.up('window').aspectChange(); }
				}
			}
		}
	],
	
	html: '<img class="edit">',
		
	listeners: {
		boxready: function() {
			var image = this.getEl().query('img.edit')[0];

			this.cropper = new Ext.xCropper(image, {
				movable: true,
				zoomable: true,
				rotatable: false,
				scalable: false,
				viewMode: 1
			});
			
			this.aspectChange();
			
			this.setValue( this.value );
		}
	},
	
	aspectChange: function() {
		var tb = this.getDockedItems('toolbar[dock="top"]')[0];
		if (tb.getComponent('aspect_check').getValue()) {
			this.cropper.setAspectRatio( tb.getComponent('aspect_width').getValue() / tb.getComponent('aspect_height').getValue() );
		}
		else {
			this.cropper.setAspectRatio( 0 );
		}
	},
	
	setValue: function(value) {
		if (this.value == value) return;
		this.value = value;
		this.cropper.replace(this.value);
	},
	
	doCrop: function() {
		var me = this;
		var ext = me.value.split('.').pop().toLowerCase();
		var contentType = false;
		if (ext == 'jpg' || ext == 'jpeg') {
			contentType = 'image/jpeg';
		}	
		var p = me.value.split('/');
		var baseName = p.pop().replace('.'+ext,'_crop.'+ext);
		var path = p.join('/');
		
		this.cropper.getCroppedCanvas().toBlob(function(blob){
			var formData = new FormData();			
			formData.append('file', blob, baseName);
			me.setLoading(true);
			Cetera.Ajax.request({
				url: '/cms/include/action_files.php?action=upload&overwrite=1&path='+path,
				timeout: 1000000,
				method: 'POST',
				rawData: formData,
				ignoreHeaders: true,
				success: function(resp) {
					var obj = Ext.decode(resp.responseText);
					if (obj.success) {
						me.fireEvent('crop', obj.path + obj.file);
						me.close();
					}
				},
				callback: function() {
					me.setLoading( false );
				},
				scope: me
			}); 			
		}, contentType, 0.8);
	}
   
});