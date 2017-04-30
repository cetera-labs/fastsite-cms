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
	
	html: '<img class="edit">',
		
	listeners: {
		boxready: function() {
			var image = this.getEl().query('img.edit')[0];

			this.cropper = new Cropper(image, {
				movable: true,
				zoomable: true,
				rotatable: false,
				scalable: false,
				viewMode: 1
			});

			this.setValue( this.value );
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
				url: 'include/action_files.php?action=upload&overwrite=1&path='+path,
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