// JavaScript source code
var NZTM = {
    A: 6378137,
    RF: 298.257222101,
    CM: 3.019419606, //173.0 in radians
    OLAT: 0.0,  //0 in radians
    SF: 0.9996,
    FE: 1600000.0,
    FN: 10000000.0,
    UTOM: 1.0
}
class tmProjection {

    constructor(TM) {
        this.a = TM.A;
        this.rf = TM.RF;
        this.meridian = TM.CM;
        this.scalef = TM.SF;
        this.orglat = TM.OLAT;
        this.falsee = TM.FE;
        this.falsen = TM.FN;
        this.utom = TM.UTOM;

        this.f = 0;
        if (this.rf != 0.0)
            this.f = 1.0 / this.rf; 
        this.e2 = (2.0 * this.f) - (this.f * this.f);
        this.ep2 = this.e2 / (1.0 - this.e2);
        this.om = tmProjection.meridian_arc(this, this.orglat);
    }

    static meridian_arc(projection, lt) {
        let e2 = projection.e2;
        let a = projection.a;
        let e4 = e2 * e2;
        let e6 = e4 * e2;
        let A0 = 1 - (e2 / 4.0) - (3.0 * e4 / 64.0) - (5.0 * e6 / 256.0);
        let A2 = (3.0 / 8.0) * (e2 + e4 / 4.0 + 15.0 * e6 / 128.0);
        let A4 = (15.0 / 256.0) * (e4 + 3.0 * e6 / 4.0);
        let A6 = 35.0 * e6 / 3072.0;

        return a * (A0 * lt - A2 * Math.sin(2 * lt) + A4 * Math.sin(4 * lt) - A6 * Math.sin(6 * lt));
    }
}

class geoConversion {

    static rad2deg = 180.0 / Math.PI;
    static deg2rad = Math.PI / 180.0;
    static TWOPI = (2 * Math.PI);

    static foot_point_lat(tm, m) {
        var f = tm.f;
        var a = tm.a;
        var n;
        var n2;
        var n3;
        var n4;
        var g;
        var sig;
        var phio;

        n = f / (2.0 - f);
        n2 = n * n;
        n3 = n2 * n;
        n4 = n2 * n2;

        g = a * (1.0 - n) * (1.0 - n2) * (1 + 9.0 * n2 / 4.0 + 225.0 * n4 / 64.0);
        sig = m / g;

        phio = sig + (3.0 * n / 2.0 - 27.0 * n3 / 32.0) * Math.sin(2.0 * sig)
            + (21.0 * n2 / 16.0 - 55.0 * n4 / 32.0) * Math.sin(4.0 * sig)
            + (151.0 * n3 / 96.0) * Math.sin(6.0 * sig)
            + (1097.0 * n4 / 512.0) * Math.sin(8.0 * sig);

        return phio;
    }



    static tm_geod(tm, ce, cn) {

        var rslt = { lat: 0.0, lon: 0.0 };

        var fn = tm.falsen;
        var fe = tm.falsee;
        var sf = tm.scalef;
        var e2 = tm.e2;
        var a = tm.a;
        var cm = tm.meridian;
        var om = tm.om;
        var utom = tm.utom;
        var cn1;
        var fphi;
        var slt;
        var clt;
        var eslt;
        var eta;
        var rho;
        var psi;
        var E;
        var x;
        var x2;
        var t;
        var t2;
        var t4;
        var trm1;
        var trm2;
        var trm3;
        var trm4;

        cn1 = (cn - fn) * utom / sf + om;
        fphi = geoConversion.foot_point_lat(tm, cn1);
        slt = Math.sin(fphi);
        clt = Math.cos(fphi);

        eslt = (1.0 - e2 * slt * slt);
        eta = a / Math.sqrt(eslt);
        rho = eta * (1.0 - e2) / eslt;
        psi = eta / rho;

        E = (ce - fe) * utom;
        x = E / (eta * sf);
        x2 = x * x;


        t = slt / clt;
        t2 = t * t;
        t4 = t2 * t2;

        trm1 = 1.0 / 2.0;

        trm2 = ((-4.0 * psi
            + 9.0 * (1 - t2)) * psi
            + 12.0 * t2) / 24.0;

        trm3 = ((((8.0 * (11.0 - 24.0 * t2) * psi
            - 12.0 * (21.0 - 71.0 * t2)) * psi
            + 15.0 * ((15.0 * t2 - 98.0) * t2 + 15)) * psi
            + 180.0 * ((-3.0 * t2 + 5.0) * t2)) * psi + 360.0 * t4) / 720.0;

        trm4 = (((1575.0 * t2 + 4095.0) * t2 + 3633.0) * t2 + 1385.0) / 40320.0;
 
        rslt.lat = fphi + (t * x * E / (sf * rho)) * (((trm4 * x2 - trm3) * x2 + trm2) * x2 - trm1);

        trm1 = 1.0;

        trm2 = (psi + 2.0 * t2) / 6.0;

        trm3 = (((-4.0 * (1.0 - 6.0 * t2) * psi
            + (9.0 - 68.0 * t2)) * psi
            + 72.0 * t2) * psi
            + 24.0 * t4) / 120.0;

        trm4 = (((720.0 * t2 + 1320.0) * t2 + 662.0) * t2 + 61.0) / 5040.0;
 
        rslt.lon = cm - (x / clt) * (((trm4 * x2 - trm3) * x2 + trm2) * x2 - trm1);

        rslt.lat = rslt.lat * geoConversion.rad2deg;
        rslt.lon = rslt.lon * geoConversion.rad2deg;
        return rslt;
    }



