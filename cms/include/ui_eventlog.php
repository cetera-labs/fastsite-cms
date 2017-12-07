<?php
namespace Cetera;
header('Content-Type: application/javascript; charset=UTF-8');
/*****************************************
 *  Cetera CMS 3                         *
 *  Интерфейс журнала событий            *
 *****************************************/ 
  
include('common_bo.php');
include('common_eventlog.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

?>
Ext.define('EventlogPanel', {

    extend: 'Ext.grid.GridPanel',

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
            pageSize: Cetera.defaultPageSize,
            proxy: {
                type: 'ajax',
                url: 'include/data_eventlog.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    root: 'rows'
                },
                extraParams: {
                    'limit': Cetera.defaultPageSize,
                    'filter[]' : []
                }
            }
        });
        
        this.filterMenu = new Ext.menu.Menu({
            items: [
<? $first = 1; foreach ($event_name_code as $code => $name) : ?>
<? if (!$first) echo ','; $first = 0;?>
                {
                    id: 'event_item_<?=$code?>',
                    text: '<?=$name?>',
                    checked: true, 
                    scope: this,
                    checkHandler: this.reload
                }
<? endforeach; ?>
            ]
        });
    
        this.tbar = [
            {
                iconCls:'icon-reload',
                tooltip:_('Обновить'),
                handler: function () { this.reload(); },
                scope: this
            },'-',{
                iconCls:'icon-clean',
                tooltip:_('Очистить журнал'),
                handler: function () { this.clean(); },
                scope: this
            },'-',{
                text: _('Фильтр событий'),
                menu: this.filterMenu 
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
        
        this.reload();
    },
    
    columns: [
        {header: _('Событие'), width: 200, dataIndex: 'name'},
        {
			header: _('Пользователь'), 
			width: 150, 
			dataIndex: 'login', 
            renderer: function (value, p, record) {
                return '<a href="javascript:Cetera.getApplication().openBoLink(\'user:' + record.get('user_id') + '\')">'+value+'</a>';
            }
		},
        {header: _('Дата'), width: 105, dataIndex: 'dat', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')},
        {header: _('Дополнительно'), width: 275, dataIndex: 'text', flex: 1}
    ],

    reload: function() {
        filter = [];
<? foreach ($event_name_code as $code => $name) : ?>
        if (this.filterMenu.getComponent('event_item_<?=$code?>').checked)
            filter[filter.length] = <?=$code?>; 
<? endforeach; ?>

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