Ext.define('Cetera.panel.MaterialEdit', {
    extend: 'Ext.FormPanel',

    layout: 'fit',
    bodyStyle: 'background: none',
    border   : false,
    pollForChanges: true,    
    timeout : 100,    
    
    fieldDefaults: {
        labelAlign: 'right',
        labelWidth: 180
    },   
    
    objectId: 0,
    sectionId: 0,
    objectDefinitionId: 0,
    objectDefinitionAlias: '',
    isModal: 0,
    isDuplicate: 0,
        
    saveParams: {},  

    editData: null,
    
    initComponent : function() {
        
        if (this.sectionId == -2) {
            this.objectDefinitionId = Config.userObjectDefinitionId
        }
                
        this.task = Ext.TaskManager.start({
             run: function() {
                if (this.objectId > 0) {
                    Ext.Ajax.request({
                        url: 'include/action_materials.php?action=lock&mat_id=' + this.objectId + '&type=' + this.objectDefinitionId,
                        failure: function(){},
                        scope: this
                    });
                }
             },
             scope: this,
             interval: 10000
        }); 
        
        var tabs = [];

        if (this.sectionId == -2) {
            this.generateUserPanels();
            tabs.push([
                {
                    title:_('Членство в группах'),
                    layout:'anchor',
                    defaults: {anchor: '0'},
                    border    : false,
                    bodyBorder: false,
                    bodyStyle:'background: none; padding: 5px',
                    items: [this.mGrid, this.aGrid]
                }            
            ]);
        }
        
        this.tabPanel = Ext.create('Ext.TabPanel',{
            activeTab : 0,
            border    : false,
            bodyStyle :'background: none',
            deferredRender: false,
            defaults  :{
                height: this.win.height-105, 
                bodyStyle:'padding:10px'
            }, 
            items: tabs
        });
        
        this.items = this.tabPanel;
        
        this.buttons = [];
        
        this.saveParams = {};     
        
        this.callParent();        
        
        Ext.Ajax.request({
            url: 'include/data_object_for_edit.php',
            params: {
                od_id: this.objectDefinitionId,
                od_alias: this.objectDefinitionAlias,
                id: this.objectId,
                height: this.win.height,
                section_id: this.sectionId,
                duplicate: this.isDuplicate,
            },
            success: function(response){
                this.editData = Ext.decode(response.responseText);
                this.buildEditor();
            },           
            scope: this
        });        
    },
    
    buildEditor : function() {
        this.win.setTitle('Редактирование: [' + this.editData.object_definition.alias + ':' + this.objectId + '] ' + this.editData.fields.name);
        
        this.saveParams = {
            table: this.editData.object_definition.alias, 
            id: this.objectId, 
            catalog_id: this.editData.section_id
        };         
        
        if (this.editData.fields.idcat > 0 && !this.isModal) { 
                                                 
            var buttons = [
                {
                    text: _('Сохранить'),
                    handler: this.save,
                    scope: this
                },{
                    text: _('Сохранить и опубликовать'),
                    disabled: !this.editData.permissions.publish,
                    handler: function() { 
                        this.save_publish(0);
                    },
                    scope: this
                },{
                    text: _('Предпросмотр'),
                    disabled: !this.editData.preview_url,
                    handler: this.save_preview,
                    scope: this
                }
            ];
            
        } else {
                        
            var buttons = [
                {
                    text: _('OK'),
                    handler: function() { 
                        this.save_publish(1);
                    },
                    scope: this
                },
                {
                    text: _('Отмена'),
                    scope: this,
                    handler: function() { 
                        this.win.returnValue = false;
                        this.win.close(); 
                    }
                }
            ];
            
        }        
        
        this.down('>toolbar[dock="bottom"]').add(buttons);
        
        if (this.editData.init) {
            eval(this.editData.init);
        }
        
        Ext.Array.each( this.editData.tabs, function(tab){
            var items = tab.items;
            tab.items = [];
            Ext.Array.each( items, function(item){
                tab.items.push( eval(item) );
            }, this );
            this.tabPanel.add(tab);
        }, this );
        this.tabPanel.setActiveTab(0);
    },
    
    show : function() {
           
        this.win.on('beforeclose', function(){
            if (this.task) Ext.TaskManager.destroy(this.task);
            Ext.Ajax.request({
                url: 'include/action_materials.php?action=clear_lock&mat_id=' + this.objectId + '&type=' + this.objectDefinitionId
            });
            this.destroy();
        }, this);
        
        this.win.add(this);
        this.win.doLayout();
        this.win.show();  
        if (this.win.getEl()) 
            this.win.getEl().child('div > table.loading').setStyle('display','none');            
        this.win.materialForm = this;      
        this.callParent();
    },
    
    save: function(){
        this.saveAction(0,0);
    },
    
    save_publish: function(close){
        this.saveAction(1,0,close);
    },
    
    save_preview: function(){
        this.saveAction(0,1);
    },
    
    saveAction: function(publish,preview,close) {
        this.saveParams.publish = publish;
                
        if (this.sectionId == -2) {
            this.saveParams['groups[]'] = [0];
            if (this.mGrid.store.getCount()) {    
                this.mGrid.store.each(function(r) {
                    this.saveParams['groups[]'].push(r.get('id'));
                }, this);
            }
        }
        
        if (!this.getForm().isValid()) {
            if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', Config.Lang.materialFixFields, 3000);
            var f = this.getForm().findInvalid();
            if (f) {
                f.getAt(0).ensureVisible();
                return;
            }
        }			
        
        this.getForm().submit({
            url:'include/action_material_save.php', 
            params: this.saveParams,
            waitMsg: _('Подождите ...'),
            scope: this,
            success: function(form, action) {
                if (Cetera.getApplication) Cetera.getApplication().msg(Config.Lang.materialSaved, '', 1000);
                this.saveParams.id = action.result.id;
                this.fireEvent('material_saved', this.saveParams);
                this.win.returnValue = {id: action.result.id, name: form.getValues().name, values: form.getValues()};
                if (this.sectionId < 0) {
                    this.win.close();
                } else {
                    this.getForm().findField('alias').setValue(action.result.alias);
                    if (preview) window.open(this.editData.preview_url + action.result.alias);
                    if (close) this.win.close();
                }
            },
            failure: function(form, action) {
                var s = '';
                //console.log(action);
                if (action.result)
                {
                    Ext.Object.each(action.result.errors, function(key, value, myself) {
                        s += value + '<br>';
                    });	
                    if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', s, 3000);
                }
                else
                {
                    var obj = Ext.decode(action.response.responseText);
                    if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', '', 3000);
                    var win = Ext.create('Cetera.window.Error', {
                        msg: obj.message,
                        ext_msg: obj.ext_message
                    });
                    win.show();						
                }
        
                
                var f = form.findInvalid();
                if (f) {
                    f.getAt(0).ensureVisible();
                }

            }
        });
    },    
    
    generateUserPanels: function() {
        this.mGrid = new Ext.grid.GridPanel({
            store: new Ext.data.JsonStore({
                autoLoad: true,
                fields: ['id', 'name'],
                proxy: {
                    type: 'ajax',
                    url: 'include/data_groups.php?member=' + this.objectId,
                    simpleSortMode: true,
                    reader: {
                        type: 'json',
                        root: 'rows'
                    }							
                }
            }),
            columns          : [
                {width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
                {dataIndex: 'name', flex: 1}
            ],
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'memberGridDDGroup',
                    dropGroup: 'allGridDDGroup'
                }
            },
            anchor: '100% 50%',
            hideHeaders      : true,
            title            : _('Состоит в'),
            listeners        : {
                itemdblclick : {
                    fn: function(t, record) {
                        this.mGrid.store.remove(record);
                        this.aGrid.store.add(record);
                    },
                    scope: this       
                }
            } 
        });
    
        this.aGrid = new Ext.grid.GridPanel({
            store: Ext.create('Ext.data.JsonStore',{
                autoLoad: true,
                fields: ['id', 'name'],
                proxy: {
                    type: 'ajax',
                    url: 'include/data_groups.php?avail=' + this.objectId,
                    simpleSortMode: true,
                    reader: {
                        type: 'json',
                        root: 'rows'
                    }							
                }
            }),
            columns          : [
                {width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
                {dataIndex: 'name', flex: 1}
            ],
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'allGridDDGroup',
                    dropGroup: 'memberGridDDGroup'
                }
            },
            margin: '5 0 0 0',
            anchor: '100% 50%',
            hideHeaders      : true,
            title            : _('Группы'),
            listeners        : {
                itemdblclick : {
                    fn: function(t, record) {
                        this.aGrid.store.remove(record);
                        this.mGrid.store.add(record);
                    },
                    scope: this    
                }
            }
        });         
    }
    
});