    static geod_tm(tm, lt, ln) {

        var rslt = { e: 0.0, n: 0.0 };

        let fn = tm.falsen;
        let fe = tm.falsee;
        let sf = tm.scalef;
        let e2 = tm.e2;
        let a = tm.a;
        let cm = tm .meridian;
        let om = tm.om;
        let utom = tm.utom;

        let dlon = ln - cm;
        while (dlon > Math.PI) dlon -= geoConversion.TWOPI;
        while (dlon < -Math.PI) dlon += geoConversion.TWOPI;

        let m = tmProjection.meridian_arc(tm, lt);

        let slt = Math.sin(lt);

        let eslt = (1.0 - e2 * slt * slt);
        let eta = a / Math.sqrt(eslt);
        let rho = eta * (1.0 - e2) / eslt;
        let psi = eta / rho;

        let clt = Math.cos(lt);
        let w = dlon;

        let wc = clt * w;
        let wc2 = wc * wc;

        let t = slt / clt;
        let t2 = t * t;
        let t4 = t2 * t2;
        let t6 = t2 * t4;

        let trm1 = (psi - t2) / 6.0;

        let trm2 = (((4.0 * (1.0 - 6.0 * t2) * psi
            + (1.0 + 8.0 * t2)) * psi
            - 2.0 * t2) * psi + t4) / 120.0;

        let trm3 = (61 - 479.0 * t2 + 179.0 * t4 - t6) / 5040.0;

        let gce = (sf * eta * dlon * clt) * (((trm3 * wc2 + trm2) * wc2 + trm1) * wc2 + 1.0);

        rslt.e = gce / utom + fe;


        trm1 = 1.0 / 2.0;

        trm2 = ((4.0 * psi + 1) * psi - t2) / 24.0;

        trm3 = ((((8.0 * (11.0 - 24.0 * t2) * psi
            - 28.0 * (1.0 - 6.0 * t2)) * psi
            + (1.0 - 32.0 * t2)) * psi
            - 2.0 * t2) * psi
            + t4) / 720.0;

        let trm4 = (1385.0 - 3111.0 * t2 + 543.0 * t4 - t6) / 40320.0;

        let gcn = (eta * t) * ((((trm4 * wc2 + trm3) * wc2 + trm2) * wc2 + trm1) * wc2);
        rslt.n = (gcn + m - om) * sf / utom + fn;

        return rslt;
    }

    static geod_nztm(lat, lon) {
        let tm = new tmProjection(NZTM);
        let latdeg = lat * geoConversion.deg2rad;
        let londeg = lon * geoConversion.deg2rad;

        return geoConversion.geod_tm(tm, latdeg, londeg);
    }

    static nztm_geod(e, n) {
        let tm = new tmProjection(NZTM);
        return geoConversion.tm_geod(tm, e, n);
    }




    static test() {

        console.log("Testin lat/lon -41.344071, 174.742869");
        let rslt = geoConversion.geod_nztm(-41.344071, 174.742869);
        console.log("Results = Easting " + rslt.e + "Northing " + rslt.n);
        console.log("Should be: 1745816.0	5421581.3");
        console.log("Convert back");
        rslt = geoConversion.nztm_geod(rslt.e, rslt.n);
        console.log("Results = lat " + rslt.lat + "lon " + rslt.lon);
    }
}