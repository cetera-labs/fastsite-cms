Ext.define('Cetera.panel.MaterialsByCatalogPanel', {

    extend:'Cetera.panel.Materials',
	alias : 'widget.materials-by-catalog',

    catalogId: 0,       // текущий раздел
    allow_own: false,   // право работать со своими материалами
    allow_all: false,   // право работать со всеми материалами
    allow_pub: false,   // право на публикацию материалов
	
	structureTree: null,
                   
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
                url: '/cms/include/action_materials.php',
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

        var cc = Ext.create( 'Cetera.panel.MaterialEdit' , {
            win: win,
            objectId: id,
            sectionId: idcat,
            objectDefinitionId: this.mat_type,            
        }); 
        if (cc) { 
            cc.show(); 
            this.fireEvent('material_editor_ready', win, cc);
        } 

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
            url: '/cms/include/action_materials.php',
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
                iconCls: 'x-fa fa-bars',
                tooltip: _('Показать структуру'),
                handler: function () { 
                    if (this.structureTree.isHidden()) {
                        this.structureTree.show(); 
                    }
                    else {
                        this.structureTree.hide(); 
                    }
                },
                scope: this
            },        
            {
                itemId:  'tb_mat_new',
                iconCls: 'x-far fa-file',
                tooltip: _('Создать'),
                handler: function () { this.edit(this.catalogId,0); },
                scope: this
            },
            {
                itemId: 'tb_mat_new1',
                disabled: true,
                iconCls: 'x-fa fa-file',
                tooltip: _('Создать по образцу'),
                handler: function () { this.edit(this.catalogId,this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            {
                itemId: 'tb_mat_edit',
                disabled: true,
                iconCls: 'x-fa fa-edit',
                tooltip: _('Изменить'),
                handler: function () { this.edit(0,this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            {
                itemId: 'tb_mat_delete',
                disabled: true,
                iconCls: 'x-fa fa-trash',
                tooltip: _('Удалить'),
                handler: function () { this.deleteMat(); },
                scope: this
            },
            {
                itemId: 'tb_mat_pub',
                disabled: true,
                iconCls: 'x-fa fa-eye',
                tooltip: _('Опубликовать'),
                handler: function() { this.call('pub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_unpub',
                disabled: true,
                iconCls: 'x-fa fa-eye-slash',
                tooltip: _('Отменить публикоцию'),
                handler: function() { this.call('unpub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_preview',
                disabled: true,
                iconCls: 'x-fab fa-internet-explorer',
                tooltip: _('Предварительный просмотр'),
                handler: function () {
                    window.open('/cms/include/action_materials.php?action=preview&type='+this.mat_type+'&mat_id=' + this.getSelectionModel().getSelection()[0].getId());
                },
                scope: this
            },
            {
                itemId: 'tb_mat_move',
                disabled: true,
                iconCls:'x-far fa-copy',
                tooltip: _('Переместить'),
                handler: function () { this.move('move'); },
                scope: this
            },
            {
                itemId: 'tb_mat_copy',
                disabled: true,
                iconCls: 'x-fa fa-copy',
                tooltip: _('Копировать'),
                handler: function () { this.move('copy'); },
                scope: this
            },			
            {
                itemId: 'tb_mat_subs',
                iconCls:'x-fa fa-sitemap',
                tooltip: _('Показать материалы из подразделов'),
                enableToggle: true,
                toggleHandler: function () { this.reload(); },
                pressed: false,
                scope: this
            },
            {
                itemId: 'tb_mat_up',
                disabled: true,
				hidden: true,
                iconCls:'x-fa fa-arrow-up',
                tooltip: _('Вверх'),
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
                iconCls:'x-fa fa-arrow-down',
                tooltip: _('Вниз'),
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

        if (!this.structureTree) {
            this.structureTree = Ext.getCmp('main_tree');
        }        
         
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