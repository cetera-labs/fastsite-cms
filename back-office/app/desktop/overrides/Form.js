Ext.define('Cetera.override.Form', {
    
    override: 'Ext.form.Basic',
    
	findInvalid: function() {
		var me = this,
			invalid;
		Ext.suspendLayouts();
		invalid = me.getFields().filterBy(function(field) {
			var preventMark = field.preventMark, isValid;
			field.preventMark = true;
			isValid = field.isValid() && !field.hasActiveError();
			field.preventMark = preventMark;
			return !isValid;
		});
		
		Ext.resumeLayouts(true);
		return invalid;
	}
});