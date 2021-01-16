// JavaScript source code
var th22 = {
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
    },
    cea(w, p) {
        var n = th22.ce(w);
        p.appendChild(n);
        return n;
    },
    sa: function (n, a, v) {
        n.setAttribute(a, v);
    },
    removeAllChildren: function (n) {
        while (n.firstChild) {
            n.removeChild(n.firstChild);
        }
    },
    isWholeNum: function (n) {
        return ((n - Math.floor(n)) === 0);
    },
}

class th22DOM {
    constructor(type, parent, id, className,inner) {
        var t = th22.ce(type);
        t.th22class = this;
        this._node = t;
        if (th22.def(id) && id.length > 0)
            t.id = id;
        if (th22.def(className) && className.length > 0)
            t.className = className;
        if (th22.def(parent.parentNode))
            th22.ac(t, parent);
        else
            th22.ac(t, parent.node);
        if (th22.def(inner) && inner.length > 0) {
            this._node.innerHTML = inner;
        }
    }

    get node() {
        return this._node;
    }

    setAttribute(n, v) {
        this._node.setAttribute(n, v);
    }

    setText(t) {
        this._node.innerHTML = t;
    }
}

class th22Div extends th22DOM {
    constructor(parent, id, className,inner) {
        super("div", parent, id, className,inner);
    }
}

class th22Para extends th22DOM {
    constructor(parent, id, className,inner) {
        super("p", parent, id, className,inner);
    }
}

class th22Span extends th22DOM {
    constructor(parent, id, className,inner) {
        super("span", parent, id, className,inner);
    }
}

class th22Button extends th22DOM {
    constructor(parent, id, className,inner) {
        super("button", parent, id, className,inner);
    }

    setGoTo(w) {
        this.setAttribute("onclick", "window.location='" + w + "'");
    }
}

class th22Input extends th22DOM {
    constructor(parent, id, className,type,size) {
        super("input", parent, id, className);
        if (th22.def(type))
            th22.sa(this._node, "type", type);
        if (th22.def(size))
            th22.sa(this._node, "size", size);
    }
    setValue(v) {
        this._node.value = v;
    }

    setMin(v) {
        this.minimum = v;
    }

    setMax(v) {
        this.maximum = v;
    }

    setMinMax(m, n) {
        this.setMin(m);
        this.setMax(n);
    }

    verifyIntegerRange() {
        if (th22.def(this.th22class.minimum) && th22.def(this.th22class.maximum)) {
            this.className = '';
            var v = this.value;
            if (v.length > 0) {
                v = parseInt(v);
                if (v < this.th22class.minimum || v > this.th22class.maximum)
                    this.className = 'th22InpErr';
            }
            else
                this.className = '';
        }
    }

    setVerifyIntegerRange() {
        this._node.addEventListener("keyup", this.verifyIntegerRange);
    }

}

class th22TableHdrCol extends th22DOM {
    constructor(parent, id, className,inner) {
        super("th", parent, id, className,inner);
    }
}

class th22TableCol extends th22DOM {
    constructor(parent, id, className,inner) {
        super("td", parent, id, className,inner);
    }
}

class th22TableRow extends th22DOM {
    constructor(parent, id, className) {
        super("tr", parent, id, className);
    }

    addHdrCol(title,className,colspan) {
        var r = new th22TableHdrCol(this._node);
        if (th22.def(title) && title.length > 0)
            r.node.innerHTML = title;
        if (th22.def(className))
            r.node.className = className;
        if (th22.def(colspan))
            r.setAttribute("colspan", colspan);
        return r;
    }

    addCol(title,className,colspan) {
        var r = new th22TableCol(this._node);
        if (th22.def(title) && title.length > 0)
            r.node.innerHTML = title;
        if (th22.def(className))
            r.node.className = className;
        if (th22.def(colspan))
            r.setAttribute("colspan", colspan);
        return r;
    }
}


class th22Table extends th22DOM {
    constructor(parent, id, className) {
        super("table", parent, id, className);
    }

    addRow() {
        return new th22TableRow(this._node);
    }
}


class th22list {

