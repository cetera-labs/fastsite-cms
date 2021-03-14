Ext.define('Cetera.eventlog.Panel', {

    extend: 'Ext.grid.GridPanel',
	requires: 'Cetera.model.Event',

    initComponent : function(){
    
        this.store = Ext.create('Ext.data.Store', {
            autoDestroy: true,
            remoteSort: true,
            fields: [
				'id','login','name','text','user_id',
                {name: 'dat', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ],
            sorters: [{property: "dat", direction: "DESC"}],
            totalProperty: 'total',
            pageSize: Config.defaultPageSize,
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_eventlog.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    rootProperty: 'rows',
                    keepRawData: true
                },
                extraParams: {
                    'limit': Config.defaultPageSize,
                    'filter[]' : []
                }
            }
        });
		
		this.eventsStore = Ext.create('Ext.data.Store',{
			model: 'Cetera.model.Event'		
		});	

		this.filterMenu = new Ext.menu.Menu();	

		this.eventsStore.load({
			scope: this,
			callback: function(records) {
				Ext.Array.each(records, function(rec){
					this.filterMenu.add({
						id: 'event_item_'+rec.getId(),
						text: rec.get('name'),
						checked: true, 
						scope: this,
						checkHandler: this.reload
					});
				}, this);
				this.reload();
			}
		});        
    
        this.tbar = [
            {
                iconCls:'x-fa fa-sync',
                tooltip:_('Обновить'),
                handler: function () { this.reload(); },
                scope: this
            },'-',{
                iconCls:'x-fa fa-broom',
                tooltip:_('Очистить журнал'),
                handler: function () { this.clean(); },
                scope: this
            },'-',{
                iconCls:'x-fa fa-filter',
                text: _('Фильтр событий'),
                menu: this.filterMenu 
            },'-',{
				iconCls:'x-fa fa-cog',
                text: _('Настройка'),
                handler: function () { 
					Ext.create('Cetera.eventlog.Setup');
				}
            }
        ];
        
        this.info = new Ext.Toolbar.TextItem();
               
        this.bbar = new Ext.PagingToolbar({
            store: this.store,
            items: ['->', this.info]
        });
                
        this.border = false;
        this.loadMask = true;
        this.stripeRows = true;
    
        this.callParent();
        
        this.store.on('load', function(s, records, options ) {
            var text = _('Всего')+ ': '+this.store.proxy.reader.rawData.total;
            this.info.setText(text);
        }, this);

    },
    
    afterShow: function() {        
        this.callParent();
    },
    
    columns: [
        {header: _('Событие'), width: 250, dataIndex: 'name'},
        {
			header: _('Пользователь'), 
			width: 150, 
			dataIndex: 'login', 
            renderer: function (value, p, record) {
                if (record.get('user_id') > 0) {
                    return '<a href="javascript:Cetera.getApplication().openBoLink(\'user:' + record.get('user_id') + '\')">'+value+'</a>';
                }
                else {
                    return '';
                }
            }
		},
        {header: _('Дата'), width: 120, dataIndex: 'dat', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')},
        {header: _('Дополнительно'), dataIndex: 'text', flex: 1}
    ],

    reload: function() {
        filter = [];
		
		this.eventsStore.each(function(rec){
			if (this.filterMenu.getComponent('event_item_'+rec.getId()).checked)
				filter[filter.length] = rec.getId(); 			
		},this);

        this.store.proxy.extraParams['filter[]'] = filter;
    
        this.store.load({params:{
            'start': 0
        }});
    },
    
    call: function(action) {
        Ext.Ajax.request({
            url: 'include/action_eventlog.php',
            params: { 
                action: action
            },
            scope: this,
            success: function(resp) {
                this.store.reload();
            }
        });
    },
    
    clean: function() {
        Ext.MessageBox.confirm(_('Очистить журнал'), _('Вы уверены'), function(btn) {
            if (btn == 'yes') this.call('clean');
        }, this);
    }
    
});