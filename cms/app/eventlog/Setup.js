Ext.define('Cetera.eventlog.Setup', {

    extend:'Ext.Window',    
          
    initComponent: function(){
		
        this.eventsPanel = Ext.create('Ext.grid.GridPanel', {
            enableHdMenu     : false,
            enableColumnMove : false,
            enableColumnResize: false,
            
            store: new Ext.data.JsonStore({
                autoDestroy: true,
                autoLoad: true,
                fields: ['id','name','log'],
                proxy: {
                    type: 'ajax',
                    api: {
                        read    : 'include/data_events.php',
                        update  : 'include/data_events.php'
                    },                   
                    reader: {
                        type: 'json',
                        root: 'rows'
                    },
                    writer: {
                        type: 'json',
                        writeAllFields: true,
                        root: 'rows',
                        encode: true
                    }                   
                },
				listeners: {
					scope: this,
					'datachanged': function() {
						this.isDirty = false;
						this.setLoading( false );
					}
				}				
            }),
            
            columns: [
          		{
                  header: _('Событие'), 
                  flex: 1,
                  dataIndex: 'name'
              },{
                  xtype: 'checkcolumn',
                  header: _('Журналировать'),
                  dataIndex: 'log',
                  width: 100
              }
          	]
        }); 

        this.applyButton = Ext.create('Ext.Button', {
            text: _('Применить'),
            scope: this,
            handler: function(){
				this.setLoading( true );
				this.eventsPanel.store.update();
			}
        }); 		
    
        Ext.apply(this, {
            title: _('Настройка журнала'),
            autoHeight: true,
            autoShow: true,
            modal: true,
            width: 600,
			height: '80%',
            closable: true,
            resizable: false,
            bodyPadding: 10,
			layout: 'fit',
			items: [this.eventsPanel],
			buttons: [this.applyButton]
        });
        
        this.callParent(arguments);
    }
});