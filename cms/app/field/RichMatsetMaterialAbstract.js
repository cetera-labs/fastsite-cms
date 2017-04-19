Ext.define('Cetera.field.RichMatsetMaterialAbstract', {
    extend: 'Ext.FormPanel',
    
    closable: true,
    animCollapse: false,
    draggable: {moveOnDrag: false},
    
    margin: '5 5 0 5',
    bodyCls: 'x-window-body-default',
    bodyPadding: 5,
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    }, 
    
    material_id: 0,
    
    initComponent : function(){
        this.tools = [{
            type:'up',
            scope: this,
            handler: function(){
                var p = this.up();
                var idx = p.items.indexOf(this);
                if (idx > 0 )
                    p.move(idx, idx-1);
            }
        },{
            type:'down',
            scope: this,
            handler: function(){
                var p = this.up();
                var idx = p.items.indexOf(this);
                if (idx < p.items.getCount()-1 )
                    p.move(idx, idx+1);
            }
        },{
            type:'toggle',
            scope: this,
            handler: function(){
                this.toggleCollapse();
            }
        }];
        
        if (this.values) this.material_id = this.values.getId();
        
        this.on('beforecollapse', function(){
            var title = this.items.getAt(0).getValue();
            if (!title) title = Config.Lang.noname;
            this.setTitle(title);
        });
        
        this.on('beforeexpand', function(){
            this.setTitle('');
        });
        
        this.callParent();  
    },
    
    setTitle : function(title) {
        title += ' [ID:'+this.material_id+']';
        this.callParent([title]);
    },
    
    afterRender : function() {
        this.setTitle('');
        this.callParent(arguments);
    }
});