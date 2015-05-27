(function(){(function(){var t,e,n,r,i,o,s,a,u,l,c,h,p,d,f,g,m,y,w,v,b,x,S,k,A,T,q,P,C,R,E,_,L,H,z,W,O,j,F,N,M,Q,U,D,X,B,G,I,K,Z=[].slice,J={}.hasOwnProperty,V=function(t,e){function n(){this.constructor=t}for(var r in e)J.call(e,r)&&(t[r]=e[r]);return n.prototype=e.prototype,t.prototype=new n,t.__super__=e.prototype,t},$=[].indexOf||function(t){for(var e=0,n=this.length;n>e;e++)if(e in this&&this[e]===t)return e;return-1};for(b={catchupTime:100,initialRate:.03,minTime:250,ghostTime:100,maxProgressPerFrame:20,easeFactor:1.25,startOnPageLoad:!0,restartOnPushState:!0,restartOnRequestAfter:500,target:"body",elements:{checkInterval:100,selectors:["body"]},eventLag:{minSamples:10,sampleCount:3,lagThreshold:3},ajax:{trackMethods:["GET"],trackWebSockets:!0,ignoreURLs:[]}},C=function(){var t;return null!=(t="undefined"!=typeof performance&&null!==performance&&"function"==typeof performance.now?performance.now():void 0)?t:+new Date},E=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||window.msRequestAnimationFrame,v=window.cancelAnimationFrame||window.mozCancelAnimationFrame,null==E&&(E=function(t){return setTimeout(t,50)},v=function(t){return clearTimeout(t)}),L=function(t){var e,n;return e=C(),(n=function(){var r;return r=C()-e,r>=33?(e=C(),t(r,function(){return E(n)})):setTimeout(n,33-r)})()},_=function(){var t,e,n;return n=arguments[0],e=arguments[1],t=3<=arguments.length?Z.call(arguments,2):[],"function"==typeof n[e]?n[e].apply(n,t):n[e]},x=function(){var t,e,n,r,i,o,s;for(e=arguments[0],r=2<=arguments.length?Z.call(arguments,1):[],o=0,s=r.length;s>o;o++)if(n=r[o])for(t in n)J.call(n,t)&&(i=n[t],null!=e[t]&&"object"==typeof e[t]&&null!=i&&"object"==typeof i?x(e[t],i):e[t]=i);return e},m=function(t){var e,n,r,i,o;for(n=e=0,i=0,o=t.length;o>i;i++)r=t[i],n+=Math.abs(r),e++;return n/e},k=function(t,e){var n,r,i;if(null==t&&(t="options"),null==e&&(e=!0),i=document.querySelector("[data-pace-"+t+"]")){if(n=i.getAttribute("data-pace-"+t),!e)return n;try{return JSON.parse(n)}catch(o){return r=o,"undefined"!=typeof console&&null!==console?console.error("Error parsing inline pace options",r):void 0}}},s=function(){function t(){}return t.prototype.on=function(t,e,n,r){var i;return null==r&&(r=!1),null==this.bindings&&(this.bindings={}),null==(i=this.bindings)[t]&&(i[t]=[]),this.bindings[t].push({handler:e,ctx:n,once:r})},t.prototype.once=function(t,e,n){return this.on(t,e,n,!0)},t.prototype.off=function(t,e){var n,r,i;if(null!=(null!=(r=this.bindings)?r[t]:void 0)){if(null==e)return delete this.bindings[t];for(n=0,i=[];n<this.bindings[t].length;)i.push(this.bindings[t][n].handler===e?this.bindings[t].splice(n,1):n++);return i}},t.prototype.trigger=function(){var t,e,n,r,i,o,s,a,u;if(n=arguments[0],t=2<=arguments.length?Z.call(arguments,1):[],null!=(s=this.bindings)?s[n]:void 0){for(i=0,u=[];i<this.bindings[n].length;)a=this.bindings[n][i],r=a.handler,e=a.ctx,o=a.once,r.apply(null!=e?e:this,t),u.push(o?this.bindings[n].splice(i,1):i++);return u}},t}(),l=window.Pace||{},window.Pace=l,x(l,s.prototype),R=l.options=x({},b,window.paceOptions,k()),G=["ajax","document","eventLag","elements"],U=0,X=G.length;X>U;U++)O=G[U],R[O]===!0&&(R[O]=b[O]);u=function(t){function e(){return I=e.__super__.constructor.apply(this,arguments)}return V(e,t),e}(Error),e=function(){function t(){this.progress=0}return t.prototype.getElement=function(){var t;if(null==this.el){if(t=document.querySelector(R.target),!t)throw new u;this.el=document.createElement("div"),this.el.className="pace pace-active",document.body.className=document.body.className.replace(/pace-done/g,""),document.body.className+=" pace-running",this.el.innerHTML='<div class="pace-progress">\n  <div class="pace-progress-inner"></div>\n</div>\n<div class="pace-activity"></div>',null!=t.firstChild?t.insertBefore(this.el,t.firstChild):t.appendChild(this.el)}return this.el},t.prototype.finish=function(){var t;return t=this.getElement(),t.className=t.className.replace("pace-active",""),t.className+=" pace-inactive",document.body.className=document.body.className.replace("pace-running",""),document.body.className+=" pace-done"},t.prototype.update=function(t){return this.progress=t,this.render()},t.prototype.destroy=function(){try{this.getElement().parentNode.removeChild(this.getElement())}catch(t){u=t}return this.el=void 0},t.prototype.render=function(){var t,e,n,r,i,o,s;if(null==document.querySelector(R.target))return!1;for(t=this.getElement(),r="translate3d("+this.progress+"%, 0, 0)",s=["webkitTransform","msTransform","transform"],i=0,o=s.length;o>i;i++)e=s[i],t.children[0].style[e]=r;return(!this.lastRenderedProgress||this.lastRenderedProgress|0!==this.progress|0)&&(t.children[0].setAttribute("data-progress-text",""+(0|this.progress)+"%"),this.progress>=100?n="99":(n=this.progress<10?"0":"",n+=0|this.progress),t.children[0].setAttribute("data-progress",""+n)),this.lastRenderedProgress=this.progress},t.prototype.done=function(){return this.progress>=100},t}(),a=function(){function t(){this.bindings={}}return t.prototype.trigger=function(t,e){var n,r,i,o,s;if(null!=this.bindings[t]){for(o=this.bindings[t],s=[],r=0,i=o.length;i>r;r++)n=o[r],s.push(n.call(this,e));return s}},t.prototype.on=function(t,e){var n;return null==(n=this.bindings)[t]&&(n[t]=[]),this.bindings[t].push(e)},t}(),Q=window.XMLHttpRequest,M=window.XDomainRequest,N=window.WebSocket,S=function(t,e){var n,r,i,o;o=[];for(r in e.prototype)try{i=e.prototype[r],o.push(null==t[r]&&"function"!=typeof i?t[r]=i:void 0)}catch(s){n=s}return o},q=[],l.ignore=function(){var t,e,n;return e=arguments[0],t=2<=arguments.length?Z.call(arguments,1):[],q.unshift("ignore"),n=e.apply(null,t),q.shift(),n},l.track=function(){var t,e,n;return e=arguments[0],t=2<=arguments.length?Z.call(arguments,1):[],q.unshift("track"),n=e.apply(null,t),q.shift(),n},W=function(t){var e;if(null==t&&(t="GET"),"track"===q[0])return"force";if(!q.length&&R.ajax){if("socket"===t&&R.ajax.trackWebSockets)return!0;if(e=t.toUpperCase(),$.call(R.ajax.trackMethods,e)>=0)return!0}return!1},c=function(t){function e(){var t,n=this;e.__super__.constructor.apply(this,arguments),t=function(t){var e;return e=t.open,t.open=function(r,i){return W(r)&&n.trigger("request",{type:r,url:i,request:t}),e.apply(t,arguments)}},window.XMLHttpRequest=function(e){var n;return n=new Q(e),t(n),n};try{S(window.XMLHttpRequest,Q)}catch(r){}if(null!=M){window.XDomainRequest=function(){var e;return e=new M,t(e),e};try{S(window.XDomainRequest,M)}catch(r){}}if(null!=N&&R.ajax.trackWebSockets){window.WebSocket=function(t,e){var r;return r=null!=e?new N(t,e):new N(t),W("socket")&&n.trigger("request",{type:"socket",url:t,protocols:e,request:r}),r};try{S(window.WebSocket,N)}catch(r){}}}return V(e,t),e}(a),D=null,A=function(){return null==D&&(D=new c),D},z=function(t){var e,n,r,i;for(i=R.ajax.ignoreURLs,n=0,r=i.length;r>n;n++)if(e=i[n],"string"==typeof e){if(-1!==t.indexOf(e))return!0}else if(e.test(t))return!0;return!1},A().on("request",function(e){var n,r,i,o,s;return o=e.type,i=e.request,s=e.url,z(s)?void 0:l.running||R.restartOnRequestAfter===!1&&"force"!==W(o)?void 0:(r=arguments,n=R.restartOnRequestAfter||0,"boolean"==typeof n&&(n=0),setTimeout(function(){var e,n,s,a,u,c;if(e="socket"===o?i.readyState<2:0<(a=i.readyState)&&4>a){for(l.restart(),u=l.sources,c=[],n=0,s=u.length;s>n;n++){if(O=u[n],O instanceof t){O.watch.apply(O,r);break}c.push(void 0)}return c}},n))}),t=function(){function t(){var t=this;this.elements=[],A().on("request",function(){return t.watch.apply(t,arguments)})}return t.prototype.watch=function(t){var e,n,r,i;return r=t.type,e=t.request,i=t.url,z(i)?void 0:(n="socket"===r?new d(e):new f(e),this.elements.push(n))},t}(),f=function(){function t(t){var e,n,r,i,o,s,a=this;if(this.progress=0,null!=window.ProgressEvent)for(n=null,t.addEventListener("progress",function(t){return a.progress=t.lengthComputable?100*t.loaded/t.total:a.progress+(100-a.progress)/2},!1),s=["load","abort","timeout","error"],r=0,i=s.length;i>r;r++)e=s[r],t.addEventListener(e,function(){return a.progress=100},!1);else o=t.onreadystatechange,t.onreadystatechange=function(){var e;return 0===(e=t.readyState)||4===e?a.progress=100:3===t.readyState&&(a.progress=50),"function"==typeof o?o.apply(null,arguments):void 0}}return t}(),d=function(){function t(t){var e,n,r,i,o=this;for(this.progress=0,i=["error","open"],n=0,r=i.length;r>n;n++)e=i[n],t.addEventListener(e,function(){return o.progress=100},!1)}return t}(),r=function(){function t(t){var e,n,r,o;for(null==t&&(t={}),this.elements=[],null==t.selectors&&(t.selectors=[]),o=t.selectors,n=0,r=o.length;r>n;n++)e=o[n],this.elements.push(new i(e))}return t}(),i=function(){function t(t){this.selector=t,this.progress=0,this.check()}return t.prototype.check=function(){var t=this;return document.querySelector(this.selector)?this.done():setTimeout(function(){return t.check()},R.elements.checkInterval)},t.prototype.done=function(){return this.progress=100},t}(),n=function(){function t(){var t,e,n=this;this.progress=null!=(e=this.states[document.readyState])?e:100,t=document.onreadystatechange,document.onreadystatechange=function(){return null!=n.states[document.readyState]&&(n.progress=n.states[document.readyState]),"function"==typeof t?t.apply(null,arguments):void 0}}return t.prototype.states={loading:0,interactive:50,complete:100},t}(),o=function(){function t(){var t,e,n,r,i,o=this;this.progress=0,t=0,i=[],r=0,n=C(),e=setInterval(function(){var s;return s=C()-n-50,n=C(),i.push(s),i.length>R.eventLag.sampleCount&&i.shift(),t=m(i),++r>=R.eventLag.minSamples&&t<R.eventLag.lagThreshold?(o.progress=100,clearInterval(e)):o.progress=100*(3/(t+3))},50)}return t}(),p=function(){function t(t){this.source=t,this.last=this.sinceLastUpdate=0,this.rate=R.initialRate,this.catchup=0,this.progress=this.lastProgress=0,null!=this.source&&(this.progress=_(this.source,"progress"))}return t.prototype.tick=function(t,e){var n;return null==e&&(e=_(this.source,"progress")),e>=100&&(this.done=!0),e===this.last?this.sinceLastUpdate+=t:(this.sinceLastUpdate&&(this.rate=(e-this.last)/this.sinceLastUpdate),this.catchup=(e-this.progress)/R.catchupTime,this.sinceLastUpdate=0,this.last=e),e>this.progress&&(this.progress+=this.catchup*t),n=1-Math.pow(this.progress/100,R.easeFactor),this.progress+=n*this.rate*t,this.progress=Math.min(this.lastProgress+R.maxProgressPerFrame,this.progress),this.progress=Math.max(0,this.progress),this.progress=Math.min(100,this.progress),this.lastProgress=this.progress,this.progress},t}(),j=null,H=null,y=null,F=null,g=null,w=null,l.running=!1,T=function(){return R.restartOnPushState?l.restart():void 0},null!=window.history.pushState&&(B=window.history.pushState,window.history.pushState=function(){return T(),B.apply(window.history,arguments)}),null!=window.history.replaceState&&(K=window.history.replaceState,window.history.replaceState=function(){return T(),K.apply(window.history,arguments)}),h={ajax:t,elements:r,document:n,eventLag:o},(P=function(){var t,n,r,i,o,s,a,u;for(l.sources=j=[],s=["ajax","elements","document","eventLag"],n=0,i=s.length;i>n;n++)t=s[n],R[t]!==!1&&j.push(new h[t](R[t]));for(u=null!=(a=R.extraSources)?a:[],r=0,o=u.length;o>r;r++)O=u[r],j.push(new O(R));return l.bar=y=new e,H=[],F=new p})(),l.stop=function(){return l.trigger("stop"),l.running=!1,y.destroy(),w=!0,null!=g&&("function"==typeof v&&v(g),g=null),P()},l.restart=function(){return l.trigger("restart"),l.stop(),l.start()},l.go=function(){var t;return l.running=!0,y.render(),t=C(),w=!1,g=L(function(e,n){var r,i,o,s,a,u,c,h,d,f,g,m,v,b,x,S;for(h=100-y.progress,i=g=0,o=!0,u=m=0,b=j.length;b>m;u=++m)for(O=j[u],f=null!=H[u]?H[u]:H[u]=[],a=null!=(S=O.elements)?S:[O],c=v=0,x=a.length;x>v;c=++v)s=a[c],d=null!=f[c]?f[c]:f[c]=new p(s),o&=d.done,d.done||(i++,g+=d.tick(e));return r=g/i,y.update(F.tick(e,r)),y.done()||o||w?(y.update(100),l.trigger("done"),setTimeout(function(){return y.finish(),l.running=!1,l.trigger("hide")},Math.max(R.ghostTime,Math.max(R.minTime-(C()-t),0)))):n()})},l.start=function(t){x(R,t),l.running=!0;try{y.render()}catch(e){u=e}return document.querySelector(".pace")?(l.trigger("start"),l.go()):setTimeout(l.start,50)},"function"==typeof define&&define.amd?define(function(){return l}):"object"==typeof exports?module.exports=l:R.startOnPageLoad&&l.start()}).call(this),!function(){"use strict";function t(r){if(!r)throw new Error("No options passed to Waypoint constructor");if(!r.element)throw new Error("No element option passed to Waypoint constructor");if(!r.handler)throw new Error("No handler option passed to Waypoint constructor");this.key="waypoint-"+e,this.options=t.Adapter.extend({},t.defaults,r),this.element=this.options.element,this.adapter=new t.Adapter(this.element),this.callback=r.handler,this.axis=this.options.horizontal?"horizontal":"vertical",this.enabled=this.options.enabled,this.triggerPoint=null,this.group=t.Group.findOrCreate({name:this.options.group,axis:this.axis}),this.context=t.Context.findOrCreateByElement(this.options.context),t.offsetAliases[this.options.offset]&&(this.options.offset=t.offsetAliases[this.options.offset]),this.group.add(this),this.context.add(this),n[this.key]=this,e+=1}var e=0,n={};t.prototype.queueTrigger=function(t){this.group.queueTrigger(this,t)},t.prototype.trigger=function(t){this.enabled&&this.callback&&this.callback.apply(this,t)},t.prototype.destroy=function(){this.context.remove(this),this.group.remove(this),delete n[this.key]},t.prototype.disable=function(){return this.enabled=!1,this},t.prototype.enable=function(){return this.context.refresh(),this.enabled=!0,this},t.prototype.next=function(){return this.group.next(this)},t.prototype.previous=function(){return this.group.previous(this)},t.invokeAll=function(t){var e=[];for(var r in n)e.push(n[r]);for(var i=0,o=e.length;o>i;i++)e[i][t]()},t.destroyAll=function(){t.invokeAll("destroy")},t.disableAll=function(){t.invokeAll("disable")},t.enableAll=function(){t.invokeAll("enable")},t.refreshAll=function(){t.Context.refreshAll()},t.viewportHeight=function(){return window.innerHeight||document.documentElement.clientHeight},t.viewportWidth=function(){return document.documentElement.clientWidth},t.adapters=[],t.defaults={context:window,continuous:!0,enabled:!0,group:"default",horizontal:!1,offset:0},t.offsetAliases={"bottom-in-view":function(){return this.context.innerHeight()-this.adapter.outerHeight()},"right-in-view":function(){return this.context.innerWidth()-this.adapter.outerWidth()}},window.Waypoint=t}(),function(){"use strict";function t(t){window.setTimeout(t,1e3/60)}function e(t){this.element=t,this.Adapter=i.Adapter,this.adapter=new this.Adapter(t),this.key="waypoint-context-"+n,this.didScroll=!1,this.didResize=!1,this.oldScroll={x:this.adapter.scrollLeft(),y:this.adapter.scrollTop()},this.waypoints={vertical:{},horizontal:{}},t.waypointContextKey=this.key,r[t.waypointContextKey]=this,n+=1,this.createThrottledScrollHandler(),this.createThrottledResizeHandler()}var n=0,r={},i=window.Waypoint,o=window.onload;e.prototype.add=function(t){var e=t.options.horizontal?"horizontal":"vertical";this.waypoints[e][t.key]=t,this.refresh()},e.prototype.checkEmpty=function(){var t=this.Adapter.isEmptyObject(this.waypoints.horizontal),e=this.Adapter.isEmptyObject(this.waypoints.vertical);t&&e&&(this.adapter.off(".waypoints"),delete r[this.key])},e.prototype.createThrottledResizeHandler=function(){function t(){e.handleResize(),e.didResize=!1}var e=this;this.adapter.on("resize.waypoints",function(){e.didResize||(e.didResize=!0,i.requestAnimationFrame(t))})},e.prototype.createThrottledScrollHandler=function(){function t(){e.handleScroll(),e.didScroll=!1}var e=this;this.adapter.on("scroll.waypoints",function(){(!e.didScroll||i.isTouch)&&(e.didScroll=!0,i.requestAnimationFrame(t))})},e.prototype.handleResize=function(){i.Context.refreshAll()},e.prototype.handleScroll=function(){var t={},e={horizontal:{newScroll:this.adapter.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.adapter.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};for(var n in e){var r=e[n],i=r.newScroll>r.oldScroll,o=i?r.forward:r.backward;for(var s in this.waypoints[n]){var a=this.waypoints[n][s],u=r.oldScroll<a.triggerPoint,l=r.newScroll>=a.triggerPoint,c=u&&l,h=!u&&!l;(c||h)&&(a.queueTrigger(o),t[a.group.id]=a.group)}}for(var p in t)t[p].flushTriggers();this.oldScroll={x:e.horizontal.newScroll,y:e.vertical.newScroll}},e.prototype.innerHeight=function(){return this.element==this.element.window?i.viewportHeight():this.adapter.innerHeight()},e.prototype.remove=function(t){delete this.waypoints[t.axis][t.key],this.checkEmpty()},e.prototype.innerWidth=function(){return this.element==this.element.window?i.viewportWidth():this.adapter.innerWidth()},e.prototype.destroy=function(){var t=[];for(var e in this.waypoints)for(var n in this.waypoints[e])t.push(this.waypoints[e][n]);for(var r=0,i=t.length;i>r;r++)t[r].destroy()},e.prototype.refresh=function(){var t,e=this.element==this.element.window,n=this.adapter.offset(),r={};this.handleScroll(),t={horizontal:{contextOffset:e?0:n.left,contextScroll:e?0:this.oldScroll.x,contextDimension:this.innerWidth(),oldScroll:this.oldScroll.x,forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:e?0:n.top,contextScroll:e?0:this.oldScroll.y,contextDimension:this.innerHeight(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};for(var i in t){var o=t[i];for(var s in this.waypoints[i]){var a,u,l,c,h,p=this.waypoints[i][s],d=p.options.offset,f=p.triggerPoint,g=0,m=null==f;p.element!==p.element.window&&(g=p.adapter.offset()[o.offsetProp]),"function"==typeof d?d=d.apply(p):"string"==typeof d&&(d=parseFloat(d),p.options.offset.indexOf("%")>-1&&(d=Math.ceil(o.contextDimension*d/100))),a=o.contextScroll-o.contextOffset,p.triggerPoint=g+a-d,u=f<o.oldScroll,l=p.triggerPoint>=o.oldScroll,c=u&&l,h=!u&&!l,!m&&c?(p.queueTrigger(o.backward),r[p.group.id]=p.group):!m&&h?(p.queueTrigger(o.forward),r[p.group.id]=p.group):m&&o.oldScroll>=p.triggerPoint&&(p.queueTrigger(o.forward),r[p.group.id]=p.group)}}for(var y in r)r[y].flushTriggers();return this},e.findOrCreateByElement=function(t){return e.findByElement(t)||new e(t)},e.refreshAll=function(){for(var t in r)r[t].refresh()},e.findByElement=function(t){return r[t.waypointContextKey]},window.onload=function(){o&&o(),e.refreshAll()},i.requestAnimationFrame=function(e){var n=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||t;n.call(window,e)},i.Context=e}(),function(){"use strict";function t(t,e){return t.triggerPoint-e.triggerPoint}function e(t,e){return e.triggerPoint-t.triggerPoint}function n(t){this.name=t.name,this.axis=t.axis,this.id=this.name+"-"+this.axis,this.waypoints=[],this.clearTriggerQueues(),r[this.axis][this.name]=this}var r={vertical:{},horizontal:{}},i=window.Waypoint;n.prototype.add=function(t){this.waypoints.push(t)},n.prototype.clearTriggerQueues=function(){this.triggerQueues={up:[],down:[],left:[],right:[]}},n.prototype.flushTriggers=function(){for(var n in this.triggerQueues){var r=this.triggerQueues[n],i="up"===n||"left"===n;r.sort(i?e:t);for(var o=0,s=r.length;s>o;o+=1){var a=r[o];(a.options.continuous||o===r.length-1)&&a.trigger([n])}}this.clearTriggerQueues()},n.prototype.next=function(e){this.waypoints.sort(t);var n=i.Adapter.inArray(e,this.waypoints),r=n===this.waypoints.length-1;return r?null:this.waypoints[n+1]},n.prototype.previous=function(e){this.waypoints.sort(t);var n=i.Adapter.inArray(e,this.waypoints);return n?this.waypoints[n-1]:null},n.prototype.queueTrigger=function(t,e){this.triggerQueues[e].push(t)},n.prototype.remove=function(t){var e=i.Adapter.inArray(t,this.waypoints);e>-1&&this.waypoints.splice(e,1)},n.prototype.first=function(){return this.waypoints[0]},n.prototype.last=function(){return this.waypoints[this.waypoints.length-1]},n.findOrCreate=function(t){return r[t.axis][t.name]||new n(t)},i.Group=n}(),function(){"use strict";function t(t){this.$element=e(t)}var e=window.jQuery,n=window.Waypoint;e.each(["innerHeight","innerWidth","off","offset","on","outerHeight","outerWidth","scrollLeft","scrollTop"],function(e,n){t.prototype[n]=function(){var t=Array.prototype.slice.call(arguments);return this.$element[n].apply(this.$element,t)}}),e.each(["extend","inArray","isEmptyObject"],function(n,r){t[r]=e[r]}),n.adapters.push({name:"jquery",Adapter:t}),n.Adapter=t}(),function(){"use strict";function t(t){return function(){var n=[],r=arguments[0];return t.isFunction(arguments[0])&&(r=t.extend({},arguments[1]),r.handler=arguments[0]),this.each(function(){var i=t.extend({},r,{element:this});"string"==typeof i.context&&(i.context=t(this).closest(i.context)[0]),n.push(new e(i))}),n}}var e=window.Waypoint;window.jQuery&&(window.jQuery.fn.waypoint=t(window.jQuery)),window.Zepto&&(window.Zepto.fn.waypoint=t(window.Zepto))}(),$(function(){return Pace.on("done",function(){var t,e,n,r,i,o,s;$("body").removeClass("is-loading"),t=function(t,e){return setTimeout(e,t)},e=$(".section_grid ul li:nth-child(1)"),n=$(".section_grid ul li:nth-child(2)"),r=$(".section_grid ul li:nth-child(3)"),i=$(".section_grid ul li:nth-child(4)"),o=$(".section_grid .section_header"),s=$(".section_text"),e.addClass("animated"),t(150,function(){return n.addClass("animated")}),t(300,function(){return r.addClass("animated")}),t(450,function(){return i.addClass("animated")}),t(750,function(){return o.addClass("animated")}),t(1050,function(){return s.addClass("animated")}),$(".pace").remove()})}),$(function(){var t,e;return t=$("header"),e=t.outerHeight(),$("main").css("margin-top",e),$(window).on("resize",function(){return e=t.outerHeight(),$("main").css("margin-top",e)})}),$(function(){var t,e,n,r,i,o;return e=$(".section_domain"),i=$(".section_domain .top"),t=$(".section_domain .bottom"),n=$("header"),o=window.innerHeight,r=n.outerHeight(),i.css("height",o-100),t.css("height",o-100),e.css("height",i.outerHeight()+t.outerHeight()),Pace.on("done",function(){var o;return o=e.waypoint(function(t){return i=$(this.element).children(".top"),n=$(this.element).children(".section_header"),i.toggleClass("is_pinned"),n.toggleClass("is_pinned animated")},{offset:r}),o=t.waypoint(function(t){return e=$(this.element).parent(".section_domain"),e.toggleClass("is_scrolled")},{offset:r})})})}).call(this);