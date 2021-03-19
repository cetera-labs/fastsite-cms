Ext.Ajax.on('requestexception', function(conn, resp, opt) {

    if (opt.failure) return;

    try {
        var obj = Ext.decode(resp.responseText);
    } catch (e) {
        obj = false;
    }
    
    var msg = Config.Lang.requestException;
    var ext_message = null;//'url: '+opt.url+'<br>method: '+opt.method+'<br>params: '+Ext.Object.toQueryString(opt.params, true);
    
    if (obj) if (obj.message) msg = obj.message;
        
    if (msg) {
        
        var win = Ext.create('Cetera.window.Error', {
            msg: msg,
            ext_msg: obj?obj.ext_message:ext_message
        });
        win.show();

    }
});

Ext.DomQuery.pseudos.scrollable = function(c, t) {
    var r = [], ri = -1;
    for(var i = 0, ci; ci = c[i]; i++){
        var o = ci.style.overflow;
        if(o=='auto'||o=='scroll') {
            //if (ci.scrollHeight < Ext.fly(ci).getHeight(true)) 
				r[++ri] = ci;
        }
    }
    return r;
};

Ext.application({
	extend: 'Cetera.Application',
	name: 'Cetera'
});