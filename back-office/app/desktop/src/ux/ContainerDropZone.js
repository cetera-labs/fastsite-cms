Ext.define('Cetera.ux.ContainerDropZone', {
    extend: 'Ext.dd.DropTarget',

    constructor: function(container, cfg) {
        this.container = container;
        Ext.dd.ScrollManager.register(container.body);
        this.callParent([container.body, cfg]);
        container.body.ddScrollConfig = this.ddScrollConfig;
    },

    ddScrollConfig: {
        vthresh: 50,
        hthresh: -1,
        animate: true,
        increment: 200
    },

    createEvent: function(dd, e, data, pos) {
        return {
            container: this.container,
            panel: data.panel,
            position: pos,
            data: data,
            source: dd,
            rawEvent: e,
            status: this.dropAllowed
        };
    },

    notifyOver: function(dd, e, data) {

        var xy = e.getXY(),
            container = this.container,
            proxy = dd.proxy;
            
        // handle case scroll where scrollbars appear during drag
        var ch = container.body.dom.clientHeight;
        if (!this.lastCH) {
            // set initial client width
            this.lastCH = ch;
        } else if (this.lastCH != ch) {
            // client width has changed, so refresh layout & grid calcs
            this.lastCH = ch;
            container.doLayout();
        }

        // find insert position
        var overPortlet, pos = 0,
            h = 0,
            match = false,
            portlets = container.items.items,
            overSelf = false;

        len = portlets.length;

        for (len; pos < len; pos++) {            
            overPortlet = portlets[pos];
            h = overPortlet.el.getHeight();
            if (h === 0) {
                overSelf = true;
            } else if ((overPortlet.el.getY() + (h / 2)) > xy[1]) {
                match = true;
                break;
            } 
        }

        pos = (match && overPortlet ? pos : container.items.getCount());// + (overSelf ? -1 : 0);
        
        var overEvent = this.createEvent(dd, e, data, pos);

        if (container.fireEvent('validatedrop', overEvent) !== false && container.fireEvent('beforedragover', overEvent) !== false) {
        
            // make sure proxy width is fluid in different width columns
            proxy.getProxy().setWidth(100);
            if (overPortlet) {
                dd.panelProxy.moveProxy(overPortlet.el.dom.parentNode, match ? overPortlet.el.dom : null);
            } else {
                dd.panelProxy.moveProxy(container.el.dom, null);
            }

            this.lastPos = overSelf || (match && overPortlet) ? pos : null;

            this.scrollPos = container.body.getScroll();

            container.fireEvent('dragover', overEvent);
            return overEvent.status;
        } else {
            return overEvent.status;
        }

    },

    notifyDrop: function(dd, e, data) {

        if (this.lastPos === null) {
            return;
        }
        var pos = this.lastPos,
            panel = dd.panel,
            dropEvent = this.createEvent(dd, e, data, pos !== false ? pos : this.container.items.getCount());

        if (this.container.fireEvent('validatedrop', dropEvent) !== false && 
            this.container.fireEvent('beforedrop', dropEvent) !== false) {

            Ext.suspendLayouts();
            
            // make sure panel is visible prior to inserting so that the layout doesn't ignore it
            panel.el.dom.style.display = '';
            dd.panelProxy.hide();
            dd.proxy.hide();

            if (pos !== false) {
                this.container.insert(pos, panel);
            } else {
                this.container.add(panel);
            }

            Ext.resumeLayouts(true);

            this.container.fireEvent('drop', dropEvent);

            // scroll position is lost on drop, fix it
            var sl = this.scrollPos.left;
            if (sl) {
                var d = this.container.body.dom;
                setTimeout(function() {
                    d.scrollLeft = sl;
                },
                10);
            }
        }
        
        delete this.lastPos;
        return true;
    },

    // unregister the dropzone from ScrollManager
    unreg: function() {
        Ext.dd.ScrollManager.unregister(this.container.body);
        this.callParent(arguments);
    }
});