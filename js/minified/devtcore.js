var devt={version:2,def:function(e){return void 0!==e},pad:function(e,t){for(var n=e+"";n.length<t;)n="0"+n;return n},nf:function(e,t){var n=Math.pow(10,t);return Math.round(parseFloat(e)*n)/n},isWholeNum:function(e){return e-Math.floor(e)==0},tedit:function(e,t,n){var r=e.parentElement,i=document.createElement("INPUT");i.value=e.innerHTML,devt.def(n.input.size)&&devt.sa(i,"size",n.input.size),devt.sa(i,"onkeydown","devt.tede(this)"),devt.sa(i,"original",e.innerHTML),devt.sa(i,"prevtag",e.tagName),i.callback=t,r.appendChild(i),r.removeChild(e)},tede:function(e){if("Enter"===event.key||27==event.keyCode){var t=e.parentElement,n=document.createElement(e.getAttribute("prevtag"));t.appendChild(n),devt.sa(n,"onclick","devt.tedit(this,"+e.callback.name+")"),27==event.keyCode?n.innerHTML=e.getAttribute("original"):(n.innerHTML=e.value,e.callback(e.value,n)),t.removeChild(e)}},tselect:function(e,t,n,r){var i=e.parentElement,a=document.createElement("SELECT"),s=t;(!s&&e.selectlist&&(s=e.selectlist),devt.def(r))&&((o=devt.cea("OPTION",a)).value=0,o.innerHTML=r);for(var d=0;d<s.length;d++){var o;(o=devt.cea("OPTION",a)).value=s[d].id,o.innerHTML=s[d].name}a.value=e.innerHTML,devt.sa(a,"onchange","devt.tselec(this)"),devt.sa(a,"original",e.innerHTML),devt.sa(a,"prevtag",e.tagName),a.callback=n,a.selectlist=t,i.appendChild(a),i.removeChild(e)},tselec:function(e){var t=e.parentElement,n=document.createElement(e.getAttribute("prevtag"));devt.sa(n,"onclick","devt.tselect(this,null,"+e.callback.name+")"),n.innerHTML=e.getAttribute("original"),n.selectlist=e.selectlist,e.callback(e.value),t.appendChild(n),t.removeChild(e)},copyclip:function(e){var t=document.getElementsByTagName("BODY")[0],n=this.cea("TEXTAREA",t);n.value=e,n.select(),document.execCommand("copy"),t.removeChild(n)},ge:function(e){return document.getElementById(e)},ce:function(e){return document.createElement(e)},cea:function(e,t){var n=devt.ce(e);return t.appendChild(n),n},ga:function(e,t){return e.getAttribute(t)},sa:function(e,t,n){e.setAttribute(t,n)},gebt:function(e){return document.getElementsByTagName(e)},loadScript:function(e,t){for(var n=devt.gebt("head"),r=0;r<n.length;r++)for(var i=devt.gebt("script"),a=0;a<i.length;a++)if(i[a].src==e)return;(i=devt.ce("script")).type="text/javascript",i.src=e,devt.def(t)&&(i.onreadystatechange=t,i.onload=t),devt.gebt("head")[0].appendChild(i)},removeAllChildren:function(e){for(;e.firstChild;)e.removeChild(e.firstChild)},apiJSON:function(e,t,n,r){var i=this,a=new XMLHttpRequest;this.strHttp="http://",devt.def(r)&&r&&(this.strHttp="https://"),this.strHttp+=e,devt.def(t)&&0<t.length&&(this.strHttp+="/"+t),devt.def(n)&&0<n.length&&(this.strHttp+="/"+n),this.http=a,this.reqQueue=[],this.queueReq=function(e,t,n){var r={};r.method=e,r.command=t,r.params=n,r.timestamp=Date.now(),this.reqQueue.push(r),1==this.reqQueue.length?this.serverSend():this.onCheck()},this.replyRcvd=function(){this.reqQueue.shift(),0<this.reqQueue.length&&this.serverSend()},this.serverSend=function(){1<=this.reqQueue.length&&(entry=this.reqQueue[0],"GET"==entry.method.toUpperCase()&&(this.http.open("GET",this.strHttp+"/"+entry.command,!0),this.http.send()),"POST"==entry.method.toUpperCase()&&(this.http.open("POST",this.strHttp+"/"+entry.command,!0),this.http.setRequestHeader("Content-Type","application/json;charset=UTF-8"),this.http.send(JSON.stringify(entry.params))),"PUT"==entry.method.toUpperCase()&&(this.http.open("PUT",this.strHttp+"/"+entry.command,!0),this.http.setRequestHeader("Content-Type","application/json;charset=UTF-8"),this.http.send(JSON.stringify(entry.params))))},a.onreadystatechange=function(){if(4==a.readyState&&200==a.status){var e=JSON.parse(a.responseText);strdebug=a.responseText,i.parseReply(e),i.replyRcvd()}},this.onCheck=function(){for(var e=0;e<i.reqQueue.length;e++){entry=i.reqQueue[e];var t=new Date;entry.timestamp+3e4<t.getTime()&&(console.log("remove from queue timeout "+entry.command),i.reqQueue.shift(),0<i.reqQueue.length&&i.serverSend())}}}};