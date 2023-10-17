// JavaScript source code
var fl2 = {
    def: function (x) {
        if (typeof x != 'undefined')
            return true;
        return false;
    },
    ge: function (n) {
        return document.getElementById(n);
    },
    ce: function (t) {
        return document.createElement(t);
    },
    cea: function (t, n) {
        let e = fl2.ce(t);
        n.appendChild(e);
        return e;
    },
    ci: function (n, v, f) {
        let i = fl2.cea("input", f);
        i.type = "text";
        i.name = n;
        i.value = v;
        return i;
    },
    ga: function (n, a) {
        return n.getAttribute(a);
    }
}
function goBack() {
    window.history.back();
}

function deleteButtonChange(tbl) {
    let but = document.getElementById("del" + tbl);
    let l = document.getElementsByClassName('listcheck' + tbl);
    but.disabled = true;
    for (let i = 0; i < l.length; i++) {
        if (l[i].checked) {
            but.disabled = false;
        }
    }
}

function record_selector(n,v,selff,formtoken) {
    let form = fl2.cea("form", document.body);
    form.method = "POST";
    form.action = selff;
    fl2.ci("v", v, form);
    fl2.ci("rec", n.value, form);
    fl2.ci("formtoken", formtoken, form);
    form.submit();
}

function record_selector_prev(n, v, selff, formtoken) {
    let form = fl2.cea("form", document.body);
    form.method = "POST";
    form.action = selff;
    fl2.ci("v", v, form);
    fl2.ci("prev", 1, form);
    fl2.ci("formtoken", formtoken, form);
    form.submit();
}

function record_selector_next(n, v, selff, formtoken) {
    let form = fl2.cea("form", document.body);
    form.method = "POST";
    form.action = selff;
    fl2.ci("v", v, form);
    fl2.ci("next", 1, form);
    fl2.ci("formtoken", formtoken, form);
    form.submit();
}


function yearClick(n, y, selff, formtoken) {
    let div = fl2.cea("div", document.body);
    div.style.display = "none";
    let form = fl2.cea("form", div);
    form.method = "POST";
    form.action = selff;
    let e = fl2.ga(n, "_expand");
    if (parseInt(e) == 1)
        fl2.ci("year", 0, form);
    else
        fl2.ci("year", y, form);
    fl2.ci("formtoken", formtoken, form);
    form.submit();
}

function monthClick(n, m, selff, formtoken) {
    let div = fl2.cea("div", document.body);
    div.style.display = "none";
    let form = fl2.cea("form", div);
    form.method = "POST";
    form.action = selff;
    let e = fl2.ga(n, "_expand");
    if (parseInt(e) == 1)
        fl2.ci("month", 0, form);
    else
        fl2.ci("month", m, form);
    fl2.ci("formtoken", formtoken, form);
    form.submit();
}

function actionStations(n) {
    var v = n.value;
    var l = window.location.origin + window.location.pathname + "?v=" + v;
    window.location = l;
}


