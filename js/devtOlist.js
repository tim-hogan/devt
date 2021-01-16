// JavaScript source code
var devtO = {
    def: function (n) {
        if (typeof n !== 'undefined')
            return true;
        return false;
    },
    ge: function (s) {
        return document.getElementById(s);
    },
    ce: function (w) {
        return document.createElement(w);
    },
    ac: function (n, p) {
        p.appendChild(n);
    }
}

class devtOTableRow {

}

class devtOTable {
    constructor(parent, id, className) {
        var t = devtO.ce("table");
        this._node = t;
        if (devtO.def(id))
            t.id = id;
        if (devtO.def(className))
            t.className = className;
        devtO.ac(t, parent);
    }
    get node() {
        return this._node;
    }

    addRow() {
        var r = new devtOTableRow(this._node);
        devtO.ac(r, parent);
        return r.node;
    }
}


class devtOlist {
    constructor(node, options) {
        this.first = 0;
        this.last = 0;
        this.node = devtO.ge(node);
        this.options = options;
        this.data = null;
        try {
            if (!devtO.def(apiJSON))
                throw new Error('Module apiClass not loaded: Inlcude the apiCLass2.js file');
        }
        catch {
            throw new Error('Module apiClass not loaded: Inlcude the apiCLass2.js file');
        }

        if (!devtO.def(options))
            throw new Error('Required options parameter not specified');
        if (!devtO.def(options.host))
            throw new Error('Required options host parameter not specified');
        if (!devtO.def(options.apiName))
            throw new Error('Required options base parameter not specified');
        if (!devtO.def(options.key))
            throw new Error('Required options key parameter not specified');
        if (!devtO.def(options.https))
            throw new Error('Required options https parameter not specified');

        this.api = new apiJSON(options.host, options.apiName, options.key, options.https);
        this.api.parseReply = this.revcData;
        this.getData();
        
    }

    getData() {
        return true;
    }

    recvData(d) {
        this.data = d;
        this.render();
    }

    render() {
        this.tb = new devtOTable(this.node);
        console.log("render from  super");
    }

}