function apiJSON(host, base, key, useHTTPS) {

    var self = this;
    var server = new XMLHttpRequest();

    this.host = host;
    this.base = base;
    this.key = key;
    this.strHttp = 'http://';
    if (typeof useHTTPS != 'undefined' && useHTTPS)
        this.strHttp = 'https://';
    this.http = server;
    this.reqQueue = [];

    this.queueReq = function (method, command, params) {
        var entry = {};
        entry['method'] = method;
        entry['command'] = command;
        entry['params'] = params;
        entry['timestamp'] = Date.now();
        this.reqQueue.push(entry);

        if (this.reqQueue.length == 1) {
            this.serverSend();
        }
        else
            this.onTimer();
    }

    this.replyRcvd = function () {
        this.reqQueue.shift();
        if (this.reqQueue.length > 0) {
            this.serverSend();
        }
    }

    this.serverSend = function () {

        if (this.reqQueue.length >= 1) {
            entry = this.reqQueue[0];
            if (entry['method'].toUpperCase() == 'GET') {
                if (this.key.length > 0)
                    this.http.open("GET", this.strHttp + this.host + "/" + this.base + "/" + this.key + "/" + entry['command'], true);
                else
                    this.http.open("GET", this.strHttp + this.host + "/" + this.base +  entry['command'], true);

                this.http.send();
            }
            if (entry['method'].toUpperCase() == 'POST') {
                if (this.key.length > 0)
                    this.http.open("POST", this.strHttp + this.host + "/" + this.base + "/" + this.key + "/" + entry['command'], true);
                else
                    this.http.open("POST", this.strHttp + this.host + "/" + this.base + entry['command'], true);

                this.http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                this.http.send(JSON.stringify(entry['params']));
            }
            if (entry['method'].toUpperCase() == 'PUT') {
                if (this.key.length > 0)
                    this.http.open("PUT", this.strHttp + this.host + "/" + this.base + "/" + this.key + "/" + entry['command'], true);
                else
                    this.http.open("PUT", this.strHttp + this.host + "/" + this.base + entry['command'], true);

                this.http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                this.http.send(JSON.stringify(entry['params']));
            }
        }
    }

    server.onreadystatechange = function () {
        if (server.readyState == 4 && server.status == 200) {
            var s = JSON.parse(server.responseText);
            self.parseReply(s);
            //try { self.parseReply(s); } catch (e) { console.log('parseReply Failed'); };
            self.replyRcvd();
        }
    }

    this.onTimer = function () {
        //Checks the queue
        for (var z = 0; z < self.reqQueue.length; z++ ) {
            entry = self.reqQueue[z];
            var ts = new Date();
            if (entry['timestamp'] + 30000 < ts.getTime()) {
                //this boy needs to be removed from the queue
                console.log("remove from queue timeout " + entry['command']);
                self.reqQueue.shift();
                if (self.reqQueue.length > 0) {
                    self.serverSend();
                }
            }
        }

    }
        
    //setInterval(this.onTimer, 15000);
}