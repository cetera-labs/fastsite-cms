Ext.define('Cetera.users.MainPanel', {

    extend:'Ext.grid.GridPanel',

    border: false,
    loadMask: true,
    stripeRows: true,
	
    stateful: true,
    stateId: 'stateMainUsersGrid',
    
    initComponent : function(){
		
		this.columns = [
			{
				title: "ID", 
				hideable: false, 
				menuDisabled: true, draggable: false,
				width: 100, 
				sortable: true, 
				dataIndex: 'id'
			},		
			{
				tooltip: "CMS", 
				hideable: false, menuDisabled: true, draggable: false,
				width: 30, 
				sortable: false, 
				dataIndex: 'bo', 
				renderer: function (value, metaData){
					if (value) metaData.css = 'icon-cms';
				}
			},
			{
				tooltip: _('Заблокирован'),
				hideable: false, menuDisabled: true, draggable: false,
				width: 30, sortable: false, dataIndex: 'disabled', 
				renderer: function (value, metaData){
					if (value) {
						return '<span class="x-fa fa-user-lock"></span>';
                    }
				}
			},
			{
				tooltip: _('Соцсети'), 
				hideable: false, menuDisabled: true, draggable: false,
				width: 30, sortable: false, 
				dataIndex: 'external', 
				renderer: function (value, metaData){
					if (value == -6) return '<span class="x-fab fa-facebook-square"></span>';
					if (value == -7) return '<span class="x-fab fa-twitter-square"></span>';
					if (value == -8) return '<span class="x-fab fa-vk"></span>';
					if (value == -10) return '<span class="x-fab fa-odnoklassniki-square"></span>';
					if (value == -9) return '<span class="x-fab fa-google-plus-square"></span>';
				}
			}
		];	
		
		var userFields = ['id','disabled', 'bo', 'external'];
		
		Ext.Array.each(Config.userObjectGridFields, function(item, index) {
			var c = {
				dataIndex: item.name,
				text: item.describ,
				hidden: !item.fixed
			};
			if (item.type == Config.fields.FIELD_DATETIME) {
				c.width = 105;
				c.xtype = 'datecolumn';
				c.format = 'd.m.Y H:i';
			}
			else if (item.type == Config.fields.FIELD_BOOLEAN) {
				c.width = 100;
				c.align = 'center';
				c.renderer = function (value) {
					if (parseInt(value)) return _('Да');
					return _('Нет');
				}
			}			
			else {
				c.flex = 1;
			}
			Ext.Array.push(this.columns, c);			
			Ext.Array.push(userFields, item.name);
		}, this);	
		
		//console.log(this.columns);
			
        this.store = Ext.create('Ext.data.JsonStore', {
            autoDestroy: true,
            remoteSort: true,
            fields: userFields,
            sorters: [{property: "login", direction: "ASC"}],
            totalProperty: 'total',  
            pageSize: Config.defaultPageSize,
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_users.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    rootProperty: 'rows',
                    keepRawData: true
                },
                extraParams: {
                    'bo': false,
                    limit: Config.defaultPageSize
                }
            }    
        });
    
        this.tbar = [
            {
                iconCls:'x-fa fa-sync',
                tooltip: _('Обновить'),
                handler: function () { this.reload(); },
                scope: this
            },
            {
                id: 'tb_user_new',
                iconCls:'x-fa fa-user-plus',
                tooltip: _('Новый пользователь'),
                handler: function () { this.edit(0); },
                scope: this
            },
            {
                id: 'tb_user_props',
                iconCls:'x-fa fa-user-edit',
                tooltip: _('Свойства'),
                handler: function () { this.edit(this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },            
            {
                id: 'tb_user_delete',
                iconCls:'x-fa fa-user-slash',
                tooltip: _('Удалить'),
                handler: this.deleteUsers,
                scope: this
            },'-',{
                id: 'tb_user_disable',
                iconCls:'x-fa fa-user-lock',
                tooltip: _('Запретить доступ'),
                handler: function () { this.call('disable'); },
                scope: this
            },{
                id: 'tb_user_enable',
                iconCls:'x-fa fa-user-check',
                tooltip:_('Разрешить доступ'),
                handler: function () { this.call('enable'); },
                scope: this
            },'-',{
                id: 'tb_user_bo',
                iconCls:'icon-cms',
                enableToggle: true,
                tooltip: _('Показывать только пользователей BackOffice'),
                handler: function (b) { 
                    this.store.proxy.extraParams.bo = b.pressed;
                    this.reload();
                },
                scope: this
            }
        ];
        
        this.info = new Ext.Toolbar.TextItem();
        
        this.filter = Ext.create('Cetera.field.Search', {
            store: this.store,
            paramName: 'query',
            width:150
        });
               
        this.bbar = new Ext.PagingToolbar({
            store: this.store,
            items: [_('Фильтр')+': ', this.filter, '->', this.info]
        });
                                   
        this.callParent();
        
        this.getSelectionModel().on({
            'selectionchange' : function(sm){
                var hs = sm.hasSelection();
                Ext.getCmp('tb_user_delete').setDisabled(!hs);
                Ext.getCmp('tb_user_disable').setDisabled(!hs);
                Ext.getCmp('tb_user_enable').setDisabled(!hs);
                Ext.getCmp('tb_user_props').setDisabled(!hs);
            },
            scope:this
        });
        
        this.on({
            'celldblclick' : function() {
                this.edit(this.getSelectionModel().getSelection()[0].getId());
            },
            scope: this
        });
        
        this.store.on('load', function(s, records, options ) {
            var text = _('Всего')+': '+this.store.proxy.reader.rawData.total;
            this.info.setText(text);
        }, this);
        
        this.reload();
    },

    reload: function() {
        this.store.load({params:{
            start: 0
        }});
    },
    
    call: function(action) {
        Ext.Ajax.request({
            url: '/cms/include/action_users.php',
            params: { 
                action: action, 
                'sel[]': this.getSelected()
            },
            scope: this,
            success: function(resp) {
                this.store.reload();
            }
        });
    },
    
    getSelected: function() {
        var a = this.getSelectionModel().getSelection();
        ret = [];
        for (var i=0; i<a.length; i++) ret[i] = a[i].getId();
        return ret;
    },
    
    deleteUsers: function() {
        Ext.MessageBox.confirm(_('Удаление пользователя'), _('Вы уверены'), function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
    
    edit: function(id) {		
		Cetera.getApplication().editUser(id, function(){ this.store.reload(); }, this);
    }
});