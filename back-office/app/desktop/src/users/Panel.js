Ext.define('Cetera.users.Panel', {

    extend:'Ext.grid.GridPanel',

    checked: [],
    unchecked: [],

    initComponent : function(){
        
        this.baseParams = this.baseParams || {};
        this.bo = this.bo || false;
        this.baseParams['limit'] = Cetera.defaultPageSize;
        this.baseParams['bo'] = this.bo;
        
        this.store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            fields: ['id','login','name','disabled', 'bo', 'checked'],
            sorters: [{property: "login", direction: "ASC"}],
            totalProperty: 'total',
            pageSize: Cetera.defaultPageSize,
            proxy: {
                type: 'ajax',
                url: (this.url)?this.url:'/cms/include/data_users.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    rootProperty: 'rows',
                    keepRawData: true
                },
                extraParams: this.baseParams
            }
        });
        
        this.info = new Ext.Toolbar.TextItem();
        
        var items = [{
                iconCls:'icon-cms',
                enableToggle: true,
                pressed: this.bo,
                tooltip: Config.Lang.usersBo,
                handler: function (b) { 
                    this.store.proxy.extraParams.bo = b.pressed;
                    this.reload();
                },
                scope: this
            },'-',
            Config.Lang.filter + ': ', 
            Ext.create('Cetera.field.Search', {
                store: this.store,
                paramName: 'query',
                width:150
            })
        ];
        
        if (this.tbar) items = items.concat(this.tbar); 
        
        this.tbar = new Ext.Toolbar({
            items: items
        });
                   
        this.bbar = new Ext.PagingToolbar({
            store: this.store,
            items: ['->',this.info]
        });
        
        if (this.filter) this.tbar.add('->', new Ext.form.Checkbox({
            boxLabel: this.filter,
            listeners: {
                check: function (cb, checked) {
                    this.store.proxy.extraParams = this.store.proxy.extraParams || {};
                    this.store.proxy.extraParams['filter'] = checked?1:0;
                    this.store.reload({params:{start: 0}});
                }, 
                scope: this
            }
        }));
               
        if (this.nocheckboxes) {
            this.columns= [
                {header: "", width: 25, sortable: false, dataIndex: 'bo', renderer: this.renderIconCms},
                {header: "", width: 25, sortable: false, dataIndex: 'disabled', renderer: this.renderIcon},
                {header: Config.Lang.nickname, width: 250, dataIndex: 'login'},
                {header: Config.Lang.name, flex:1, dataIndex: 'name'}
            ];
        } else {
            this.columns = [
                {
                    dataIndex: 'checked',
                    xtype: 'checkcolumn',
                    id: 'check',
                    width: 25,
                    listeners: {
                        checkchange: function (c, rowIndex, checked, eOpts) {
                            var rid = this.store.getAt( rowIndex ).getId();
                            if (checked) {
                                if (this.unchecked[rid]) {
                                    delete this.unchecked[rid];
                                } else {
                                    this.checked[rid] = rid;
                                }
                            } else {
                                if (this.checked[rid]) {
                                    delete this.checked[rid];
                                } else {
                                    this.unchecked[rid] = rid;
                                }
                            }
                        }, 
                        scope: this
                    }
                },
                {header: "", width: 25, sortable: false, dataIndex: 'bo', renderer: this.renderIconCms},
                {header: "", width: 25, sortable: false, dataIndex: 'disabled', renderer: this.renderIcon},
                {header: Config.Lang.nickname, width: 250, dataIndex: 'login'},
                {header: Config.Lang.name, flex:1, dataIndex: 'name'}
            ];
        }
        
        this.store.on('load', function(s, records, options ) {
            for (var id in this.checked) {
                var r = s.getById(id);
                if (r) r.set('checked', true);
            }
            for (var id in this.unchecked) {
                var r = s.getById(id);
                if (r) r.set('checked', false);
            }
            
            var text = _('Всего') + ': ' + this.store.getProxy().getReader().rawData.total;
            if (this.store.proxy.reader.rawData.checked) 
                text += ' ' + this.store.proxy.reader.rawData.checked;  
            this.info.setText(text);
        }, this);
                       
        this.border = false;
        this.loadMask = true;
        this.stripeRows = true;
    
        this.callParent(); 
    },

    reload: function() {
        this.store.load({
            params: { start: 0 }
        });
    },
    
    removeAll: function() {
        this.store.removeAll();
        this.unchecked = [];
        this.checked = [];
    },

    renderIcon: function (value, metaData){
        if (value)
            metaData.css = 'icon-user-disabled';
            else metaData.css = 'icon-user';
    },
    
    renderIconCms: function (value, metaData){
        if (value) metaData.css = 'icon-cms';
    }
});
