// JavaScript source code
//Check that TwoD is includes
(function() {if (typeof dPoint == "undefined") throw  "Missing JavaScript include file TwoD.js" } ())

var canvasex = {
    def: function (n) {
        if (typeof n !== 'undefined')
            return true;
        return false;
    }
}

class CanvasEx {
    constructor(elementname,options) {
        this._canvas = document.getElementById(elementname);
        this._ctx = this._canvas.getContext("2d");
        this._pTranslate = new dPoint(0, 0);
        this._scale = 1.0;
        this._flipY = false;
        if (options !== undefined) {
            if (canvasex.def(options.flipY) && options.flipY)
                this._flipY = true;
            if (canvasex.def(options.scale))
            this._scale = parseFloat(options.scale);
        }
    }


    background(c) {
        this._ctx.fillStyle = c;
        this._ctx.fillRect(0, 0, this._canvas.width - 1, this._canvas.height - 1);
    }

    setTranslate(x, y) {
        if (x !== undefined) {
            var x1 = 0;
            var y1 = 0;
            if (y === undefined) {
                if (x.constructor.name == "dPoint") {
                    x1 = x.x;
                    y1 = x.y;
                }
                else {
                    if (x == "middle") {
                        x1 = parseInt(this._canvas.width / 2);
                        y1 = parseInt(this._canvas.height / 2);
                    }
                else
                    if (x == "bottom") {
                        x1 = parseInt(this._canvas.width / 2);
                        y1 = parseInt(this._canvas.height);
                    }
                else
                    if (x == "bottomthird") {
                        x1 = parseInt(this._canvas.width / 2);
                        y1 = parseInt(this._canvas.height * 2 / 3);
                    }
                }
            }
            else {
                x1 = x;
                y1 = y;
            }
            //this._ctx.translate(x1, y1);
            this._pTranslate = new dPoint(x1, y1);
        }
    }

    translateAndScale(p1) {
        p1.scale(this._scale);
        p1.offset(this._pTranslate.x, this._pTranslate.y);
        return new dPoint(p1);
    }

    setScale(d) {
        this._scale = d;
    }

    flipY() {
        this._flipY = true;
    }

    screenToReal(x, y) {
        var y = (y - this._pTranslate.y) / this._scale;
        if (this._flipY)
            y = -y;
        return new dPoint((x - this._pTranslate.x) / this._scale, y);
    }

    line(x1, y1, x2, y2, co) {
        var p1, p2 = null;
        if (y2 === undefined) {

        }
        else {
            p1 = new dPoint(x1, y1);
            p2 = new dPoint(x2, y2);
        }
        if (co !== undefined) {
            this._ctx.strokeStyle = co;
        }

        if (this._flipY) {
            p1 = new dPoint(p1.x, -p1.y);
            p2 = new dPoint(p2.x, -p2.y);
        }

        //Translate
        p1 = this.translateAndScale(p1);
        p2 = this.translateAndScale(p2);

        this._ctx.beginPath();
        this._ctx.moveTo(p1.x, p1.y);
        this._ctx.lineTo(p2.x, p2.y);
        this._ctx.stroke();
    }

    dot(x1, y1, r, co) {
        if (co !== undefined) {
            this._ctx.fillStyle = co;
        }
        var p1 = new dPoint(x1, y1);

        if (this._flipY) {
            p1 = new dPoint(p1.x, -p1.y);
        }

        p1 = this.translateAndScale(p1);
        this._ctx.beginPath();
        this._ctx.arc(p1.x, p1.y, r, 0, Math.PI * 2);
        this._ctx.fill();
    }
}