// nolink - не выбирать разделы-ссылки
// from - начинать с ветки $from
// rule - разрешение, в соответствии с которым запрещать доступ к разделу
// only - разрешать только разделы типа $only
// exclude - исключить ветку с id = $exclude
// initPath - выбрать раздел
// nocatselect -
// norootselect - 
// materials -   
Ext.define('Cetera.field.Folder', {

    extend: 'Cetera.field.Trigger',
    
    alias: 'widget.folderfield',
	displayValue: '',
    
    initComponent : function(){
    
        this.trigger1Cls = 'icon-delete';
        this.trigger2Cls = 'icon-folder';
        this.editable    = false;  
		
		if (this.mat_type) this.url = '/cms/include/data_tree_materials_by_type.php?type='+this.mat_type;
    
        this.window = Ext.create('Cetera.window.SiteTree', {
            exclude: this.exclude,
            nolink : this.nolink,
            from   : this.from,
            rule   : this.rule,
            only   : this.only,
            path   : this.path,
			url    : this.url,
            materials   : this.materials,
            nocatselect : this.nocatselect,
            norootselect: this.norootselect,
            matsort     : this.matsort,
            exclude_mat : this.exclude_mat
        });
        
        this.window.on('select', function(res) {
            this.path = res.path;    
            this.setDisplayValue(res.name_to);
            if (this.structureValue) {
                this.setValue(res.structure_id, true);  
            }
            else {
                this.setValue(res.id, true);  
            }
                
            this.fireEvent('select', res);
        }, this); 
    
        this.callParent();
    },
	
	getDisplayValue: function(){		
		return this.displayValue;
	},
	
	setDisplayValue: function(value){
		this.displayValue = value;
		this.callParent([value]);		
	},	
        
    setValue : function(value, displayOK, mat_type) {
		
		if (!mat_type) {
			if (this.mat_type) {
				mat_type = this.mat_type;
			}
			else if (this.only) {
				mat_type = this.only;
			}
		}

		var obj = Ext.JSON.decode(value, true);
        if (obj instanceof Object) {
        
            this.setDisplayValue(obj.name);
            this.callParent([obj.id]);
            
        } else {

            if (!displayOK && value && !this.materials)
			{
                Ext.Ajax.request({
                    url: '/cms/include/action_catalog.php?action=get_path_info&id='+value,
                    success: function(response){
                        var obj = Ext.decode(response.responseText);
                        this.setDisplayValue(obj.displayPath);
                        this.path = obj.treePath;
                        this.window.path = obj.treePath;
                    },
                    scope: this
                });        
            }
			else if (!displayOK && value && this.materials && mat_type)
			{
                Ext.Ajax.request({
                    url: '/cms/include/action_materials.php?action=get_path_info&mat_id='+value+'&type='+mat_type,
                    success: function(response){
                        var obj = Ext.decode(response.responseText);
                        this.setDisplayValue(obj.displayPath);
                    },
                    scope: this
                });  				
			}
			
            this.callParent([value]);

        }        
    
    },
	
	setOnly : function(value) {
		this.onTrigger1Click();
		this.only = value;
		this.window.setOnly(value);
	},

    onTrigger2Click: function() {
        this.window.show();
    },
    
    onTrigger1Click: function() { 
        this.setDisplayValue('');
        this.setValue(0); 
    },
     
	  onDestroy: function(){
		    this.window.close();
        this.callParent();
	  }, 
    
    validator: function(value) {
        if (!this.allowBlank && !parseInt(value)) return this.blankText;
        return true;
    }
});