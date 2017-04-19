Ext.define('Cetera.view.ModalLoadingAnimation', {
    alias: 'widget.loading',

    msgCt: undefined,
    loadingBox: undefined,

    constructor: function () {
        var html='', me = this;

        if(!me.msgCt){
            me.msgCt = Ext.core.DomHelper.insertFirst(document.body, {id:'loading-div'}, true);
        }

        Ext.core.DomHelper.applyStyles(me.msgCt, {
            top:  ( window.innerHeight - 70) / 2 + 'px',
            left: ( window.innerWidth - 70) / 2  + 'px'
        });
        
        html += '<div class="outer-circle"></div><div class="inner-circle"></div><div class="loading-text">exploring</div>';
        me.loadingBox = Ext.core.DomHelper.append(me.msgCt, html, true);
        me.msgCt.hide();
    },

    show: function () {
        this.msgCt.show();
    },

    hide: function () {
        this.msgCt.hide();
    }
});