var devtic = {
    version: 1.0,
    haveUserMedia: function () {
        return !!(navigator.mediaDevices &&
            navigator.mediaDevices.getUserMedia);
    },
    fullScreen: function (e) {
        if (e.requestFullscreen) {
        e.requestFullscreen();
        } else if (e.mozRequestFullScreen) { /* Firefox */
        e.mozRequestFullScreen();
        } else if (e.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
        e.webkitRequestFullscreen();
        } else if (e.msRequestFullscreen) { /* IE/Edge */
        e.msRequestFullscreen();
        }
    },
    videoconstraints: {
        video: {facingMode: "environment"}
    },
    vidObject: function (tag) {
        this.video = document.querySelector(tag);
        this.stream = null;
        this.callback = null;
        this.stop = function () {
        };
        this.enumerate = function (devicecallback) {
            navigator.mediaDevices.enumerateDevices()
                .then(devicecallback)
                .then(this.stop)
                .catch( (error) => {console.error("enuerateDevices Error:",error); });
        };
        this.start = function (callback) {
            if (callback)
                this.callback = callback;
            navigator.mediaDevices.getUserMedia(devtic.videoconstraints).
                then((stream) => {
                    this.video.srcObject = stream
                    this.stream = stream;
                    if (this.callback)
                        this.callback(true);
                    }
                );
        };
        this.capture = function (canvas) {
            canvas.width = this.video.videoWidth;
            canvas.height = this.video.videoHeight;
            canvas.getContext('2d').drawImage(this.video, 0, 0);
        };
        this.getTracks = function () {
            if (this.stream)
                return this.stream.getVideoTracks();
            return null;
        };

    },

    apiJSON: function (host, base, key, useHTTPS) {

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
                    this.http.open("GET", this.strHttp + this.host + "/" + this.base + "/"  + entry['command'], true);
                    this.http.send();
                }
                if (entry['method'].toUpperCase() == 'POST') {
                    this.http.open("POST", this.strHttp + this.host + "/" + this.base + "/"  + entry['command'], true);
                    this.http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                    this.http.send(JSON.stringify(entry['params']));
                }
                if (entry['method'].toUpperCase() == 'PUT') {
                    this.http.open("PUT", this.strHttp + this.host + "/" + this.base + "/"  + entry['command'], true);
                    this.http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                    this.http.send(JSON.stringify(entry['params']));
                }
            }
        }

        server.onreadystatechange = function () {
            if (server.readyState == 4 && server.status == 200) {
                var s = JSON.parse(server.responseText);
                try { self.parseReply(s); } catch (e) { mi6.logError(e) };
                self.replyRcvd();
            }
        }

        this.onTimer = function () {
            //Checks the queue
            for (var z = 0; z < self.reqQueue.length; z++) {
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
    },

};