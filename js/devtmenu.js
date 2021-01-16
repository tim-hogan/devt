// JScript source code
var devtmenu = {
    version: 1.0,
    currentMenu: null,
    removeAll: function () {
        if (devtmenu.currentMenu) {
            devtmenu.currentMenu.parentElement.removeChild(devtmenu.currentMenu);
            devtmenu.currentMenu = null;
        }
    },
    menu: function (parntid, src) {
        this.parnt = devt.ge(parntid);
        if (devt.def(src))
            this.src = src;
        else
            this.src = null;
        this.div = null;
        this.items = [];
        this.additem = function (type, text, funcname, param, enabled) {
            var enbled = true;
            if (devt.def(enabled))
                enbled = enabled;
            var item = new devtmenu.menuitem(type, text, funcname, param, enbled);
            this.items.push(item);
        };
        this.build = function (l, t) {
            devtmenu.removeAll();
            this.div = devt.cea('DIV', this.parnt);
            devtmenu.currentMenu = this.div;
            this.div.className = 'menudivclass';
            this.div.style.top = t + 'px';
            this.div.style.left = l + 'px';
            var ul = devt.cea('UL', this.div);
            var list = this.items;
            for (var i = 0; i < list.length; i++) {
                var li = devt.cea('LI', ul);
                if (list[i].type == 'menusel') {
                    li.style.cursor = "context-menu";
                    if (list[i].enabled)
                        li.setAttribute('onclick', 'devtmenu.menuselect(this)');
                    else
                        li.className = 'greyed';
                    li.devtmi = list[i];
                    li.devtdiv = this.div;
                    li.devtsrc = this.src;
                    li.innerHTML = list[i].text;
                    li.undisplay = function () {
                        this.devtdiv.parentElement.removeChild(this.devtdiv);
                    }
                }
                if (list[i].type == 'separator')
                    li.style.borderBottom = 'solid 1px black';
            }
        };
    },
    menuitem: function (type, text, funcname, param, enabled) {
        if (devt.def(text))
            this.text = text;
        this.type = type;
        if (devt.def(funcname))
            this.funcname = funcname;
        if (devt.def(param))
            this.param = param;
        this.enabled = true;
        if (devt.def(enabled))
            this.enabled = enabled;

    },
    menuselect: function (n) {
        var mi = n.devtmi;
        if (devt.def(mi.funcname)) {
            if (devt.def(mi.param))
                window[mi.funcname](mi.param, n);
            else
                window[mi.funcname](null, n.devtmi);
        }
        //Remove the menu
        devtmenu.removeAll();
    }
}