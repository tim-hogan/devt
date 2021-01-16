// JScript source code
var ThreeD = {
    def: function (x) {
        if (typeof x != 'undefined')
            return true;
        return false;
    },
    Vector3D: function (x, y, z) {
        this.x = x;
        this.y = y;
        this.z = z;

        this.add = function (vector) {
            return vout = new ThreeD.Vector3D(this.x + vector.x, this.y + vector.y, this.z + vector.z);
        }

        this.subtract = function (vector) {
            return vout = new ThreeD.Vector3D(this.x - vector.x, this.y - vector.y, this.z - vector.z);
        }

        this.length = function () {
            return Math.sqrt(this.x * this.x + this.y * this.y + this.z * this.z);
        }

        this.lenthto = function (vector) {
            var dx = this.x - vector.x;
            var dy = this.y - vector.y;
            var dz = this.z - vector.z;
            return Math.sqrt(dx * dx + dy * dy + dz * dz);
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
    },
    TwoVector3D: function (v1, v2) {
        this.v1 = v1;
        this.v2 = v2;
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