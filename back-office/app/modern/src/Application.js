/**
 * The main application class. An instance of this class is created by app.js when it calls
 * Ext.application(). This is the ideal place to handle application launch and initialization
 * details.
 */
Ext.define('Cetera.Application', {
    extend: 'Ext.app.Application',

    name: 'Cetera',

    views: [
        'Cetera.view.main.Main'
    ],

    launch: function () {
		Ext.ariaWarn = Ext.emptyFn
		Ext.getBody().removeCls('launching')
		var elem = document.getElementById("splash")
		elem.parentNode.removeChild(elem)        
    }
});
