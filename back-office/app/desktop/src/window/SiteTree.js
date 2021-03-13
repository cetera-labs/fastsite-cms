Ext.define('Cetera.window.SiteTree', {

    extend:'Ext.Window',

    initComponent : function(){
       
        this.tree = Ext.create('Cetera.catalog.SiteTree', {
            border:false,
            
            url:this.url,
            from:this.from,
            exclude:this.exclude,
            rule:this.rule,
            nolink:this.nolink,
            only:this.only,
            materials:this.materials,
            exclude_mat:this.exclude_mat,
            matsort:this.matsort,
            nocatselect:this.nocatselect,
            norootselect: this.norootselect
        });
        
        this.tree.on('itemdblclick', function() {
            this.processSelect(); 
        },this);

        if (!this.width) this.width = 500;
        if (!this.height) this.height = 500;
        this.closeAction = 'hide';
        this.layout = 'fit';
        this.modal = true;
        this.items = [this.tree];
        
        if (this.dontclose) {
             var text1 = Config.Lang.add;
             var text2 = Config.Lang.close;        
        } else {
             var text1 = Ext.MessageBox.buttonText.ok;
             var text2 = Ext.MessageBox.buttonText.cancel;
        }
        
        this.buttons = [
            {
				itemId: 'ok-button',
				disabled: true,
                text: text1,
                scope: this,
                handler: function() { 
                    this.processSelect(); 
                }
            }, {
                text: text2,
                scope: this,
                handler: function() { 
                    this.hide();
                }
            }
        ];
    
        this.callParent();
        
        this.on('show',function() {
			if (this.expandPath) this.tree.expandPath(this.expandPath, 'id', '/');
            if (this.path) this.tree.selectPath(this.path, 'id', '/');
        } , this);
		
		this.tree.on('select', function(){
			
			this.down('#ok-button').enable();
			
		},this);
    },
    
	setOnly : function(value) {
		this.only = value;
		this.tree.setOnly(value);
	},	
	
    processSelect: function() {
        var sn = this.tree.getSelectionModel().getLastSelected();
        this.path = sn.getPath();
        var name = sn.get('text');
        if (sn) {
            var a = sn.getId().split('-');
            var node_id = sn.get('node_id');
            var res = '';
            var n = '';
			var url = '/';
            while (sn.parentNode) {
                if (sn.text == 'root') break;
				if (sn.get('item_id') && !sn.get('isServer')) url = '/' + sn.get('alias') + url;
                var b = sn.get('text').split('</span>');
                n = b[1]?b[1]:b[0];
                if (res) res = n + ' / ' + res; else res = n;
                sn = sn.parentNode;
            }
            this.fireEvent('select', {
                id:         a[1],
                node_id:    node_id,
                path:       this.path,
                name:       name,
                name_to:    res,
				url:        url,
                table:      a[3]?a[2]:'main',
				type:       a[3]?a[3]:4,
            });
        }
        if (!this.dontclose) this.hide(); 
    }
    
});