// JavaScript source code
class dPoint {
    constructor(x, y) {
        if (y === undefined && x.constructor.name == "dPoint") {
            this._x = x.x;
            this._y = x.y;
        }
        else {
            this._x = x;
            this._y = y;
        }
    }

    get x() {
        return this._x;
    }

    get y() {
        return this._y;
    }

    get vectorLength() {
        return Math.sqrt(this._x * this._x + this._y * this._y);
    }

    copy() {
        return new dPoint(this._x, this._y);
    }

    offset(x, y) {
        this._x += x;
        this._y += y;
    }

    scale(s) {
        this._x *= s;
        this._y *= s;
    }

    dot(p) {
        return (p.x * this._x) + (p.y * this._y);
    }

    rotate(a, p) {
        var s = Math.sin(a);
        var c = Math.cos(a);
        var nx = this._x - p.x;
        var ny = this._y - p.y;
        var x = c * nx - ny * s + p.x;
        var y = s * nx + ny * c + p.y;
        return new dPoint(x, y);
    }

}

class dPolar {
    constructor(r, t) {
        this._r = r;
        this._t = t;
    }
    get r() {
        return this._r;
    }

    get t() {
        return this._t;
    }

}

class dLine {
    constructor(p1, p2, p3, p4) {
        if (p2 === undefined && p1.constructor.name == "dLine") {
            this._p1 = p1.p1;
            this._p2 = p2.p2;

        }
        else
        if (p3 === undefined && p1.constructor.name == "dPoint") {
            this._p1 = p1;
            this._p2 = p2;
        }
        else {
            this._p1 = new dPoint(p1, p2);
            this._p2 = new dPoint(p3, p4);
            }
        if (this._p1.x != this._p2.x) {
            this._m = (this._p1.y - this._p2.y) / (this._p1.x - this._p2.x);
            this._c = this._p1.y - (this._m * this._p1.x);
        }
    }

    get m() {
        return this._m;
    }

    get c() {
        return this._c;
    }

    get p1() {
        return this._p1;
    }

    get p2() {
        return this._p2;
    }

    fn(x) {
        if (this._m === undefined)
            return undefined;
        return this._m * x + this._c;
    }

    get length() {
        var a = this._p2.x - this._p1.x;
        var b = this._p2.y - this._p1.y;
        return Math.sqrt(a * a + b * b); 
    }

    get normalise() {
        var a = this._p2.x - this._p1.x;
        var b = this._p2.y - this._p1.y;
        var l = Math.sqrt(a * a + b * b); 
        return new dPoint(a/l, b/l);
    }

    get polar() {
        var a = this._p2.x - this._p1.x;
        var b = this._p2.y - this._p1.y;
        var l = Math.sqrt(a * a + b * b); 
        return new dPolar(l, Math.atan2(b, a));
    }

    intersect(l) {
        if (l.m !== undefined && this.m !== undefined) {
            if (l.m == this.m) {
                return undefined;
            }
            var x = (l.c - this.c) / (this.m - l.m);
            var y = (this.m * x) + this.c;
            return new dPoint(x, y);
        }
        else {
            if (l.m === undefined && this.m !== undefined) {
                return new dPoint(l.p1.x, ((l.p1.x * this.m) + this.c));
            }
            else
                if (l.m !== undefined && this.m === undefined) {
                    return new dPoint(this.p1.x, ((this.p1.x * l.m) + l.c));
                }
                else
                    return undefined;
        }
        return undefined;
    }

    pointOnLine(p) {
        if (p !== undefined && p.constructor.name == "dPoint") {
            //Get min and max x and y
            var minx = Math.min(this._p1.x, this._p2.x);
            var maxx = Math.max(this._p1.x, this._p2.x);
            var miny = Math.min(this._p1.y, this._p2.y);
            var maxy = Math.max(this._p1.y, this._p2.y);
            if (this._m !== undefined) {
                var y = p.x * this._m + this._c;
                if (Math.abs(p.y - y) < 0.00000000001) {
                    if (p.x <= maxx && p.x >= minx && p.y <= maxy && p.y >= miny)
                        return true;
                }
            }
            else
                if (p.x == this._p1.x) {
                    var y1 = Math.min(this._p1.y,this._p2.y);
                    var y2 = Math.max(this._p1.y, this._p2.y);
                    if (p.y >= y1 && p.y <= y2)
                        return true;
                }
        }
        return false;
    }
}

class dPolygon {
    constructor(p) {
        this._points = [];
        if (p !== undefined)
            this._points = p;
    }

    addPoint(p) {
        this._points.push(p);
    }

    get count() {
        return this._points.length;
    }

    get area() {
        var l = this._points.length;
        var sum = 0.0;
        for (var i = 0; i < l;i++) {
            var j = (i + 1) % l;
            sum += (this._points[i].x * this._points[j].y) - (this._points[j].x * this._points[i].y); 
        }
        return sum / 2.0;
    }

    get centroid() {
        var l = this._points.length;
        var sumx = 0.0;
        var sumy = 0.0;
        for (var i = 0; i < l; i++) {
            var j = (i + 1) % l;
            sumx += (this._points[i].x + this._points[j].x) * ((this._points[i].x * this._points[j].y) - (this._points[j].x * this._points[i].y));
            sumy += (this._points[i].y + this._points[j].y) * ((this._points[i].x * this._points[j].y) - (this._points[j].x * this._points[i].y));
        }
        var a = 1 / (this.area * 6);
        
        return new dPoint(sumx * a, sumy * a);
    }
}