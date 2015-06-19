(function(){(function(){var t,e,n,r,i,o,s,a,u,c,l,h,p,d,f,g,m,v,y,w,b,x,k,E,S,A,L,q,C,T,_,P,j,O,R,W,H,z,F,N,D,M,Q,I,B,U,V,X,G,K=[].slice,Z={}.hasOwnProperty,J=function(t,e){function n(){this.constructor=t}for(var r in e)Z.call(e,r)&&(t[r]=e[r]);return n.prototype=e.prototype,t.prototype=new n,t.__super__=e.prototype,t},$=[].indexOf||function(t){for(var e=0,n=this.length;n>e;e++)if(e in this&&this[e]===t)return e;return-1};for(b={catchupTime:100,initialRate:.03,minTime:250,ghostTime:100,maxProgressPerFrame:20,easeFactor:1.25,startOnPageLoad:!0,restartOnPushState:!0,restartOnRequestAfter:500,target:"body",elements:{checkInterval:100,selectors:["body"]},eventLag:{minSamples:10,sampleCount:3,lagThreshold:3},ajax:{trackMethods:["GET"],trackWebSockets:!0,ignoreURLs:[]}},C=function(){var t;return null!=(t="undefined"!=typeof performance&&null!==performance&&"function"==typeof performance.now?performance.now():void 0)?t:+new Date},_=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||window.msRequestAnimationFrame,w=window.cancelAnimationFrame||window.mozCancelAnimationFrame,null==_&&(_=function(t){return setTimeout(t,50)},w=function(t){return clearTimeout(t)}),j=function(t){var e,n;return e=C(),(n=function(){var r;return r=C()-e,r>=33?(e=C(),t(r,function(){return _(n)})):setTimeout(n,33-r)})()},P=function(){var t,e,n;return n=arguments[0],e=arguments[1],t=3<=arguments.length?K.call(arguments,2):[],"function"==typeof n[e]?n[e].apply(n,t):n[e]},x=function(){var t,e,n,r,i,o,s;for(e=arguments[0],r=2<=arguments.length?K.call(arguments,1):[],o=0,s=r.length;s>o;o++)if(n=r[o])for(t in n)Z.call(n,t)&&(i=n[t],null!=e[t]&&"object"==typeof e[t]&&null!=i&&"object"==typeof i?x(e[t],i):e[t]=i);return e},m=function(t){var e,n,r,i,o;for(n=e=0,i=0,o=t.length;o>i;i++)r=t[i],n+=Math.abs(r),e++;return n/e},E=function(t,e){var n,r,i;if(null==t&&(t="options"),null==e&&(e=!0),i=document.querySelector("[data-pace-"+t+"]")){if(n=i.getAttribute("data-pace-"+t),!e)return n;try{return JSON.parse(n)}catch(o){return r=o,"undefined"!=typeof console&&null!==console?console.error("Error parsing inline pace options",r):void 0}}},s=function(){function t(){}return t.prototype.on=function(t,e,n,r){var i;return null==r&&(r=!1),null==this.bindings&&(this.bindings={}),null==(i=this.bindings)[t]&&(i[t]=[]),this.bindings[t].push({handler:e,ctx:n,once:r})},t.prototype.once=function(t,e,n){return this.on(t,e,n,!0)},t.prototype.off=function(t,e){var n,r,i;if(null!=(null!=(r=this.bindings)?r[t]:void 0)){if(null==e)return delete this.bindings[t];for(n=0,i=[];n<this.bindings[t].length;)i.push(this.bindings[t][n].handler===e?this.bindings[t].splice(n,1):n++);return i}},t.prototype.trigger=function(){var t,e,n,r,i,o,s,a,u;if(n=arguments[0],t=2<=arguments.length?K.call(arguments,1):[],null!=(s=this.bindings)?s[n]:void 0){for(i=0,u=[];i<this.bindings[n].length;)a=this.bindings[n][i],r=a.handler,e=a.ctx,o=a.once,r.apply(null!=e?e:this,t),u.push(o?this.bindings[n].splice(i,1):i++);return u}},t}(),c=window.Pace||{},window.Pace=c,x(c,s.prototype),T=c.options=x({},b,window.paceOptions,E()),V=["ajax","document","eventLag","elements"],Q=0,B=V.length;B>Q;Q++)H=V[Q],T[H]===!0&&(T[H]=b[H]);u=function(t){function e(){return X=e.__super__.constructor.apply(this,arguments)}return J(e,t),e}(Error),e=function(){function t(){this.progress=0}return t.prototype.getElement=function(){var t;if(null==this.el){if(t=document.querySelector(T.target),!t)throw new u;this.el=document.createElement("div"),this.el.className="pace pace-active",document.body.className=document.body.className.replace(/pace-done/g,""),document.body.className+=" pace-running",this.el.innerHTML='<div class="pace-progress">\n  <div class="pace-progress-inner"></div>\n</div>\n<div class="pace-activity"></div>',null!=t.firstChild?t.insertBefore(this.el,t.firstChild):t.appendChild(this.el)}return this.el},t.prototype.finish=function(){var t;return t=this.getElement(),t.className=t.className.replace("pace-active",""),t.className+=" pace-inactive",document.body.className=document.body.className.replace("pace-running",""),document.body.className+=" pace-done"},t.prototype.update=function(t){return this.progress=t,this.render()},t.prototype.destroy=function(){try{this.getElement().parentNode.removeChild(this.getElement())}catch(t){u=t}return this.el=void 0},t.prototype.render=function(){var t,e,n,r,i,o,s;if(null==document.querySelector(T.target))return!1;for(t=this.getElement(),r="translate3d("+this.progress+"%, 0, 0)",s=["webkitTransform","msTransform","transform"],i=0,o=s.length;o>i;i++)e=s[i],t.children[0].style[e]=r;return(!this.lastRenderedProgress||this.lastRenderedProgress|0!==this.progress|0)&&(t.children[0].setAttribute("data-progress-text",""+(0|this.progress)+"%"),this.progress>=100?n="99":(n=this.progress<10?"0":"",n+=0|this.progress),t.children[0].setAttribute("data-progress",""+n)),this.lastRenderedProgress=this.progress},t.prototype.done=function(){return this.progress>=100},t}(),a=function(){function t(){this.bindings={}}return t.prototype.trigger=function(t,e){var n,r,i,o,s;if(null!=this.bindings[t]){for(o=this.bindings[t],s=[],r=0,i=o.length;i>r;r++)n=o[r],s.push(n.call(this,e));return s}},t.prototype.on=function(t,e){var n;return null==(n=this.bindings)[t]&&(n[t]=[]),this.bindings[t].push(e)},t}(),M=window.XMLHttpRequest,D=window.XDomainRequest,N=window.WebSocket,k=function(t,e){var n,r,i,o;o=[];for(r in e.prototype)try{i=e.prototype[r],o.push(null==t[r]&&"function"!=typeof i?t[r]=i:void 0)}catch(s){n=s}return o},L=[],c.ignore=function(){var t,e,n;return e=arguments[0],t=2<=arguments.length?K.call(arguments,1):[],L.unshift("ignore"),n=e.apply(null,t),L.shift(),n},c.track=function(){var t,e,n;return e=arguments[0],t=2<=arguments.length?K.call(arguments,1):[],L.unshift("track"),n=e.apply(null,t),L.shift(),n},W=function(t){var e;if(null==t&&(t="GET"),"track"===L[0])return"force";if(!L.length&&T.ajax){if("socket"===t&&T.ajax.trackWebSockets)return!0;if(e=t.toUpperCase(),$.call(T.ajax.trackMethods,e)>=0)return!0}return!1},l=function(t){function e(){var t,n=this;e.__super__.constructor.apply(this,arguments),t=function(t){var e;return e=t.open,t.open=function(r,i){return W(r)&&n.trigger("request",{type:r,url:i,request:t}),e.apply(t,arguments)}},window.XMLHttpRequest=function(e){var n;return n=new M(e),t(n),n};try{k(window.XMLHttpRequest,M)}catch(r){}if(null!=D){window.XDomainRequest=function(){var e;return e=new D,t(e),e};try{k(window.XDomainRequest,D)}catch(r){}}if(null!=N&&T.ajax.trackWebSockets){window.WebSocket=function(t,e){var r;return r=null!=e?new N(t,e):new N(t),W("socket")&&n.trigger("request",{type:"socket",url:t,protocols:e,request:r}),r};try{k(window.WebSocket,N)}catch(r){}}}return J(e,t),e}(a),I=null,S=function(){return null==I&&(I=new l),I},R=function(t){var e,n,r,i;for(i=T.ajax.ignoreURLs,n=0,r=i.length;r>n;n++)if(e=i[n],"string"==typeof e){if(-1!==t.indexOf(e))return!0}else if(e.test(t))return!0;return!1},S().on("request",function(e){var n,r,i,o,s;return o=e.type,i=e.request,s=e.url,R(s)?void 0:c.running||T.restartOnRequestAfter===!1&&"force"!==W(o)?void 0:(r=arguments,n=T.restartOnRequestAfter||0,"boolean"==typeof n&&(n=0),setTimeout(function(){var e,n,s,a,u,l;if(e="socket"===o?i.readyState<2:0<(a=i.readyState)&&4>a){for(c.restart(),u=c.sources,l=[],n=0,s=u.length;s>n;n++){if(H=u[n],H instanceof t){H.watch.apply(H,r);break}l.push(void 0)}return l}},n))}),t=function(){function t(){var t=this;this.elements=[],S().on("request",function(){return t.watch.apply(t,arguments)})}return t.prototype.watch=function(t){var e,n,r,i;return r=t.type,e=t.request,i=t.url,R(i)?void 0:(n="socket"===r?new d(e):new f(e),this.elements.push(n))},t}(),f=function(){function t(t){var e,n,r,i,o,s,a=this;if(this.progress=0,null!=window.ProgressEvent)for(n=null,t.addEventListener("progress",function(t){return a.progress=t.lengthComputable?100*t.loaded/t.total:a.progress+(100-a.progress)/2},!1),s=["load","abort","timeout","error"],r=0,i=s.length;i>r;r++)e=s[r],t.addEventListener(e,function(){return a.progress=100},!1);else o=t.onreadystatechange,t.onreadystatechange=function(){var e;return 0===(e=t.readyState)||4===e?a.progress=100:3===t.readyState&&(a.progress=50),"function"==typeof o?o.apply(null,arguments):void 0}}return t}(),d=function(){function t(t){var e,n,r,i,o=this;for(this.progress=0,i=["error","open"],n=0,r=i.length;r>n;n++)e=i[n],t.addEventListener(e,function(){return o.progress=100},!1)}return t}(),r=function(){function t(t){var e,n,r,o;for(null==t&&(t={}),this.elements=[],null==t.selectors&&(t.selectors=[]),o=t.selectors,n=0,r=o.length;r>n;n++)e=o[n],this.elements.push(new i(e))}return t}(),i=function(){function t(t){this.selector=t,this.progress=0,this.check()}return t.prototype.check=function(){var t=this;return document.querySelector(this.selector)?this.done():setTimeout(function(){return t.check()},T.elements.checkInterval)},t.prototype.done=function(){return this.progress=100},t}(),n=function(){function t(){var t,e,n=this;this.progress=null!=(e=this.states[document.readyState])?e:100,t=document.onreadystatechange,document.onreadystatechange=function(){return null!=n.states[document.readyState]&&(n.progress=n.states[document.readyState]),"function"==typeof t?t.apply(null,arguments):void 0}}return t.prototype.states={loading:0,interactive:50,complete:100},t}(),o=function(){function t(){var t,e,n,r,i,o=this;this.progress=0,t=0,i=[],r=0,n=C(),e=setInterval(function(){var s;return s=C()-n-50,n=C(),i.push(s),i.length>T.eventLag.sampleCount&&i.shift(),t=m(i),++r>=T.eventLag.minSamples&&t<T.eventLag.lagThreshold?(o.progress=100,clearInterval(e)):o.progress=100*(3/(t+3))},50)}return t}(),p=function(){function t(t){this.source=t,this.last=this.sinceLastUpdate=0,this.rate=T.initialRate,this.catchup=0,this.progress=this.lastProgress=0,null!=this.source&&(this.progress=P(this.source,"progress"))}return t.prototype.tick=function(t,e){var n;return null==e&&(e=P(this.source,"progress")),e>=100&&(this.done=!0),e===this.last?this.sinceLastUpdate+=t:(this.sinceLastUpdate&&(this.rate=(e-this.last)/this.sinceLastUpdate),this.catchup=(e-this.progress)/T.catchupTime,this.sinceLastUpdate=0,this.last=e),e>this.progress&&(this.progress+=this.catchup*t),n=1-Math.pow(this.progress/100,T.easeFactor),this.progress+=n*this.rate*t,this.progress=Math.min(this.lastProgress+T.maxProgressPerFrame,this.progress),this.progress=Math.max(0,this.progress),this.progress=Math.min(100,this.progress),this.lastProgress=this.progress,this.progress},t}(),z=null,O=null,v=null,F=null,g=null,y=null,c.running=!1,A=function(){return T.restartOnPushState?c.restart():void 0},null!=window.history.pushState&&(U=window.history.pushState,window.history.pushState=function(){return A(),U.apply(window.history,arguments)}),null!=window.history.replaceState&&(G=window.history.replaceState,window.history.replaceState=function(){return A(),G.apply(window.history,arguments)}),h={ajax:t,elements:r,document:n,eventLag:o},(q=function(){var t,n,r,i,o,s,a,u;for(c.sources=z=[],s=["ajax","elements","document","eventLag"],n=0,i=s.length;i>n;n++)t=s[n],T[t]!==!1&&z.push(new h[t](T[t]));for(u=null!=(a=T.extraSources)?a:[],r=0,o=u.length;o>r;r++)H=u[r],z.push(new H(T));return c.bar=v=new e,O=[],F=new p})(),c.stop=function(){return c.trigger("stop"),c.running=!1,v.destroy(),y=!0,null!=g&&("function"==typeof w&&w(g),g=null),q()},c.restart=function(){return c.trigger("restart"),c.stop(),c.start()},c.go=function(){var t;return c.running=!0,v.render(),t=C(),y=!1,g=j(function(e,n){var r,i,o,s,a,u,l,h,d,f,g,m,w,b,x,k;for(h=100-v.progress,i=g=0,o=!0,u=m=0,b=z.length;b>m;u=++m)for(H=z[u],f=null!=O[u]?O[u]:O[u]=[],a=null!=(k=H.elements)?k:[H],l=w=0,x=a.length;x>w;l=++w)s=a[l],d=null!=f[l]?f[l]:f[l]=new p(s),o&=d.done,d.done||(i++,g+=d.tick(e));return r=g/i,v.update(F.tick(e,r)),v.done()||o||y?(v.update(100),c.trigger("done"),setTimeout(function(){return v.finish(),c.running=!1,c.trigger("hide")},Math.max(T.ghostTime,Math.max(T.minTime-(C()-t),0)))):n()})},c.start=function(t){x(T,t),c.running=!0;try{v.render()}catch(e){u=e}return document.querySelector(".pace")?(c.trigger("start"),c.go()):setTimeout(c.start,50)},"function"==typeof define&&define.amd?define(function(){return c}):"object"==typeof exports?module.exports=c:T.startOnPageLoad&&c.start()}).call(this),!function(){"use strict";function t(r){if(!r)throw new Error("No options passed to Waypoint constructor");if(!r.element)throw new Error("No element option passed to Waypoint constructor");if(!r.handler)throw new Error("No handler option passed to Waypoint constructor");this.key="waypoint-"+e,this.options=t.Adapter.extend({},t.defaults,r),this.element=this.options.element,this.adapter=new t.Adapter(this.element),this.callback=r.handler,this.axis=this.options.horizontal?"horizontal":"vertical",this.enabled=this.options.enabled,this.triggerPoint=null,this.group=t.Group.findOrCreate({name:this.options.group,axis:this.axis}),this.context=t.Context.findOrCreateByElement(this.options.context),t.offsetAliases[this.options.offset]&&(this.options.offset=t.offsetAliases[this.options.offset]),this.group.add(this),this.context.add(this),n[this.key]=this,e+=1}var e=0,n={};t.prototype.queueTrigger=function(t){this.group.queueTrigger(this,t)},t.prototype.trigger=function(t){this.enabled&&this.callback&&this.callback.apply(this,t)},t.prototype.destroy=function(){this.context.remove(this),this.group.remove(this),delete n[this.key]},t.prototype.disable=function(){return this.enabled=!1,this},t.prototype.enable=function(){return this.context.refresh(),this.enabled=!0,this},t.prototype.next=function(){return this.group.next(this)},t.prototype.previous=function(){return this.group.previous(this)},t.invokeAll=function(t){var e=[];for(var r in n)e.push(n[r]);for(var i=0,o=e.length;o>i;i++)e[i][t]()},t.destroyAll=function(){t.invokeAll("destroy")},t.disableAll=function(){t.invokeAll("disable")},t.enableAll=function(){t.invokeAll("enable")},t.refreshAll=function(){t.Context.refreshAll()},t.viewportHeight=function(){return window.innerHeight||document.documentElement.clientHeight},t.viewportWidth=function(){return document.documentElement.clientWidth},t.adapters=[],t.defaults={context:window,continuous:!0,enabled:!0,group:"default",horizontal:!1,offset:0},t.offsetAliases={"bottom-in-view":function(){return this.context.innerHeight()-this.adapter.outerHeight()},"right-in-view":function(){return this.context.innerWidth()-this.adapter.outerWidth()}},window.Waypoint=t}(),function(){"use strict";function t(t){window.setTimeout(t,1e3/60)}function e(t){this.element=t,this.Adapter=i.Adapter,this.adapter=new this.Adapter(t),this.key="waypoint-context-"+n,this.didScroll=!1,this.didResize=!1,this.oldScroll={x:this.adapter.scrollLeft(),y:this.adapter.scrollTop()},this.waypoints={vertical:{},horizontal:{}},t.waypointContextKey=this.key,r[t.waypointContextKey]=this,n+=1,this.createThrottledScrollHandler(),this.createThrottledResizeHandler()}var n=0,r={},i=window.Waypoint,o=window.onload;e.prototype.add=function(t){var e=t.options.horizontal?"horizontal":"vertical";this.waypoints[e][t.key]=t,this.refresh()},e.prototype.checkEmpty=function(){var t=this.Adapter.isEmptyObject(this.waypoints.horizontal),e=this.Adapter.isEmptyObject(this.waypoints.vertical);t&&e&&(this.adapter.off(".waypoints"),delete r[this.key])},e.prototype.createThrottledResizeHandler=function(){function t(){e.handleResize(),e.didResize=!1}var e=this;this.adapter.on("resize.waypoints",function(){e.didResize||(e.didResize=!0,i.requestAnimationFrame(t))})},e.prototype.createThrottledScrollHandler=function(){function t(){e.handleScroll(),e.didScroll=!1}var e=this;this.adapter.on("scroll.waypoints",function(){(!e.didScroll||i.isTouch)&&(e.didScroll=!0,i.requestAnimationFrame(t))})},e.prototype.handleResize=function(){i.Context.refreshAll()},e.prototype.handleScroll=function(){var t={},e={horizontal:{newScroll:this.adapter.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.adapter.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};for(var n in e){var r=e[n],i=r.newScroll>r.oldScroll,o=i?r.forward:r.backward;for(var s in this.waypoints[n]){var a=this.waypoints[n][s],u=r.oldScroll<a.triggerPoint,c=r.newScroll>=a.triggerPoint,l=u&&c,h=!u&&!c;(l||h)&&(a.queueTrigger(o),t[a.group.id]=a.group)}}for(var p in t)t[p].flushTriggers();this.oldScroll={x:e.horizontal.newScroll,y:e.vertical.newScroll}},e.prototype.innerHeight=function(){return this.element==this.element.window?i.viewportHeight():this.adapter.innerHeight()},e.prototype.remove=function(t){delete this.waypoints[t.axis][t.key],this.checkEmpty()},e.prototype.innerWidth=function(){return this.element==this.element.window?i.viewportWidth():this.adapter.innerWidth()},e.prototype.destroy=function(){var t=[];for(var e in this.waypoints)for(var n in this.waypoints[e])t.push(this.waypoints[e][n]);for(var r=0,i=t.length;i>r;r++)t[r].destroy()},e.prototype.refresh=function(){var t,e=this.element==this.element.window,n=this.adapter.offset(),r={};this.handleScroll(),t={horizontal:{contextOffset:e?0:n.left,contextScroll:e?0:this.oldScroll.x,contextDimension:this.innerWidth(),oldScroll:this.oldScroll.x,forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:e?0:n.top,contextScroll:e?0:this.oldScroll.y,contextDimension:this.innerHeight(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};for(var i in t){var o=t[i];for(var s in this.waypoints[i]){var a,u,c,l,h,p=this.waypoints[i][s],d=p.options.offset,f=p.triggerPoint,g=0,m=null==f;p.element!==p.element.window&&(g=p.adapter.offset()[o.offsetProp]),"function"==typeof d?d=d.apply(p):"string"==typeof d&&(d=parseFloat(d),p.options.offset.indexOf("%")>-1&&(d=Math.ceil(o.contextDimension*d/100))),a=o.contextScroll-o.contextOffset,p.triggerPoint=g+a-d,u=f<o.oldScroll,c=p.triggerPoint>=o.oldScroll,l=u&&c,h=!u&&!c,!m&&l?(p.queueTrigger(o.backward),r[p.group.id]=p.group):!m&&h?(p.queueTrigger(o.forward),r[p.group.id]=p.group):m&&o.oldScroll>=p.triggerPoint&&(p.queueTrigger(o.forward),r[p.group.id]=p.group)}}for(var v in r)r[v].flushTriggers();return this},e.findOrCreateByElement=function(t){return e.findByElement(t)||new e(t)},e.refreshAll=function(){for(var t in r)r[t].refresh()},e.findByElement=function(t){return r[t.waypointContextKey]},window.onload=function(){o&&o(),e.refreshAll()},i.requestAnimationFrame=function(e){var n=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||t;n.call(window,e)},i.Context=e}(),function(){"use strict";function t(t,e){return t.triggerPoint-e.triggerPoint}function e(t,e){return e.triggerPoint-t.triggerPoint}function n(t){this.name=t.name,this.axis=t.axis,this.id=this.name+"-"+this.axis,this.waypoints=[],this.clearTriggerQueues(),r[this.axis][this.name]=this}var r={vertical:{},horizontal:{}},i=window.Waypoint;n.prototype.add=function(t){this.waypoints.push(t)},n.prototype.clearTriggerQueues=function(){this.triggerQueues={up:[],down:[],left:[],right:[]}},n.prototype.flushTriggers=function(){for(var n in this.triggerQueues){var r=this.triggerQueues[n],i="up"===n||"left"===n;r.sort(i?e:t);for(var o=0,s=r.length;s>o;o+=1){var a=r[o];(a.options.continuous||o===r.length-1)&&a.trigger([n])}}this.clearTriggerQueues()},n.prototype.next=function(e){this.waypoints.sort(t);var n=i.Adapter.inArray(e,this.waypoints),r=n===this.waypoints.length-1;return r?null:this.waypoints[n+1]},n.prototype.previous=function(e){this.waypoints.sort(t);var n=i.Adapter.inArray(e,this.waypoints);return n?this.waypoints[n-1]:null},n.prototype.queueTrigger=function(t,e){this.triggerQueues[e].push(t)},n.prototype.remove=function(t){var e=i.Adapter.inArray(t,this.waypoints);e>-1&&this.waypoints.splice(e,1)},n.prototype.first=function(){return this.waypoints[0]},n.prototype.last=function(){return this.waypoints[this.waypoints.length-1]},n.findOrCreate=function(t){return r[t.axis][t.name]||new n(t)},i.Group=n}(),function(){"use strict";function t(t){this.$element=e(t)}var e=window.jQuery,n=window.Waypoint;e.each(["innerHeight","innerWidth","off","offset","on","outerHeight","outerWidth","scrollLeft","scrollTop"],function(e,n){t.prototype[n]=function(){var t=Array.prototype.slice.call(arguments);return this.$element[n].apply(this.$element,t)}}),e.each(["extend","inArray","isEmptyObject"],function(n,r){t[r]=e[r]}),n.adapters.push({name:"jquery",Adapter:t}),n.Adapter=t}(),function(){"use strict";function t(t){return function(){var n=[],r=arguments[0];return t.isFunction(arguments[0])&&(r=t.extend({},arguments[1]),r.handler=arguments[0]),this.each(function(){var i=t.extend({},r,{element:this});"string"==typeof i.context&&(i.context=t(this).closest(i.context)[0]),n.push(new e(i))}),n}}var e=window.Waypoint;window.jQuery&&(window.jQuery.fn.waypoint=t(window.jQuery)),window.Zepto&&(window.Zepto.fn.waypoint=t(window.Zepto))}(),function(){function t(){}function e(t,e){for(var n=t.length;n--;)if(t[n].listener===e)return n;return-1}function n(t){return function(){return this[t].apply(this,arguments)}}var r=t.prototype,i=this,o=i.EventEmitter;r.getListeners=function(t){var e,n,r=this._getEvents();if("object"==typeof t){e={};for(n in r)r.hasOwnProperty(n)&&t.test(n)&&(e[n]=r[n])}else e=r[t]||(r[t]=[]);return e},r.flattenListeners=function(t){var e,n=[];for(e=0;t.length>e;e+=1)n.push(t[e].listener);return n},r.getListenersAsObject=function(t){var e,n=this.getListeners(t);return n instanceof Array&&(e={},e[t]=n),e||n},r.addListener=function(t,n){var r,i=this.getListenersAsObject(t),o="object"==typeof n;for(r in i)i.hasOwnProperty(r)&&-1===e(i[r],n)&&i[r].push(o?n:{listener:n,once:!1});return this},r.on=n("addListener"),r.addOnceListener=function(t,e){return this.addListener(t,{listener:e,once:!0})},r.once=n("addOnceListener"),r.defineEvent=function(t){return this.getListeners(t),this},r.defineEvents=function(t){for(var e=0;t.length>e;e+=1)this.defineEvent(t[e]);return this},r.removeListener=function(t,n){var r,i,o=this.getListenersAsObject(t);for(i in o)o.hasOwnProperty(i)&&(r=e(o[i],n),-1!==r&&o[i].splice(r,1));return this},r.off=n("removeListener"),r.addListeners=function(t,e){return this.manipulateListeners(!1,t,e)},r.removeListeners=function(t,e){return this.manipulateListeners(!0,t,e)},r.manipulateListeners=function(t,e,n){var r,i,o=t?this.removeListener:this.addListener,s=t?this.removeListeners:this.addListeners;if("object"!=typeof e||e instanceof RegExp)for(r=n.length;r--;)o.call(this,e,n[r]);else for(r in e)e.hasOwnProperty(r)&&(i=e[r])&&("function"==typeof i?o.call(this,r,i):s.call(this,r,i));return this},r.removeEvent=function(t){var e,n=typeof t,r=this._getEvents();if("string"===n)delete r[t];else if("object"===n)for(e in r)r.hasOwnProperty(e)&&t.test(e)&&delete r[e];else delete this._events;return this},r.removeAllListeners=n("removeEvent"),r.emitEvent=function(t,e){var n,r,i,o,s=this.getListenersAsObject(t);for(i in s)if(s.hasOwnProperty(i))for(r=s[i].length;r--;)n=s[i][r],n.once===!0&&this.removeListener(t,n.listener),o=n.listener.apply(this,e||[]),o===this._getOnceReturnValue()&&this.removeListener(t,n.listener);return this},r.trigger=n("emitEvent"),r.emit=function(t){var e=Array.prototype.slice.call(arguments,1);return this.emitEvent(t,e)},r.setOnceReturnValue=function(t){return this._onceReturnValue=t,this},r._getOnceReturnValue=function(){return this.hasOwnProperty("_onceReturnValue")?this._onceReturnValue:!0},r._getEvents=function(){return this._events||(this._events={})},t.noConflict=function(){return i.EventEmitter=o,t},"function"==typeof define&&define.amd?define("eventEmitter/EventEmitter",[],function(){return t}):"object"==typeof module&&module.exports?module.exports=t:this.EventEmitter=t}.call(this),function(t){function e(e){var n=t.event;return n.target=n.target||n.srcElement||e,n}var n=document.documentElement,r=function(){};n.addEventListener?r=function(t,e,n){t.addEventListener(e,n,!1)}:n.attachEvent&&(r=function(t,n,r){t[n+r]=r.handleEvent?function(){var n=e(t);r.handleEvent.call(r,n)}:function(){var n=e(t);r.call(t,n)},t.attachEvent("on"+n,t[n+r])});var i=function(){};n.removeEventListener?i=function(t,e,n){t.removeEventListener(e,n,!1)}:n.detachEvent&&(i=function(t,e,n){t.detachEvent("on"+e,t[e+n]);try{delete t[e+n]}catch(r){t[e+n]=void 0}});var o={bind:r,unbind:i};"function"==typeof define&&define.amd?define("eventie/eventie",o):t.eventie=o}(this),function(t,e){"function"==typeof define&&define.amd?define(["eventEmitter/EventEmitter","eventie/eventie"],function(n,r){return e(t,n,r)}):"object"==typeof exports?module.exports=e(t,require("wolfy87-eventemitter"),require("eventie")):t.imagesLoaded=e(t,t.EventEmitter,t.eventie)}(window,function(t,e,n){function r(t,e){for(var n in e)t[n]=e[n];return t}function i(t){return"[object Array]"===p.call(t)}function o(t){var e=[];if(i(t))e=t;else if("number"==typeof t.length)for(var n=0,r=t.length;r>n;n++)e.push(t[n]);else e.push(t);return e}function s(t,e,n){if(!(this instanceof s))return new s(t,e);"string"==typeof t&&(t=document.querySelectorAll(t)),this.elements=o(t),this.options=r({},this.options),"function"==typeof e?n=e:r(this.options,e),n&&this.on("always",n),this.getImages(),c&&(this.jqDeferred=new c.Deferred);var i=this;setTimeout(function(){i.check()})}function a(t){this.img=t}function u(t){this.src=t,d[t]=this}var c=t.jQuery,l=t.console,h=void 0!==l,p=Object.prototype.toString;s.prototype=new e,s.prototype.options={},s.prototype.getImages=function(){this.images=[];for(var t=0,e=this.elements.length;e>t;t++){var n=this.elements[t];"IMG"===n.nodeName&&this.addImage(n);var r=n.nodeType;if(r&&(1===r||9===r||11===r))for(var i=n.querySelectorAll("img"),o=0,s=i.length;s>o;o++){var a=i[o];this.addImage(a)}}},s.prototype.addImage=function(t){var e=new a(t);this.images.push(e)},s.prototype.check=function(){function t(t,i){return e.options.debug&&h&&l.log("confirm",t,i),e.progress(t),n++,n===r&&e.complete(),!0}var e=this,n=0,r=this.images.length;if(this.hasAnyBroken=!1,!r)return void this.complete();for(var i=0;r>i;i++){var o=this.images[i];o.on("confirm",t),o.check()}},s.prototype.progress=function(t){this.hasAnyBroken=this.hasAnyBroken||!t.isLoaded;var e=this;setTimeout(function(){e.emit("progress",e,t),e.jqDeferred&&e.jqDeferred.notify&&e.jqDeferred.notify(e,t)})},s.prototype.complete=function(){var t=this.hasAnyBroken?"fail":"done";this.isComplete=!0;var e=this;setTimeout(function(){if(e.emit(t,e),e.emit("always",e),e.jqDeferred){var n=e.hasAnyBroken?"reject":"resolve";e.jqDeferred[n](e)}})},c&&(c.fn.imagesLoaded=function(t,e){var n=new s(this,t,e);return n.jqDeferred.promise(c(this))}),a.prototype=new e,a.prototype.check=function(){var t=d[this.img.src]||new u(this.img.src);if(t.isConfirmed)return void this.confirm(t.isLoaded,"cached was confirmed");if(this.img.complete&&void 0!==this.img.naturalWidth)return void this.confirm(0!==this.img.naturalWidth,"naturalWidth");var e=this;t.on("confirm",function(t,n){return e.confirm(t.isLoaded,n),!0}),t.check()},a.prototype.confirm=function(t,e){this.isLoaded=t,this.emit("confirm",this,e)};var d={};return u.prototype=new e,u.prototype.check=function(){if(!this.isChecked){var t=new Image;n.bind(t,"load",this),n.bind(t,"error",this),t.src=this.src,this.isChecked=!0}},u.prototype.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},u.prototype.onload=function(t){this.confirm(!0,"onload"),this.unbindProxyEvents(t)},u.prototype.onerror=function(t){this.confirm(!1,"onerror"),this.unbindProxyEvents(t)},u.prototype.confirm=function(t,e){this.isConfirmed=!0,this.isLoaded=t,this.emit("confirm",this,e)},u.prototype.unbindProxyEvents=function(t){n.unbind(t.target,"load",this),n.unbind(t.target,"error",this)},s});var t,e,n,r,i,o;$(function(){return Pace.on("done",function(){var t,e,n,r;$("body").removeClass("is-loading"),t=function(t,e){return setTimeout(e,t)},e=$(".section_grid .section_header"),n=$(".section_grid ul li p"),e.addClass("animated"),r=150,n.each(function(e,n){return t(r,function(){return $(n).addClass("animated")}),r+=150}),$(".pace").remove()})}),$(function(){var t,e;return t=$("header"),e=t.outerHeight(),$(window).on("resize",function(){return e=t.outerHeight()})}),$(function(){var t,e;return t=$("header"),e=$(window).scrollTop(),e>120?t.addClass("shrunk"):t.removeClass("shrunk"),$(window).scroll(function(){return t=$("header"),e=$(this).scrollTop(),e>120?t.addClass("shrunk"):t.removeClass("shrunk")})}),$(".section_accordion").length>0&&(o=$(".section_accordion .section_header > a"),e=$(".section_accordion .section_body > div"),t=$(".section_accordion .section_body"),n=function(t,e){return setTimeout(e,t)},r=0,i=void 0,e.each(function(){return $(this).outerHeight()>r?(i=$(this),r=$(this).outerHeight()):void 0}),t.css("height",r),o.click(function(){var r,i,s,a;return a=$(this).attr("data-count"),s=$(".section_accordion .section_body > div."+a),r=$(this).hasClass("active"),i=t.hasClass("active"),r&&i===!0?(s.removeClass("active"),n(500,function(){return t.removeClass("active")}),n(500,function(){return o.removeClass("active")})):(o.removeClass("active"),e.removeClass("active"),t.addClass("active"),$(this).addClass("active"),n(500,function(){return s.addClass("active")}))}),$(window).on("resize",function(){return r=0,i=void 0,e.each(function(){return $(this).outerHeight()>r?(i=$(this),r=$(this).outerHeight()):void 0}),t.css("height",r)})),$(function(){var t,e,n,r;return n=$("header nav"),r=$(".nav-open"),t=$(".nav-close"),e=$("header"),r.click(function(){return e.addClass("nav-active"),n.addClass("is-active")}),t.click(function(){return e.removeClass("nav-active"),n.removeClass("is-active")})}),$(function(){var t,e,n,r,i,o;return e=$(".section_domain"),r=$(".section_domain .top"),t=$(".section_domain .bottom"),i=window.innerHeight,n=60,e.css("height",2*i-2*n),r.css("height",i-n),t.css("height",i-n),o=e.waypoint(function(t){var e;return r=$(this.element).children(".top"),e=$(this.element).children(".section_header"),r.toggleClass("is_pinned"),e.toggleClass("is_pinned animated")},{offset:n}),o=t.waypoint(function(t){return e=$(this.element).parent(".section_domain"),"down"===t?(e.toggleClass("is_scrolled"),e.css("height",i-n),e.css("margin-top",i-n)):(e.toggleClass("is_scrolled"),e.css("height",2*(i-n)),e.css("margin-top",0))},{offset:n})})}).call(this);