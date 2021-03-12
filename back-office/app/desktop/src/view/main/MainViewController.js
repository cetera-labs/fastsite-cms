Ext.define('Cetera.view.main.MainViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.mainviewcontroller',

    mainRoute:function(xtype) {
      
        console.log(xtype);
          
        //var menuview = this.lookup('menuview');
        var navview = this.lookup('navview');
        var menuview = navview.items.items[0]
        var centerview = this.lookup('centerview');
        var exists = Ext.ClassManager.getByAlias('widget.' + xtype);
        if (exists === undefined) {
          console.log(xtype + ' does not exist');
          return;
        }
        var node = menuview.getStore().findNode('xtype', xtype);
        if (node == null) {
          console.log('unmatchedRoute: ' + xtype);
          return;
        }
        if (!centerview.getComponent(xtype)) {
          centerview.add({ xtype: xtype,  itemId: xtype, heading: node.get('text') });
        }
        centerview.setActiveItem(xtype);
        menuview.setSelection(node);
        var vm = this.getViewModel(); 
        vm.set('heading', node.get('text'));
    },

    onMenuViewSelectionChange: function (tree, node) {
        if (node == null) { return }
        var vm = this.getViewModel();
        if (node.get('xtype') != undefined) {
          this.redirectTo( node.get('xtype') );
        }
    },

    onHeaderViewNavToggle: function () {
        var vm = this.getViewModel();
        vm.set('navCollapsed', !vm.get('navCollapsed'));
        //var topPic = this.lookup('topPic');
        var topPic = Ext.getCmp('topPic');
        if (vm.get('navCollapsed') == true) {
          topPic.setData({ src:'resources/desktop/5.jpg', caption:'John Smith', imgStyle: 'imgSmall', height: '100px' });
        }
        else {
          topPic.setData({ src:'resources/desktop/5.jpg', caption:'John Smith', imgStyle: 'imgBig', height: '150px' });
        }

    },

    onHeaderViewDetailToggle: function () {
        var vm = this.getViewModel();
        vm.set('detailCollapsed', !vm.get('detailCollapsed'));
        var detailtoggle = this.lookup('detailtoggle');
        if(vm.get('detailCollapsed') === true) {
          //detailtoggle.setType('prev')
          detailtoggle.setIconCls('x-fa fa-arrow-left')
        }
        else {
          //detailtoggle.setType('next')
          detailtoggle.setIconCls('x-fa fa-arrow-right')
        }
    }

});