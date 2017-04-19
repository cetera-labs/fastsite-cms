<?php
namespace Cetera;
header('Content-Type: application/javascript; charset=UTF-8');
/*****************************************
 *  Cetera CMS 3                         *
 *  Интерфейс работы с пользователями    *
 *****************************************/ 
  
include_once('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

?>
Ext.define('UsersPanel', {

    extend:'Ext.grid.GridPanel',

    border: false,
    loadMask: true,
    stripeRows: true,
    
    columns: [
        {
            header: "", width: 25, sortable: false, dataIndex: 'bo', 
            renderer: function (value, metaData){
                if (value) metaData.css = 'icon-cms';
            }
        },
        {
            header: "", width: 25, sortable: false, dataIndex: 'disabled', 
            renderer: function (value, metaData){
                if (value)
                    metaData.css = 'icon-user-disabled';
                    else metaData.css = 'icon-user';
            }
        },
        {
            header: "", width: 25, sortable: false, dataIndex: 'external', 
            renderer: function (value, metaData){
                if (value == <?=USER_FACEBOOK?>) metaData.css = 'icon-fb';
                if (value == <?=USER_TWITTER?>) metaData.css = 'icon-tw';
                if (value == <?=USER_VK?>) metaData.css = 'icon-vk';
                if (value == <?=USER_ODNOKLASSNIKI?>) metaData.css = 'icon-odno';
				if (value == <?=USER_GOOGLE?>) metaData.css = 'icon-google';
            }
        },
        {header: "<?=$translator->_('Псевдоним')?>", width: 200, dataIndex: 'login'},
        {header: "E-mail", width: 200, dataIndex: 'email'},
        {header: "<?=$translator->_('Имя')?>", flex: 1, dataIndex: 'name'},
        {header: "<?=$translator->_('Дата регистрации')?>", width: 105, dataIndex: 'date_reg', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')},
        {header: "<?=$translator->_('Дата последнего входа')?>", width: 105, dataIndex: 'last_login', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')}
    ],

    initComponent : function(){
    
        this.store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            fields: ['id','login','name','email','disabled', 'bo', 'external',
                {name: 'last_login', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'date_reg', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ],
            sorters: [{property: "login", direction: "ASC"}],
            totalProperty: 'total',  
            pageSize: Cetera.defaultPageSize,         
            proxy: {
                type: 'ajax',
                url: 'include/data_users.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
					root: 'rows',
                    rootProperty: 'rows'
                },
                extraParams: {
                    'bo': false,
                    limit: Cetera.defaultPageSize
                }
            }    
        });
    
        this.tbar = [
            {
                iconCls:'icon-reload',
                tooltip:'<b><?=$translator->_('Обновить')?></b>',
                handler: function () { this.reload(); },
                scope: this
            },{
                id: 'tb_user_new',
                iconCls:'icon-new-user',
                tooltip:'<b><?=$translator->_('Новый пользователь')?></b>',
                handler: function () { this.edit(0); },
                scope: this
            },{
                id: 'tb_user_delete',
                iconCls:'icon-user-delete',
                tooltip:'<b><?=$translator->_('Удалить')?></b>',
                handler: this.deleteUsers,
                scope: this
            },'-',{
                id: 'tb_user_disable',
                iconCls:'icon-user-disabled',
                tooltip:'<b><?=$translator->_('Запретить доступ')?></b>',
                handler: function () { this.call('disable'); },
                scope: this
            },{
                id: 'tb_user_enable',
                iconCls:'icon-user',
                tooltip:'<b><?=$translator->_('Разрешить доступ')?></b>',
                handler: function () { this.call('enable'); },
                scope: this
            },'-',{
                id: 'tb_user_props',
                iconCls:'icon-props',
                tooltip:'<b><?=$translator->_('Свойства')?></b>',
                handler: function () { this.edit(this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },'-',{
                id: 'tb_user_bo',
                iconCls:'icon-cms',
                enableToggle: true,
                tooltip:'<b><?=$translator->_('Показывать только пользователей BackOffice')?></b>',
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
            items: ['<?=$translator->_('Фильтр')?>: ', this.filter, '->', this.info]
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
            var text = '<?=$translator->_('Всего')?>: '+this.store.proxy.reader.rawData.total;
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
            url: 'include/action_users.php',
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
        Ext.MessageBox.confirm('<?=$translator->_('Удаление пользователя')?>', '<?=$translator->_('Вы уверены')?>?', function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
    
    edit: function(id) {
        if (this.editWindow) this.editWindow.destroy();
        
        this.editWindow = Ext.create('Cetera.window.MaterialEdit', { 
            title: '<?=$translator->_('Пользователь')?>',
            listeners: {
                close: {
                    fn: function(win){
                        this.store.reload();
                    },
                    scope: this
                }
            }
        });
        
        var win = this.editWindow;
		win.show();
        
        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?idcat=<?=CATALOG_VIRTUAL_USERS?>&id='+id+'&height='+this.editWindow.height,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor<?=User::TYPE?>', {win: win});
                if (cc) cc.show();
            }
        });
            
    }
});