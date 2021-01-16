// JScript source code
var financial = {
    version: 1,
    fv: function (r, n, pmt, pv) {
        var x = Math.pow(1 + r, n);
        return (pv * x) - (pmt * ((x - 1) / r));
    },
    pmt: function (r, n, pv) {
        /*
        r = rate period n
        n = number of periods
        pv = present value
        */
        var m = (r * pv);
        if (n > 0)
            m = m / (1 - Math.pow(1 + r, -n));
        return m;
    },
    interestPaid: function (P, r, m, nStart, nEnd) {
        //P = principle
        //r = rate per period
        //m = payment per period
        //nStart = period start
        //nEnd = period end
        //For 1st year enter interestPaid(P,r,m,0,12);
        //For second year enter interestPaid(P,r,m,12,24);
        var e = (((P * r) - m) * ((Math.pow((1 + r), nEnd) - 1) / r)) + (m * nEnd);
        if (nStart <= 1)
            return e;
        else {
            return e - ((((P * r) - m) * ((Math.pow((1 + r), nStart) - 1) / r)) + (m * nStart));
        }
    },
    principlePaid: function (P, r, m, nStart, nEnd) {
        var o = financial.interestPaid(P, r, m, nStart, nEnd);
        var e = (nEnd * m) - o;
        if (nStart <= 1)
            return e;
        else {
            o = financial.interestPaid(P, r, m, 0, nStart);
            e = e - ((nStart * m) - o);
            return e;
        }
    },
    inflationAdjust: function (v, j, n) {
        var x = v * Math.pow(1 + j, n);
        return x;
    },
    inflationIndex: function (j, n) {
        if (j > 0)
            return (Math.pow(1 + j, n) - 1) / j;
        else
            return n;
    },
    rentalModel: {
        cashForPeriod: function (R, W, E, j, n, P, D, T, r, m) {
            /*
            R Rent per week
            W Number of weeks rented
            E Sum of all annual expesnes
            j infaltion rate
            n Number of years
            P Purchase price
            D Deposit
            T Tax Rate
            r Mortgage Interest Rate
            m repayemnts of loan
            */
            var l = financial.inflationIndex(j, n);
            var o = financial.interestPaid((P - D), r / 12.0, m, 0, n * 12.0);
            var v = financial.principlePaid((P - D), r / 12.0, m, 0, n * 12.0);
            var y = ((l * ((R * W) - E) - o) * (1 - T)) - v;
            return y;
        },
        cagr: function (R, W, E, P, D, j, k, T, n, r, m) {
            /*
            R Rent per week
            W Number of weeks rented
            E Sum of all annual expesnes
            P Purchase price
            D Deposit
            j infaltion rate
            k captial gain rate
            T Tax Rate
            n is years 
            r Mortgage Interest Rate
            m repayemnts of loan (monthly)
            */
            var loan = financial.fv(r / 12.0, n * 12, m, (P - D));
            var v = financial.rentalModel.cashForPeriod(R, W, E, j, n, P, D, T, r, m);
            var equity = P * Math.pow(1 + k, n);
            equity = (equity - loan) + v;
            var cagr = (Math.pow(equity / D, 1 / n)) - 1;
            return cagr;
        }
    }
};
