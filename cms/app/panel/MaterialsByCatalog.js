Ext.define('Cetera.panel.MaterialsByCatalog', {

    extend:'Cetera.panel.Materials',
	alias : 'widget.structurematerials',

    catalogId: 0,       // текущий раздел
    allow_own: false,   // право работать со своими материалами
    allow_all: false,   // право работать со всеми материалами
    allow_pub: false,   // право на публикацию материалов
	
	structureTree: mainTree,
                   
    reload: function() {
        this.store.proxy.extraParams.id = this.catalogId;
		this.store.proxy.extraParams.mat_type = this.mat_type;
        this.store.proxy.extraParams.math_subs = this.toolbar.getComponent('tb_mat_subs').pressed?1:0;
        this.callParent();   
    },
       
    catalogChanged: function() {
        this.store.removeAll();  
        this.catalogId = this.structureTree.getSelectedId();   
        if (this.catalogId > 0) {
            Ext.Ajax.request({
                url: 'include/action_materials.php',
                params: { action: 'permissions', id: this.catalogId },
                scope: this,
                async: false,
                success: function(resp) {
                    var obj = Ext.decode(resp.responseText);
                    if (obj.link) {
                        this.disable();
                        Ext.MessageBox.confirm(Config.Lang.attention, Config.Lang.msgCatLink, function(btn) {
                            if (btn == 'yes') this.structureTree.selectPath(obj.link, 'id');
                        }, this);
                    } else if (obj.right[4] == 0) {
                        this.disable();
                    } else {  
                        this.enable();                
                        this.allow_own = obj.right[0];
                        this.allow_all = obj.right[1];
                        this.allow_pub = obj.right[2];
                        this.mat_type  = obj.right[4];
                        
                        this.toolbar.getComponent('tb_mat_new').setDisabled(!this.allow_own && !this.allow_all);
                        this.toolbar.getComponent('tb_mat_subs').setDisabled(false); 
                        this.toolbar.enable();
                        this.reload(); 
                    }
                }
            });
        } else {
            this.disable();
        }
    }, 
    
    edit: function(idcat, id, mat_type) {
        if (this.editWindow) this.editWindow.destroy();
        this.editWindow = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        this.store.load();
                        this.stopInactivityTimer();
                    },
                    scope: this
                }
            }
        });
        
        if (!mat_type) mat_type = this.mat_type;
        var win = this.editWindow;
        win.show();
