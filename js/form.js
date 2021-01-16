devt.form = {
    version: 1.0,
    // **********************************************************************
    // JS FUNCTIONS
    // **********************************************************************
    input: function (n) {
        console.log("Input is: " + n.value);
        var v = devt.ga(n, 'devtvalidation');
        var valid = true;
        if (devt.def(v)) {
            if (n.value.length > 0) {
                switch (v) {
                    case 'integer':
                        valid = this.validate_integer(n.value);
                        break;
                    case 'none':
                        break;
                }
            }

            if (!valid) {
                n.className = n.className + " err";
            }
            else {
                var name = n.className;
                var i = name.indexOf(" err");
                if (i >= 0)
                    n.className = name.substr(0,i);
            }
        }
    },

    // **********************************************************************
    //  Validations functions
    // **********************************************************************
    validate_integer: function (v) {
        return (v.match(/^[0-9]+$/) != null && devt.isWholeNum(v));
    }
};