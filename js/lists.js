// JavaScript source code
var devtO = {
    def: function (n) {
        if (typeof n !== 'undefined')
            return true;
        return false;
    }
}
class devtOlist {
    constructor(node,options) {
        this.first = 0;
        this.last = 0;
        this.node = node;
        this.options = options;
        this.data = null;
        if (!devtO.def(apiJSON))
            throw new Error('Module apiClass not loaded: Inlcude the apiCLass2.js file');
        if (!devtO.def(options))
            throw new Error('Required options parameter not specified');
        if (!devtO.def(options.host) || !devtO.def(options.base) || !devtO.def(options.key) || !devtO.def(options.https))
            throw new Error('Required options parameters not specified');

        this.api = new apiJSON(options.host, options.base, options.key, options.https);
        this.api.parseReply = this.revcData;
        this.getData();
    }

    getData() {
        return 
    }

    recvData(d) {
        this.data = d;
        this.render();
    }

    render() {
        console.log("render from  super");
    }

}