/*
        cc = Ext.create( 'Cetera.panel.MaterialEdit' , {
            win: win,
            objectId: id,
            sectionId: idcat,
            objectDefinitionId: this.mat_type,            
        }); 
        if (cc) { 
            cc.show(); 
            this.fireEvent('material_editor_ready', win, cc);
        } 
*/        

        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?type='+mat_type+'&idcat='+idcat+'&id='+id+'&height='+this.editWindow.height,
			scope: this,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+mat_type, {win: win});
                if (cc) { 
					         cc.show(); 
					         this.fireEvent('material_editor_ready', win, cc);
				        }
            }
        });
      
        // Таймер неактивности
        this.clearInactivityTimeout();
        this.timeoutTask = Ext.TaskManager.start({
             run: function() {
             
                if (this.globalTimeout <= 0) {
                    // таймаут сработал, останавливаем таймер
                    this.stopInactivityTimer();
                    this.editWindow.materialForm.saveAction(0,0,1);
                } else {
                    this.globalTimeout = this.globalTimeout - 1;
                }
             
             },
             interval: 1000,
             scope: this
        });

        Ext.EventManager.addListener("main_body", 'click', this.clearInactivityTimeout, this);
        Ext.EventManager.addListener("main_body", 'keypress', this.clearInactivityTimeout, this);
        
    },
          
    call: function(action, cat) {
        Ext.Ajax.request({
            url: 'include/action_materials.php',
            params: { 
                action: action, 
                math_subs: this.toolbar.getComponent('tb_mat_subs').pressed?1:0,
                id: this.catalogId, 
                'sel[]': this.getSelected(),
                cat: cat
            },
            scope: this,
            success: function(resp) {
                this.store.load();
            }
        });
    },
	
	getToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {items: [
            {
                itemId:       'tb_mat_new',
                iconCls: 'icon-new',
                tooltip: Config.Lang.newMaterial,
                handler: function () { this.edit(this.catalogId,0); },
                scope: this
            },
            {
                itemId: 'tb_mat_new1',
                disabled: true,
                iconCls:'icon-new1',
                tooltip: Config.Lang.newMaterialAs,
                handler: function () { this.edit(this.catalogId,this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            '-',
            {
                itemId: 'tb_mat_edit',
                disabled: true,
                iconCls:'icon-edit',
                tooltip: Config.Lang.edit,
                handler: function () { this.edit(0,this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            {
                itemId: 'tb_mat_delete',
                disabled: true,
                iconCls:'icon-delete',
                tooltip: Config.Lang.delete,
                handler: function () { this.deleteMat(); },
                scope: this
            },
            '-',
            {
                itemId: 'tb_mat_pub',
                disabled: true,
                iconCls:'icon-pub',
                tooltip: Config.Lang.publish,
                handler: function() { this.call('pub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_unpub',
                disabled: true,
                iconCls:'icon-unpub',
                tooltip: Config.Lang.unpublish,
                handler: function() { this.call('unpub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_preview',
                disabled: true,
                iconCls:'icon-preview',
                tooltip: Config.Lang.preview,
                handler: function () {
                    window.open('/cms/include/action_materials.php?action=preview&type='+this.mat_type+'&mat_id=' + this.getSelectionModel().getSelection()[0].getId());
                },
                scope: this
            },
            '-',
            {
                itemId: 'tb_mat_move',
                disabled: true,
                iconCls:'icon-move',
                tooltip: Config.Lang.move,
                handler: function () { this.move('move'); },
                scope: this
            },
            {
                itemId: 'tb_mat_copy',
                disabled: true,
                iconCls:'icon-copy',
                tooltip: Config.Lang.copy,
                handler: function () { this.move('copy'); },
                scope: this
            },			
            '-',
            {
                itemId: 'tb_mat_subs',
                iconCls:'icon-subs',
                tooltip: Config.Lang.materialDeep,
                enableToggle: true,
                toggleHandler: function () { this.reload(); },
                pressed: false,
                scope: this
            },
			'-',			
            {
                itemId: 'tb_mat_up',
                disabled: true,
				hidden: true,
                iconCls:'icon-up',
                tooltip: Config.Lang.upper,
                handler: function() { 
                    if (this.store.sorters.get(0).direction == 'ASC') 
                        this.call('up'); 
                        else this.call('down');
                },
                scope: this
            },
            {
                itemId: 'tb_mat_down',
                disabled: true,
				hidden: true,
                iconCls:'icon-down',
                tooltip: Config.Lang.downer,
                handler: function() {
                    if (this.store.sorters.get(0).direction == 'ASC') 
                        this.call('down'); 
                        else this.call('up');
                },
                scope: this
            }
        ]});		
	},
    
    initComponent : function() {
		
		this.callParent();  
         
        this.on({
            'beforedestroy': function() {
                this.structureTree.getSelectionModel().removeListener('selectionchange', this.catalogChanged, this);
            },
			'celldblclick' : function() {
                this.edit(0,this.getSelectionModel().getSelection()[0].getId());
            },		
			'sortchange' : function( ct, column, direction, eOpts ) {
                var sf = this.store.sorters.first().property;
                this.toolbar.getComponent('tb_mat_up').setVisible(sf == 'tag');
                this.toolbar.getComponent('tb_mat_down').setVisible(sf == 'tag');
			},
            scope: this
        });  
        
        this.getSelectionModel().on({
            'selectionchange' : function(sm){
                var hs = sm.hasSelection();
                var sf = this.store.sorters.first().property;
				this.toolbar.getComponent('tb_mat_new1').setDisabled(!hs);
                this.toolbar.getComponent('tb_mat_preview').setDisabled(!hs);
                this.toolbar.getComponent('tb_mat_up').setDisabled(!hs || sf != 'tag');
                this.toolbar.getComponent('tb_mat_down').setDisabled(!hs || sf != 'tag');
				this.toolbar.getComponent('tb_mat_pub').setDisabled(!hs || !this.allow_pub);
                this.toolbar.getComponent('tb_mat_unpub').setDisabled(!hs || !this.allow_pub);
            },
            scope:this
        });
        
        this.structureTree.getSelectionModel().addListener('selectionchange', this.catalogChanged, this);
        
        this.catalogChanged();               
        
    }
                
});