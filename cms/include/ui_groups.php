<?php
namespace Cetera;
header('Content-Type: application/javascript; charset=UTF-8');
/*****************************************
 *  Cetera CMS 3                         *
 *  Интерфейс работы с группами          *
 *****************************************/ 
  
include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

?>
Ext.define('GroupsPanel', {

    extend:'Ext.grid.GridPanel',

    initComponent : function(){
       
        this.tbar = [
            {
                iconCls:'icon-reload',
                tooltip:'<b><?=$translator->_('Обновить')?></b>',
                handler: function () { this.reload(); },
                scope: this
            },{
                id: 'tb_group_new',
                iconCls:'icon-new-group',
                tooltip:'<b><?=$translator->_('Новая группа')?></b>',
                handler: function () { this.newGroup(); },
                scope: this
            },{
                id: 'tb_group_delete',
                iconCls:'icon-delete2',
                disabled: true,
                tooltip:'<b><?=$translator->_('Удалить')?></b>',
                handler: this.deleteGroup,
                scope: this
            },'-',{
                id: 'tb_group_props',
                iconCls:'icon-props',
                disabled: true,
                tooltip:'<b><?=$translator->_('Свойства')?></b>',
                handler: function () { this.edit(); },
                scope: this
            }
        ]; 
        
        this.users = Ext.create('Cetera.users.Panel', {
            baseParams: {'gid': 0},
            filter: '<?=$translator->_('члены группы')?>',
            title: '<?=$translator->_('Пользователи группы')?>',
            region: 'center',
            bo: true
        });

        this.propForm = new Ext.form.FormPanel({
            region: 'north',
            baseCls    : 'x-plain',
            width      : '100%',
            defaultType: 'textfield',
            bodyStyle  : 'padding:5px 5px 0;',
            fieldDefaults: {
                labelWidth : 100,
                anchor: '0' 
            },
            waitMsgTarget: true,
            items      : [
                {
                    fieldLabel: '<?=$translator->_('Имя группы')?>',
                    allowBlank: false,
                    name: 'name'
                },{
                    fieldLabel: '<?=$translator->_('Описание')?>',
                    name: 'describ'
                }
            ]
        });  
        
        this.propWin = new Ext.Window({
            closable:true,
            width:600,
            height:500,
            closeAction: 'hide',
            plain:true,
            resizable: false,
            modal: true,
            header: true,
            layout: 'border',
            items: [this.propForm, this.users],
            buttons: [
                {
                    text: '<?=$translator->_('ОК')?>',
                    scope: this,
                    handler: function() { this.saveGroup(); }
                },{
                    text: '<?=$translator->_('Отмена')?>',
                    scope: this,
                    handler: function() { this.propWin.hide(); }
                }
            ]
        });     
                    
        this.callParent();
        
        this.on({
            'celldblclick' : function() {
                this.edit();
            },
            'beforedestroy': function() {
                this.propWin.close();
            },
            scope: this
        });
        
        this.reload();
    },
    
    border: false,
    loadMask: true,
    stripeRows: true,
    
    columns: [
        {dataIndex: 'user_defined', hidden: true},
        {sortable: false, header: "GID", width: 50, dataIndex: 'id'},
        {sortable: true, header: "<?=$translator->_('Имя')?>", width: 300, dataIndex: 'name'},
        {header: "<?=$translator->_('Описание')?>", flex:1, dataIndex: 'describ'}
    ],
    
    features: [{
        id: 'group',
        ftype: 'grouping',
        groupHeaderTpl: '<tpl if="name == 0"><?=$translator->_('Встроенные группы')?></tpl><tpl if="name == 1"><?=$translator->_('Пользовательские группы')?></tpl>',
        hideGroupedHeader: true,
        enableGroupingMenu: false
    }],

    selModel: {
        mode: 'SINGLE',
        listeners: {
            'selectionchange' : {
                fn: function(sm) {
                    var hs = sm.hasSelection();
                    Ext.getCmp('tb_group_delete').setDisabled(!hs || !sm.getSelection()[0].get('user_defined'));
                    Ext.getCmp('tb_group_props').setDisabled(!hs);
                },
                scope: this
            }
        }
    },
    
    store: Ext.create('Ext.data.Store', {
        groupField:'user_defined',
        autoDestroy: true,
        remoteSort: true,
        fields: ['id','name','describ','user_defined'],
        sorters: [{property: "name", direction: "ASC"}],
        totalProperty: 'total',
        
        proxy: {
            type: 'ajax',
            url: 'include/data_groups.php',
            simpleSortMode: true,
            reader: {
                type: 'json',
                rootProperty: 'rows',
				root: 'rows'
            }
        }
    }),
    

    reload: function() {
        this.store.load();
    },
    
    call: function(action) {
        Ext.Ajax.request({
            url: 'include/action_groups.php',
            params: { 
                action: action, 
                'id': this.getSelected()
            },
            scope: this,
            success: function(resp) {
                this.store.load();
            }
        });
    },
    
    getSelected: function() {
        return this.getSelectionModel().getSelection()[0].getId();
    },
    
    deleteGroup: function() {
        Ext.MessageBox.confirm('<?=$translator->_('Удаление группы')?>', '<?=$translator->_('Вы уверены')?>?', function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
    
    edit: function() {
        this.propForm.getForm().findField('name').setValue(this.getSelectionModel().getSelection()[0].get('name'));
        this.propForm.getForm().findField('describ').setValue(this.getSelectionModel().getSelection()[0].get('describ'));
        this.propWin.setTitle('<?=$translator->_('Свойства')?>: ' + this.getSelectionModel().getSelection()[0].get('name'));
        this.users.store.proxy.extraParams.gid = this.getSelected();
        this.users.removeAll();
        this.users.store.load();
        this.saveParams['id'] = this.getSelected();
        this.propWin.show();
    },
    
    newGroup: function() {
        this.propForm.getForm().findField('name').setValue('');
        this.propForm.getForm().findField('describ').setValue('');
        this.propWin.setTitle('<?=$translator->_('Новая группа')?>');
        this.users.store.proxy.extraParams.gid = 0;
        this.users.removeAll();
        this.users.store.load();
        this.saveParams['id'] = 0;
        this.propWin.show();
    },
    
    saveParams: {
        'action': 'save',
        'id': 0,
        'add[]': [],
        'remove[]': []
    },
    
    saveGroup: function() {     
        this.saveParams['add[]'] = [];
        for (var i in this.users.checked) if (!isNaN(parseInt(i))) this.saveParams['add[]'].push(i);
        this.saveParams['remove[]'] = [];
        for (var i in this.users.unchecked) if (!isNaN(parseInt(i))) this.saveParams['remove[]'].push(i);  
            
        this.propForm.getForm().submit({
            url:'include/action_groups.php', 
            params: this.saveParams,
            waitMsg:'<?=$translator->_('Подождите ...')?>',
            scope: this,
            success: function(form, action) {
                this.propWin.hide();
                this.store.load();
            }
        });
    }
});