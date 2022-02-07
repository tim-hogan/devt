// JScript source code
var ThreeD = {
    def: function (x) {
        if (typeof x != 'undefined')
            return true;
        return false;
    },
    pad: function (num, size) {
        var s = num + "";
        while (s.length < size) s = "0" + s;
        return s;
    },
    Vector3D: function (x, y, z) {

        this.x = 0.0;
        this.y = 0.0;
        this.z = 0.0;

        if (ThreeD.def(x))
            this.x = x;
        if (ThreeD.def(y))
            this.y = y;
        if (ThreeD.def(z))
            this.z = z;

        this.add = function (vector) {
            return new ThreeD.Vector3D(this.x + vector.x, this.y + vector.y, this.z + vector.z);
        }

        this.subtract = function (vector) {
            return new ThreeD.Vector3D(this.x - vector.x, this.y - vector.y, this.z - vector.z);
        }

        this.scale = function (d) {
            return new ThreeD.Vector3D(this.x *d, this.y *d, this.z *d);
        },

        this.length = function () {
            return Math.sqrt(this.x * this.x + this.y * this.y + this.z * this.z);
        }

        this.lengthTo = function (vector) {
            var dx = this.x - vector.x;
            var dy = this.y - vector.y;
            var dz = this.z - vector.z;
            return Math.sqrt(dx * dx + dy * dy + dz * dz);
        }

        this.inverse = function () {
            return new ThreeD.Vector3D(-this.x,-this.y,-this.z);
        }

        this.dot = function (vector) {
            return (this.x * vector.x) + (this.y * vector.y) + (this.z * vector.z);
        }
        this.cross = function (vector) {
            var vout = new ThreeD.Vector3D(0.0, 0.0, 0.0);
            vout.x = this.y * vector.z - this.z * vector.y;
            vout.y = this.z * vector.x - this.x * vector.z;
            vout.z = this.x * vector.y - this.y * vector.x;
            return vout;
        }

        this.normalize = function () {
            var dl = this.length();
            if (dl > 0) {
                this.x = this.x / dl;
                this.y = this.y / dl;
                this.z = this.z / dl;
            }
        }

        this.dirTo = function (to) {
            let d = to.subtract(this);
            d.normalize();
            return d;
        }

        this.zzero = function (vector) {
            var f = (1.0 - this.z) / (vector.z - this.z);
            vout.x = this.x + ((vector.x - this.x) * f);
            vout.y = this.y + ((vector.y - this.y) * f);
            vout.z = 1.0;
            return vout;
        }

        this.applymatrix = function (matrix) {
            var vout = new ThreeD.Vector3D(0.0, 0.0, 0.0);
            vout.x = (this.x * matrix.data[0]) + (this.y * matrix.data[4]) + (this.z * matrix.data[8]) + matrix.data[12];
            vout.y = (this.x * matrix.data[1]) + (this.y * matrix.data[5]) + (this.z * matrix.data[9]) + matrix.data[13];
            vout.z = (this.x * matrix.data[2]) + (this.y * matrix.data[6]) + (this.z * matrix.data[10]) + matrix.data[14];
            return vout;
        }

        this.swapYZ = function () {
            let z = this.z;
            this.z = this.y;
            this.y = z;
        }

        this.dump = function () {
            return "Vector3D: [" + this.x + "," + this.y + "," + this.z + "]";
        }
    },
    TwoVector3D: function (v1, v2) {
        this.v1 = v1;
        this.v2 = v2;
    },
    Line3D: function (v1, v2) {
        this.v1 = v1;
        this.v2 = v2;
    },
    Plane3D: function (v1, v2, v3, colour) {
        if (!ThreeD.def(v1) || !ThreeD.def(v2) || !ThreeD.def(v3))
            return null;
        if (v1 == null || v2 == null || v3 == null)
            return null;


        this.v1 = v1;
        this.v2 = v2;
        this.v3 = v3;

        if (ThreeD.def(colour))
            this.colour = colour;
        else
            this.colour = new ThreeD.Colour3D("#000000");

        this.shadecolour = "#000000";

        this.normal = (this.v2.subtract(this.v1)).cross(this.v3.subtract(this.v1));
        this.normal.normalize();

        this.centre = function () {
            //Returns a 3d vecticie of the centre
            r = this.v1.add(this.v2.add(this.v3));
            return r.scale(1 / 3);
        }

        this.intercept = function (from, dir) {
            let E1 = this.v2.subtract(this.v1);
            let E2 = this.v3.subtract(this.v1);
            let n = E1.cross(E2);
            let d = -(dir.dot(n));
            if (d < 1e-6)
                return null;
            let i = 1 / d;
            let AO = from.subtract(this.v1);
            let t = AO.dot(n) * i;

            let DAO = AO.cross(dir);
            let u = E2.dot(DAO) * i;
            let v = -(E1.dot(DAO) * i);

            if (t >= 0.0 && u >= 0.0 && v >= 0.0 && (u + v) <= 1.0) {
                let r = from.add(dir.scale(t));
                r.d = t;
                r.u = u;
                r.v = v;
                return r;
            }
            else
                return null;
        }

        this.copy = function () {
            let q = new ThreeD.Plane3D(this.v1,this.v2,this.v3,this.colour);
            q.shadecolour = this.shadecolour;
            return q;
        }

        this.updateVectors = function (v1, v2, v3) {
            q.v1 = v1;
            q.v2 = v2;
            q.v2 = v3;
            q.normal = (v2.subtract(v1)).cross(v3.subtract(v1));
            q.normal.normalize();
        }

        this.transform = function (matrix) {
            q = this.copy();
            q.updateVectors(this.v1.applymatrix(matrix), this.v2.applymatrix(matrix), this.v3.applymatrix(matrix));
            return q;
        }

        this.projection2D = function (f) {
            q = this.copy();
            q.updateVectors(ThreeD.Projection2d(this.v1, f), ThreeD.Projection2d(this.v2, f), ThreeD.Projection2d(this.v3, f));
            return q;
        }

        this.shade = function (lights) {
            let centre = this.centre();
            let c = ThreeD.Shader3D(lights, this.colour, centre, this.normal);
            this.shadecolour = c.toString();
        }

        this.pointsValid = function () {
            if (!this.v1 || !this.v2 || !this.v3)
               return false;
            return true;
        }

        this.draw = function (cvs, camera, options) {
            var plane = this.transform(camera.matrix);
            var p = plane.projection2D(camera.focal);
            if (p.pointsValid())
                cvs.drawPlane(q, options);
        }

        this.dump = function (t) {
            var ret = "";
            if (ThreeD.def(t))
                ret = t + " ";
            ret += "Plane3D: ";

            if (this.v1)
                ret += this.v1.dump();
            else
                ret += "[null]";

            ret += " ";

            if (this.v2)
                ret += this.v2.dump();
            else
                ret += "[null]";

            ret += " ";
            
            if (this.v3)
                ret += this.v3.dump();
            else
                ret += "[null]";

            return ret;
        }
    },
    Quad3D: function (v1, v2, v3, v4,colour) {

        this.points = [v1, v2, v3, v4];
        this.colour = colour;
        this.pl1 = new ThreeD.Plane3D(v1, v2, v3, colour);
        this.pl2 = new ThreeD.Plane3D(v3, v4, v1, colour);
        this.name = "";

        this.copy = function () {
            var q = new ThreeD.Quad3D(this.points[0], this.points[1], this.points[2], this.points[3],this.colour);
            q.name = this.name;
            q.pl1.shadecolour = this.pl1.shadecolour;
            q.pl2.shadecolour = this.pl2.shadecolour;
            return q;
        }

        this.transform = function (matrix) {
            var q = new ThreeD.Quad3D(
                this.points[0].applymatrix(matrix),
                this.points[1].applymatrix(matrix),
                this.points[2].applymatrix(matrix),
                this.points[3].applymatrix(matrix));
            q.name = this.name;
            q.pl1.shadecolour = this.pl1.shadecolour;
            q.pl2.shadecolour = this.pl2.shadecolour;

            return q;
        }

        this.projection2D = function (f) {
            var q = new ThreeD.Quad3D(
                ThreeD.Projection2d(this.points[0], f),
                ThreeD.Projection2d(this.points[1], f),
                ThreeD.Projection2d(this.points[2], f),
                ThreeD.Projection2d(this.points[3], f));
            q.name = this.name;
            q.pl1.shadecolour = this.pl1.shadecolour;
            q.pl2.shadecolour = this.pl2.shadecolour;
            return q;
        }

        this.pointsValid = function () {
            for (let i = 0; i < this.points.length; i++) {
                if (!this.points[i])
                    return false;
            }
            return true;
        }

        this.centre = function () {
            //Returns a 3d vecticie of the centre
            r = this.points[0].add(this.points[1].add(this.points[2].add(this.points[3])));
            return r.scale(1 / 4);
        }
        
        this.shade = function (lights) {
            let centre = this.centre();
            let c = ThreeD.Shader3D(lights, this.colour, centre, this.pl1.normal);
            this.pl1.shadecolour = c.toString();
            this.pl2.shadecolour = c.toString();
        }

        this.draw = function (cvs, camera, options) {
            var quad = this.transform(camera.matrix);
            var q = quad.projection2D(camera.focal);
            if (q.pointsValid())
                cvs.drawQuad(q, options);
        }

        this.dump = function (t) {
            var ret = "";
            if (ThreeD.def(t))
                ret = t + " ";
            ret += "Quad3D: " + this.pl1.v1.dump() + " " + this.pl1.v2.dump() + " " + this.pl1.v3.dump() + " " + this.pl2.v2.dump();
            return ret;
        }
    },
    Box3D: function (w, h, d, offset, colours) {
        
        this.w = w;
        this.h = h;
        this.d = d;
        this.offset = offset;
        this.colours = {};

        if (ThreeD.def(colours)) {
            this.colours = colours;
            if (!ThreeD.def(colours['front']))
                this.colours['front'] = new ThreeD.Colour3D("#000000");
            if (!ThreeD.def(colours['bottom']))
                this.colours['bottom'] = new ThreeD.Colour3D("#000000");
            if (!ThreeD.def(colours['back']))
                this.colours['back'] = new ThreeD.Colour3D("#000000");
            if (!ThreeD.def(colours['top']))
                this.colours['top'] = new ThreeD.Colour3D("#000000");
            if (!ThreeD.def(colours['right']))
                this.colours['right'] = new ThreeD.Colour3D("#000000");
            if (!ThreeD.def(colours['left']))
                this.colours['left'] = new ThreeD.Colour3D("#000000");
        }
        else {
            this.colours['front'] = new ThreeD.Colour3D("#000000");
            this.colours['bottom'] = new ThreeD.Colour3D("#000000");
            this.colours['back'] = new ThreeD.Colour3D("#000000");
            this.colours['top'] = new ThreeD.Colour3D("#000000");
            this.colours['right'] = new ThreeD.Colour3D("#000000");
            this.colours['left'] = new ThreeD.Colour3D("#000000");
        }


        //front face
        var v1 = (new ThreeD.Vector3D(0.0, 0.0, 0.0)).add(offset); 
        var v2 = (new ThreeD.Vector3D(0.0, h, 0.0)).add(offset);
        var v3 = (new ThreeD.Vector3D(w, h, 0.0)).add(offset);
        var v4 = (new ThreeD.Vector3D(w, 0.0, 0.0)).add(offset);
        var v5 = (new ThreeD.Vector3D(0, 0.0, d)).add(offset);
        var v6 = (new ThreeD.Vector3D(w, 0.0, d)).add(offset);
        var v7 = (new ThreeD.Vector3D(0, h, d)).add(offset);
        var v8 = (new ThreeD.Vector3D(w, h, d)).add(offset);

        var f1 = new ThreeD.Quad3D(v1, v2, v3, v4, this.colours['front']); //Front
        var f2 = new ThreeD.Quad3D(v1, v4, v6, v5, this.colours['bottom']); //Bottom
        var f3 = new ThreeD.Quad3D(v6, v8, v7, v5, this.colours['back']); //Back
        var f4 = new ThreeD.Quad3D(v2, v7, v8, v3, this.colours['top']); //Top
        var f5 = new ThreeD.Quad3D(v4, v3, v8, v6, this.colours['right']); //Right
        var f6 = new ThreeD.Quad3D(v5, v7, v2, v1, this.colours['left']); //Left

        f1.name = "front";
        f2.name = "bottom";
        f3.name = "back";
        f4.name = "top";
        f5.name = "right";
        f6.name = "left";



        this.faces = [f1, f2, f3, f4, f5, f6];

        this.copy = function () {
            var n = new ThreeD.Box3D(this.w,this.h,this.d,this.offset,this.colours);
            for (let i = 0; i < this.faces.length; i++)
                n.faces[i] = this.faces[i].copy();
            return n;
        }

        this.transform = function (matrix) {
            var b = this.copy();
            for (f = 0; f < b.faces.length; f++) {
                b.faces[f] = b.faces[f].transform(matrix);
            }
            return b;
        }

        this.projection2D = function (f) {
            var b = this.copy();
            for (let i = 0; i < b.faces.length; i++) {
                b.faces[i] = b.faces[i].projection2D(f);
            }
            return b;
        }

        this.shade = function (lights) {
            quads = this.allQuads(false);
            for (let i = 0; i < quads.length; i++) {
                quads[i].shade(lights);
            }
        }

        this.allPlanes = function () {
            var planes = [];
            for (let i = 0; i < this.faces.length; i++) {
                planes.push(this.faces[i].pl1);
                planes.push(this.faces[i].pl2);
            }
            return planes;
        }

        this.allQuads = function (sortbyZ = false) {
            var quads = [];
            for (let i = 0; i < this.faces.length; i++) {
                quads.push(this.faces[i]);
            }
            if (sortbyZ) {
                quads.sort(function (a, b) {
                    var bs = [b.points[0].z, b.points[1].z, b.points[2].z, b.points[3].z];
                    var as = [a.points[0].z, a.points[1].z, a.points[2].z, a.points[3].z];
                    bs.sort(function (a, b) { return b - a });
                    as.sort(function (a, b) { return b - a });
                    var b1 = bs[0] * 8 + bs[1] * 4 + bs[2] * 2 + bs[3];
                    var a1 = as[0] * 8 + as[1] * 4 + as[2] * 2 + bs[3];
                    return b1 - a1;
                });
            }
            return quads;
        }

        this.dump = function () {
            return "Box3D: " + this.faces[0].dump() + this.faces[1].dump() + this.faces[2].dump() + this.faces[3].dump() + this.faces[4].dump() + this.faces[5].dump();
        }

    },
    BoxColour3D: function (f,bo,ba,t,r,l) {
        //default is white

        this.front = new ThreeD.Colour3D("#ffffff");
        this.bottom = new ThreeD.Colour3D("#ffffff");
        this.back = new ThreeD.Colour3D("#ffffff");
        this.top = new ThreeD.Colour3D("#ffffff");
        this.right = new ThreeD.Colour3D("#ffffff");
        this.left = new ThreeD.Colour3D("#ffffff");

        if (ThreeD.def(f)) {
            this.front = new ThreeD.Colour3D(f);
        }
        if (ThreeD.def(bo)) {
            this.bottom = new ThreeD.Colour3D(bo);
        }
        if (ThreeD.def(ba)) {
            this.back = new ThreeD.Colour3D(ba);
        }
        if (ThreeD.def(t)) {
            this.top = new ThreeD.Colour3D(t);
        }
        if (ThreeD.def(r)) {
            this.right = new ThreeD.Colour3D(r);
        }
        if (ThreeD.def(l)) {
            this.left = new ThreeD.Colour3D(l);
        }
    },
    Object3D: function (geomatry, options) {
        this.geomatry = geomatry;
        this.options = options;
        this.allPlanes = function () {
            var planes = this.geomatry.allPlanes();  
            planes.sort(function (a, b) {
                var bs = [b.v1.z, b.v2.z, b.v3.z];
                var as = [a.v1.z, a.v2.z, a.v3.z];
                bs.sort(function (a, b) { return b - a });
                as.sort(function (a, b) { return b - a });

                var b1 = bs[0] * 100 + bs[1] * 10 + bs[2];
                var a1 = as[0] * 100 + as[1] * 10 + as[2];
                return b1 - a1;
            });
            return planes;
        }

        this.allQuads = function () {
            var quads = this.geomatry.allQuads();
            quads.sort(function (a, b) {
                var bs = [b.points[0].z, b.points[1].z, b.points[2].z, b.points[3].z];
                var as = [a.points[0].z, a.points[1].z, a.points[2].z, a.points[3].z];
                bs.sort(function (a, b) { return b - a });
                as.sort(function (a, b) { return b - a });
                var b1 = bs[0] * 8 + bs[1] * 4 + bs[2] * 2 + bs[3];
                var a1 = as[0] * 8 + as[1] * 4 + as[2] * 2 + bs[3];
                return b1 - a1;
            });
        }

        this.shade = function (lights) {
            //Call the object shade
            this.geomatry.shade(lights);

            //var planes = this.geomatry.allPlanes();
            //for (let i = 0; i < planes.length; i++) {
                //planes[i].shade(lights);
            //}
        }
    },
    Objects3D: function () {
        this.objects = [];

        this.add = function (obj) {
            this.objects.push(obj);
        }

        this.createBox = function (w, h, d, offset, colours) {
            let b = new ThreeD.Box3D(w, h, d, offset, colours);
            this.add(new ThreeD.Object3D(b, null));
        }

        this.shadeall = function (lights) {
            this.objects.forEach(function (o) { o.shade(lights) });
        }

        this.drawall = function (cvs, camera, options) {
            //Get all quads and all planes
            let items = [];
            this.objects.forEach(
                function (o) {
                    if (o.geomatry.constructor.name == "Box3D") {
                        for (let i = 0; i < 6; i++) {
                            items.push(o.geomatry.faces[i]);
                        }
                    }
                    if (o.geomatry.constructor.name == "Plane3D") {
                        items.push(o.geomatry);
                    }
               }
            );
            //Sort by length to camera ofr three largest points.
            items.sort(function (a, b) {
                if (a.constructor.name == "Quad3D") {
                    zs = [(a.points[0].subtract(camera.eye)).length(),
                    (a.points[1].subtract(camera.eye)).length(),
                    (a.points[2].subtract(camera.eye)).length(),
                    (a.points[3].subtract(camera.eye)).length()
                    ];
                    zs.sort(function (a, b) { return b - a });
                    aval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2) + zs[3];
                }
                if (b.constructor.name == "Quad3D") {
                    zs = [(b.points[0].subtract(camera.eye)).length(),
                    (b.points[1].subtract(camera.eye)).length(),
                    (b.points[2].subtract(camera.eye)).length(),
                    (b.points[3].subtract(camera.eye)).length()
                    ];
                    zs.sort(function (a, b) { return b - a });
                    bval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2) + zs[3];

                }
                if (a.constructor.name == "Plane3D") {
                    zs = [(a.v1.subtract(camera.eye)).length(),
                        (a.v2.subtract(camera.eye)).length(),
                        (a.v3.subtract(camera.eye)).length()
                        ];
                    zs.sort(function (a, b) { return b - a });
                    aval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2);
                }
                if (b.constructor.name == "Plane3D") {
                    zs = [(b.v1.subtract(camera.eye)).length(),
                    (b.v2.subtract(camera.eye)).length(),
                    (b.v3.subtract(camera.eye)).length()
                    ];
                    zs.sort(function (a, b) { return b - a });
                    bval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2);
                }
                return bval - aval;
            });
            //Draw all based on deseending length
            items.forEach(function (o) { o.draw(cvs, camera, options) });
        }

        this.allPlanes = function (camera,sort) {
            let items = [];
            this.objects.forEach(
                function (o) {
                    if (o.geomatry.constructor.name == "Box3D") {
                        for (let i = 0; i < 6; i++) {
                            items.push(o.geomatry.faces[i].pl1);
                            items.push(o.geomatry.faces[i].pl2);
                        }
                    }
                    if (o.geomatry.constructor.name == "Plane3D") {
                        items.push(o.geomatry);
                    }
                }
            );
            //No have a complete list of planes fro all objects
            if (ThreeD.def(camera) && ThreeD.def(sort) && sort) {
                items.sort(function (a, b) {
                    let aval = 0;
                    let bval = 0;
                    if (a.constructor.name == "Plane3D") {
                        zs = [(a.v1.subtract(camera.eye)).length(),
                        (a.v2.subtract(camera.eye)).length(),
                        (a.v3.subtract(camera.eye)).length()
                        ];
                        zs.sort(function (a, b) { return b - a });
                        aval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2);
                    }
                    if (b.constructor.name == "Plane3D") {
                        zs = [(b.v1.subtract(camera.eye)).length(),
                        (b.v2.subtract(camera.eye)).length(),
                        (b.v3.subtract(camera.eye)).length()
                        ];
                        zs.sort(function (a, b) { return b - a });
                        bval = (zs[0] * 8) + (zs[1] * 4) + (zs[2] * 2);
                    }
                    return bval - aval;
                });
            }
            return items;
        },
        this.RayTrace = function (from, dir) {
            let planes = this.allPlanes();
            let dist = [];
            for (let j = 0; j < planes.length; j++) {
                let v = planes[j].intercept(from, dir);
                if (v) {
                    //Hit
                    dist.push({ pl: planes[j], pt: v, d: v.d });
                }
            }
            if (dist.length > 0) {
                dist.sort(function (a, b) { return a.d - b.d });
                return dist[0].pl.shadecolour;
            }
            return null;
        }
    },
    Light3D: function (type, colour, location, direction) {
        this.type = type;
        this.colour = colour;
        switch (type) {
            case "ambient":
                break;
            case "omni":
                this.location = location;
                break;
            case "spot":
                this.location = location;
                this.direction = direction;
                this.direction.normalize();
                this.invdirection = this.direction.inverse();
                break;
        }
    },
    Colour3D: function (strcolour) {
        this.red = 1.0;
        this.green = 1.0;
        this.blue = 1.0;

        if (ThreeD.def(strcolour)) {
            let s = strcolour;
            if (s.substr(0, 1) == "#")
                s = s.substr(1);
            switch (s.length) {
                case 0:
                    break;
                case 6:
                    this.red = parseInt("0x" + s.substr(0, 2));
                    this.green = parseInt("0x" + s.substr(2, 2));
                    this.blue = parseInt("0x" + s.substr(4, 2));
                    break;
            }
            this.red = parseFloat(this.red / 255);
            this.green = parseFloat(this.green / 255);
            this.blue = parseFloat(this.blue / 255);
        }

        this.copy = function () {
            return new ThreeD.Colour3D(this.toString());
        }

        this.blend = function (c, f) {
            fact = 1.0;
            if (ThreeD.def(f))
                fact = parseFloat(f);
            var r = new ThreeD.Colour3D();
            r.red = c.red * this.red * fact;
            r.green =c.green * this.green * fact;
            r.blue = c.blue * this.blue * fact;
            return r;
        }

        this.add = function (a) {
            var r = new ThreeD.Colour3D();
            r.red = a.red + this.red;
            r.green = a.green + this.green;
            r.blue = a.blue + this.blue;
            return r;
        }

        this.toString = function () {
            return "#" + ThreeD.pad(parseInt(Math.min(this.red, 1.0) * 255).toString(16), 2) + ThreeD.pad(parseInt(Math.min(this.green, 1.0) * 255).toString(16), 2) + ThreeD.pad(parseInt(Math.min(this.blue,1.0) * 255).toString(16),2);
        }
    },
    Camera3D: function (eye,at,up,focal) {
        this.eye = eye;
        this.at = at;
        this.up = up;
        this.focal = focal;
        this.matrix = new ThreeD.Matrix3D();
        this.matrix.setView(eye, at, up);
    },
    Matrix3D: function () {
        this.data = [1.0, 0.0, 0.0, 0.0,
                     0.0, 1.0, 0.0, 0.0,
                     0.0, 0.0, 1.0, 0.0,
                     0.0, 0.0, 0.0, 1.0];
        this.setView = function (veye, vat, vup) {
            var fwd = vat.subtract(veye);
            fwd.normalize();
            var right = vup.cross(fwd);
            right.normalize();
            var up = fwd.cross(right);
            up.normalize();
            this.data[0] = right.x;
            this.data[1] = up.x;
            this.data[2] = fwd.x;
            this.data[3] = 0.0;
            this.data[4] = right.y;
            this.data[5] = up.y;
            this.data[6] = fwd.y;
            this.data[7] = 0.0;
            this.data[8] = right.z;
            this.data[9] = up.z;
            this.data[10] = fwd.z;
            this.data[11] = 0.0;
            this.data[12] = -(right.dot(veye));
            this.data[13] = -(up.dot(veye));
            this.data[14] = -(fwd.dot(veye));
            this.data[15] = 1.0;

        }
        this.inverse = function () {
            var out = new ThreeD.Matrix3D();
            out.data[0] = this.data[5] * this.data[10] * this.data[15] -
                         this.data[5] * this.data[11] * this.data[14] -
                         this.data[9] * this.data[6] * this.data[15] +
                         this.data[9] * this.data[7] * this.data[14] +
                         this.data[13] * this.data[6] * this.data[11] -
                         this.data[13] * this.data[7] * this.data[10];

            out.data[4] = -this.data[4] * this.data[10] * this.data[15] +
                          this.data[4] * this.data[11] * this.data[14] +
                          this.data[8] * this.data[6] * this.data[15] -
                          this.data[8] * this.data[7] * this.data[14] -
                          this.data[12] * this.data[6] * this.data[11] +
                          this.data[12] * this.data[7] * this.data[10];

            out.data[8] = this.data[4] * this.data[9] * this.data[15] -
                         this.data[4] * this.data[11] * this.data[13] -
                         this.data[8] * this.data[5] * this.data[15] +
                         this.data[8] * this.data[7] * this.data[13] +
                         this.data[12] * this.data[5] * this.data[11] -
                         this.data[12] * this.data[7] * this.data[9];

            out.data[12] = -this.data[4] * this.data[9] * this.data[14] +
                         this.data[4] * this.data[10] * this.data[13] +
                         this.data[8] * this.data[5] * this.data[14] -
                         this.data[8] * this.data[6] * this.data[13] -
                         this.data[12] * this.data[5] * this.data[10] +
                         this.data[12] * this.data[6] * this.data[9];

            out.data[1] = -this.data[1] * this.data[10] * this.data[15] +
                          this.data[1] * this.data[11] * this.data[14] +
                          this.data[9] * this.data[2] * this.data[15] -
                          this.data[9] * this.data[3] * this.data[14] -
                          this.data[13] * this.data[2] * this.data[11] +
                          this.data[13] * this.data[3] * this.data[10];

            out.data[5] = this.data[0] * this.data[10] * this.data[15] -
                         this.data[0] * this.data[11] * this.data[14] -
                         this.data[8] * this.data[2] * this.data[15] +
                         this.data[8] * this.data[3] * this.data[14] +
                         this.data[12] * this.data[2] * this.data[11] -
                         this.data[12] * this.data[3] * this.data[10];

            out.data[9] = -this.data[0] * this.data[9] * this.data[15] +
                      this.data[0] * this.data[11] * this.data[13] +
                      this.data[8] * this.data[1] * this.data[15] -
                      this.data[8] * this.data[3] * this.data[13] -
                      this.data[12] * this.data[1] * this.data[11] +
                      this.data[12] * this.data[3] * this.data[9];

            out.data[13] = this.data[0] * this.data[9] * this.data[14] -
                      this.data[0] * this.data[10] * this.data[13] -
                      this.data[8] * this.data[1] * this.data[14] +
                      this.data[8] * this.data[2] * this.data[13] +
                      this.data[12] * this.data[1] * this.data[10] -
                      this.data[12] * this.data[2] * this.data[9];

            out.data[2] = this.data[1] * this.data[6] * this.data[15] -
                     this.data[1] * this.data[7] * this.data[14] -
                     this.data[5] * this.data[2] * this.data[15] +
                     this.data[5] * this.data[3] * this.data[14] +
                     this.data[13] * this.data[2] * this.data[7] -
                     this.data[13] * this.data[3] * this.data[6];

            out.data[6] = -this.data[0] * this.data[6] * this.data[15] +
                      this.data[0] * this.data[7] * this.data[14] +
                      this.data[4] * this.data[2] * this.data[15] -
                      this.data[4] * this.data[3] * this.data[14] -
                      this.data[12] * this.data[2] * this.data[7] +
                      this.data[12] * this.data[3] * this.data[6];

            out.data[10] = this.data[0] * this.data[5] * this.data[15] -
                      this.data[0] * this.data[7] * this.data[13] -
                      this.data[4] * this.data[1] * this.data[15] +
                      this.data[4] * this.data[3] * this.data[13] +
                      this.data[12] * this.data[1] * this.data[7] -
                      this.data[12] * this.data[3] * this.data[5];

            out.data[14] = -this.data[0] * this.data[5] * this.data[14] +
                       this.data[0] * this.data[6] * this.data[13] +
                       this.data[4] * this.data[1] * this.data[14] -
                       this.data[4] * this.data[2] * this.data[13] -
                       this.data[12] * this.data[1] * this.data[6] +
                       this.data[12] * this.data[2] * this.data[5];

            out.data[3] = -this.data[1] * this.data[6] * this.data[11] +
                      this.data[1] * this.data[7] * this.data[10] +
                      this.data[5] * this.data[2] * this.data[11] -
                      this.data[5] * this.data[3] * this.data[10] -
                      this.data[9] * this.data[2] * this.data[7] +
                      this.data[9] * this.data[3] * this.data[6];

            out.data[7] = this.data[0] * this.data[6] * this.data[11] -
                     this.data[0] * this.data[7] * this.data[10] -
                     this.data[4] * this.data[2] * this.data[11] +
                     this.data[4] * this.data[3] * this.data[10] +
                     this.data[8] * this.data[2] * this.data[7] -
                     this.data[8] * this.data[3] * this.data[6];

            out.data[11] = -this.data[0] * this.data[5] * this.data[11] +
                       this.data[0] * this.data[7] * this.data[9] +
                       this.data[4] * this.data[1] * this.data[11] -
                       this.data[4] * this.data[3] * this.data[9] -
                       this.data[8] * this.data[1] * this.data[7] +
                       this.data[8] * this.data[3] * this.data[5];

            out.data[15] = this.data[0] * this.data[5] * this.data[10] -
                      this.data[0] * this.data[6] * this.data[9] -
                      this.data[4] * this.data[1] * this.data[10] +
                      this.data[4] * this.data[2] * this.data[9] +
                      this.data[8] * this.data[1] * this.data[6] -
                      this.data[8] * this.data[2] * this.data[5];

            var det = this.data[0] * out.data[0] + this.data[1] * out.data[4] + this.data[2] * out.data[8] + this.data[3] * out.data[12];

            if (det == 0)
                return false;

            det = 1.0 / det;

            for (i = 0; i < 16; i++)
                out.data[i] = out.data[i] * det;

            return out;
        }
        this.transformarray = function (v) {
            for (var i = 0; i < v.length; i++) {
                v[i] = v[i].applymatrix(this);
            }
        }
    },
    Projection2d(v1, f) {
        if (v1.z > 0) {
            return new ThreeD.Vector3D(v1.x * (f / v1.z), v1.y * (f / v1.z), 0.0);
        }
        return null;
    },
    Shader3D: function (lights,faceColour,centre,normal) {
        let ambient = new ThreeD.Colour3D("#000000");
        let omnis = [];
        let spots = [];
        for (let i = 0; i < lights.length; i++) {
            light = lights[i];
            switch (light.type) {
                case "ambient":
                    ambient = faceColour.copy().blend(light.colour, 1.0);
                    break;
                case "omni":
                    let vectTo = light.location.subtract(centre);
                    vectTo.normalize();
                    dot = normal.dot(vectTo);
                    if (dot > 0.0) {
                        sp = faceColour.copy().blend(light.colour, dot);
                        omnis.push(sp);
                    }
                    break;
                case "spot":
                    break;
            }
        }
        for (let j = 0; j < spots.length; j++) {
            ambient = ambient.add(spots[j]);
        }
        for (let j = 0; j < omnis.length; j++) {
            ambient = ambient.add(omnis[j]);
        }
        return ambient;
    },

    Screen: function (scale, blx, bly) {
        this.scale = scale;
        this.bottomleftX = blx;
        this.bottomleftY = bly;

        this.RealToScreen = function (vect, view) {
            var v;
            if (ThreeD.def(view))
                v = vect.applymatrix(view);
            else
                v = vect;
            vout = new ThreeD.Vector3D(0.0, 0.0, 0.0);
            if (!ThreeD.def(v))
                console.log("Bug");
            else {
                vout.x = (v.x / v.z) * this.scale;
                vout.y = (v.y / v.z) * this.scale;
                vout.x = vout.x - this.bottomleftX;
                vout.y = vout.y - this.bottomleftY;
                //Set z, not used as a 2d point, put if -ve then not visible.
                vout.z = v.z;
            }
            return vout;
        };
        this.RealToScreenLine = function (v1, v2) {

            var m;
            var x;
            var y;
            vs = new ThreeD.Vector3D(0.0, 0.0, 0.0);
            vp = new ThreeD.Vector3D(0.0, 0.0, 0.0);

            if (v1.z < 0.0 || v2.z < 0.0) {
                if (v1.z < 0.0 && v2.z < 0.0)
                    return null;
                if (v2.x == v1.x)
                    x = v2.x;
                else {
                    m = (v2.z - v1.z) / (v2.x - v1.x);
                    x = (-(v1.z) + (m * v1.x)) / m;
                }
                if (v2.y == v1.y)
                    y = v2.y;
                else {
                    m = (v2.z - v1.z) / (v2.y - v1.y);
                    y = (-(v1.z) + (m * v1.y)) / m;
                }
                //Which one i -ve z
                if (v1.z < 0.0) {
                    vs.x = (x * this.scale) - this.bottomleftX;
                    vs.y = (y * this.scale) - this.bottomleftY;
                    vs.z = v1.z;
                    vp.x = ((v2.x / v2.z) * this.scale) - this.bottomleftX;
                    vp.y = ((v2.y / v2.z) * this.scale) - this.bottomleftY;
                    vp.z = v2.z;
                }
                else {
                    vp.x = (x * this.scale) - this.bottomleftX;
                    vp.y = (y * this.scale) - this.bottomleftY;
                    vp.z = v2.z;
                    vs.x = ((v1.x / v1.z) * this.scale) - this.bottomleftX;
                    vs.y = ((v1.y / v1.z) * this.scale) - this.bottomleftY;
                    vs.z = v1.z;
                }
            }
            else {
                vs.x = ((v1.x / v1.z) * this.scale) - this.bottomleftX;
                vs.y = ((v1.y / v1.z) * this.scale) - this.bottomleftY;
                vp.x = ((v2.x / v2.z) * this.scale) - this.bottomleftX;
                vp.y = ((v2.y / v2.z) * this.scale) - this.bottomleftY;
            }

            return new ThreeD.TwoVector3D(vs, vp);
        };
    }
}