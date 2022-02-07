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

    setPixel(x, y, co) {
        let p1 = new dPoint(x, y);
        if (this._flipY) 
            p1 = new dPoint(p1.x, -p1.y);
        p1 = this.translateAndScale(p1);
        this._ctx.fillStyle = co;
        this._ctx.fillRect(Math.round(p1.x), Math.round(p1.y), 1, 1);

    }

    plane(v1, v2, v3, co) {
        var p1, p2, p3;
        var strColour;

        if (ThreeD.def(co))
            strColour = co;

        if (v1.constructor.name == "Plane3D") {
            p1 = new dPoint(v1.v1.x, v1.v1.y);
            p2 = new dPoint(v1.v2.x, v1.v2.y);
            p3 = new dPoint(v1.v3.x, v1.v3.y);
            strColour = v1.shadecolour;
        }
        else {
            if (v1.constructor.name == "dPoint") {
                p1 = v1;
            }
            if (v1.constructor.name == "Vector3D") {
                p1 = new dPoint(v1.x, v1.y);
            }
            if (v2.constructor.name == "dPoint") {
                p2 = v2;
            }
            if (v2.constructor.name == "Vector3D") {
                p2 = new dPoint(v2.x, v2.y);
            }
            if (v3.constructor.name == "dPoint") {
                p3 = v3;
            }
            if (v3.constructor.name == "Vector3D") {
                p3 = new dPoint(v3.x, v3.y);
            }
        }

        if (this._flipY) {
            p1 = new dPoint(p1.x, -p1.y);
            p2 = new dPoint(p2.x, -p2.y);
            p3 = new dPoint(p3.x, -p3.y);
        }

        p1 = this.translateAndScale(p1);
        p2 = this.translateAndScale(p2);
        p3 = this.translateAndScale(p3);

        this._ctx.fillStyle = strColour;
        this._ctx.beginPath();
        this._ctx.moveTo(p1.x, p1.y);
        this._ctx.lineTo(p2.x, p2.y);
        this._ctx.lineTo(p3.x, p3.y);
        this._ctx.closePath();
        this._ctx.fill(); 

    }

    strokePlane(plane, co) {
        this.line(plane.v1.x, plane.v1.y, plane.v2.x, plane.v2.y, co);
        this.line(plane.v2.x, plane.v2.y, plane.v3.x, plane.v3.y, co);
        this.line(plane.v3.x, plane.v3.y, plane.v1.x, plane.v1.y, co);
    }

    fillPlane(plane) {

        if (plane.pointsValid()) {
            let p1 = new dPoint(plane.v1.x, plane.v1.y);
            let p2 = new dPoint(plane.v2.x, plane.v2.y);
            let p3 = new dPoint(plane.v3.x, plane.v3.y);

            if (this._flipY) {
                p1 = new dPoint(p1.x, -p1.y);
                p2 = new dPoint(p2.x, -p2.y);
                p3 = new dPoint(p3.x, -p3.y);
            }

            p1 = this.translateAndScale(p1);
            p2 = this.translateAndScale(p2);
            p3 = this.translateAndScale(p3);

            this._ctx.fillStyle = plane.shadecolour;
            //console.log("fillPlane: [" + p1.x + "," + p1.y + "] [" + p2.x + "," + p2.y + "] [" + p3.x + "," + p3.y + "]" + plane.shadecolour);
            this._ctx.beginPath();
            this._ctx.moveTo(p1.x, p1.y);
            this._ctx.lineTo(p2.x, p2.y);
            this._ctx.lineTo(p3.x, p3.y);
            this._ctx.closePath();
            this._ctx.fill();
        }
    }


    strokeQuad(quad,co) {
        this.line(quad.points[0].x, quad.points[0].y, quad.points[1].x, quad.points[1].y, co);
        this.line(quad.points[1].x, quad.points[1].y, quad.points[2].x, quad.points[2].y, co);
        this.line(quad.points[2].x, quad.points[2].y, quad.points[3].x, quad.points[3].y, co);
        this.line(quad.points[3].x, quad.points[3].y, quad.points[0].x, quad.points[0].y, co);
    }

    fillQuad(quad) {

        if (quad.pl1.shadecolour == quad.pl2.shadecolour) {
            if (quad.pointsValid()) {
                let p1 = new dPoint(quad.points[0].x, quad.points[0].y);
                let p2 = new dPoint(quad.points[1].x, quad.points[1].y);
                let p3 = new dPoint(quad.points[2].x, quad.points[2].y);
                let p4 = new dPoint(quad.points[3].x, quad.points[3].y);

                if (this._flipY) {
                    p1 = new dPoint(p1.x, -p1.y);
                    p2 = new dPoint(p2.x, -p2.y);
                    p3 = new dPoint(p3.x, -p3.y);
                    p4 = new dPoint(p4.x, -p4.y);
                }

                p1 = this.translateAndScale(p1);
                p2 = this.translateAndScale(p2);
                p3 = this.translateAndScale(p3);
                p4 = this.translateAndScale(p4);

                this._ctx.fillStyle = quad.pl1.shadecolour;
                this._ctx.beginPath();
                this._ctx.moveTo(p1.x, p1.y);
                this._ctx.lineTo(p2.x, p2.y);
                this._ctx.lineTo(p3.x, p3.y);
                this._ctx.lineTo(p4.x, p4.y);
                this._ctx.closePath();
                this._ctx.fill();
            }
        }
        else {
            this.fillPlane(quad.pl1);
            this.fillPlane(quad.pl2);
        }

    }

    drawPlane(plane, option) {
        if (typeof options.fill !== "undefined" && options.fill) {
            this.fillPlane(plane);
        }
        if (typeof options.wireframe !== "undefined" && options.wireframe) {
            let co = "#ffffff";
            if (typeof options.colour !== "undefined")
                co = options.colour;
            this.strokePlane(plane, co);
        }
    }

    drawQuad(quad, options) {
        if (typeof options.fill !== "undefined" && options.fill) {
            this.fillQuad(quad);
        }
        if (typeof options.wireframe !== "undefined" && options.wireframe) {
            let co = "#ffffff";
            if (typeof options.colour !== "undefined")
                co = options.colour;
            this.strokeQuad(quad,co);
        }

    }
}