    constructor(node, options) {
        this.first = 0;
        this.last = 0;
        this.node = th22.ge(node);
        this.options = options;
        this.schema = null;
        this.data = null;
        this.listners = {};

        try {
            if (!th22.def(apiJSON))
                throw new Error('Module apiClass not loaded: Inlcude the apiCLass2.js file');
        }
        catch {
            throw new Error('Module apiClass not loaded: Inlcude the apiCLass2.js file');
        }

        if (!th22.def(options))
            throw new Error('Required options parameter not specified');
        if (!th22.def(options.host))
            throw new Error('Required options host parameter not specified');
        if (!th22.def(options.apiName))
            throw new Error('Required options base parameter not specified');
        if (!th22.def(options.key))
            throw new Error('Required options key parameter not specified');
        if (!th22.def(options.https))
            throw new Error('Required options https parameter not specified');

        this.api = new apiJSON(options.host, options.apiName, options.key, options.https);
        this.api.parent = this;
        this.api.parseReply = function (d) {
            console.log("receved reply back from server");
            if (d.meta.status) {
                if (d.meta.request == "dataset") {
                    switch (d.meta.subrequest) {
                        case "schema":
                            this.parent.schema = d.data;
                            if (th22.def(this.parent.listners.schema))
                                this.parent.listners.schema(d.data);
                            break;
                        case "records":
                            this.parent.data = d.data;
                            if (th22.def(this.parent.listners.records))
                                this.parent.listners.records(d.data);
                            break;
                    }
                }
            }
            else {
                if (th22.def(this.parent.listners.error))
                    this.parent.listners.error(d.meta);
                console.log("Error returned from api call");
            }
        }
    }

    addEventListner(type,f) {
        this.listners[type] = f;
    }

    getSchema() {
        this.schema = null;
        this.data = null;
        this.api.queueReq('GET', "dataset/" + this.dataset + "/schema", null);
    }

    getRecords(from,to) {
        this.data = null;
        var v = "dataset/" + this.dataset + "/records";
        if (th22.def(from)) {
            v += ("/" + from);
            if (th22.def(to)) {
                v += ("/" + to);
            }
        }
        this.api.queueReq('GET',v,null);
    }

    start() {
        this.getSchema();
        this.getRecords();
    }

    next() {
        if ((this.listparent.data.to + 1) < this.listparent.data.total) {
            this.listparent.getRecords(this.listparent.data.to + 1);
        }
    }

    prev() {
        var start = this.listparent.data.from - this.listparent.schema.list.default_page_count;
        if (start < 0)
            start = 0;
        this.listparent.getRecords(start);
    }

    render() {
        console.log("Render from  super");
        th22.removeAllChildren(this.node);

        if (this.schema) {
            var l = this.schema.list;
            var cols = l.columns;

            //Create first div for record numbers
            var div1 = new th22Div(this.node, "th22Div1");


            var div11 = new th22Div(div1, "th22Div11");
            var span1 = new th22Span(div11);
            span1.setText((this.data.from + 1) + " to " + (this.data.to + 1) + " of " + this.data.total); 

            var div12 = new th22Div(div1, "th22Div12");
            span1 = new th22Span(div12);
            span1.setText("RECORD");

            var imp = new th22Input(div12, "thInp10", "", "text", 2);
            imp.setValue(this.data.from + 1);
            imp.setMinMax(1,this.data.total);
            imp.setVerifyIntegerRange();

            var divprev = new th22Div(div12, "th22DivPrev","th22prevnext");
            divprev.node.listparent = this;
            divprev.node.addEventListener("click", this.prev);

            var divnext = new th22Div(div12, "th22DivNext", "th22prevnext");
            divnext.node.listparent = this;
            divnext.node.addEventListener("click", this.next);

            var divclear = new th22Div(div1, "th22DivClear13");

            div1 = new th22Div(this.node, "th22Div20");

            //Create table
            this.tb = new th22Table(div1);

            //Create header row
            var r = this.tb.addRow();
            if (this.schema.list.record_checkbox) {
                r.addHdrCol();
            }
            for (var idx = 0; idx < cols.length; idx++) {
                r.addHdrCol(cols[idx].title);
            }

            if (!this.data || this.data.rows.length == 0) {
                var r = this.tb.addRow();
                r.addCol("NO RECORDS", "", cols.length);
            }
            else {
                for (var idx = 0; idx < this.data.rows.length; idx++) {
                    r = this.tb.addRow();
                    if (this.schema.list.record_checkbox) {
                        var c = r.addCol("","th22ColCheck");
                        var cb = new th22Input(c, "", "", "checkbox");
                    }
                    var row = this.data.rows[idx];
                    for (var col = 0; col < row.length; col++) {
                        r.addCol(row[col]);
                    }
                }
            }
        }
    }
}