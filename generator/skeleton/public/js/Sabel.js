if (typeof(Sabel) == "undefined") window.Sabel = {};

Sabel.init = function()
{
  var elements = document.getElementsByTagName("script");
  var script   = null;
  var pattern  = /Sabel\.js$/;
  for (var i = 0; i < elements.length; i++) {
    script = elements[i];
    if (pattern.test(script.src)) {
      this.path = script.src.replace(pattern, "");
    }
  }
}
Sabel.using = function(src)
{
  var src  = this.path + src.replace(/\./, "/") + ".js";
  var type = "text/javascript";
  if (typeof document.body === "object" && !document.all) {
    document.write('<script type="' + type + '" src="' + src + '"></script>');
    return;
    var script  = document.createElement("script");
    script.type = type;
    script.src  = src;
    document.body.appendChild(script);
  } else {
    document.write('<script type="' + type + '" src="' + src + '"></script>');
  }
}
Sabel.init();

Sabel.get = function(elem)
{
  if (typeof elem === "string") elem = document.getElementById(elem);
  return elem;
}

Sabel.find = function(selector)
{
  if (typeof document.getElementsByTagName == "undefined") return new Array();

  var tokens = selector.split(" ");
  var current = new Array(document);
  for (var i = 0; i < tokens.length; i++) {
    var token = tokens[i].replace(/^\s/, "").replace(/\s$/, "");
    var pattern = /^([\w-]*)([#\.]?)([\w-]*)$/;
    var matches = pattern.exec(token);

    if (matches) {
      switch(matches[2]) {
        case "#":
          var element = document.getElementById(matches[3]);
          if (matches[1] && element.nodeName.toLowerCase() != matches[1]) {
            return new Array();
          }
          if (tokens.length-1 == i) {
            current = element;
          } else {
            current = new Array(element);
          }
          break;
        case ".":
          tagName = matches[1] || "*";
          var found = new Array();
          for (var j = 0; j < current.length; j++) {
            var elements = current[j].getElementsByTagName(tagName);
            for (var k = 0; k < elements.length; k++) {
              found.push(elements[k]);
            }
          }
          current = new Array();
          var pattern = "(^|\\s)" + matches[3] + "(\\s|$)";
          for (var l = 0; l < found.length; l++) {
            if (found[l].className == matches[3] ||
                found[l].className.match(new RegExp(pattern))) current.push(found[l]);
          }
          break;
        default:
          var found = new Array();
          for (var j = 0; j < current.length; j++) {
            var elements = current[j].getElementsByTagName(matches[1]);
            for (var k = 0; k < elements.length; k++) {
              found.push(elements[k]);
            }
          }
          current = new Array();
          for (var l = 0; l < found.length; l++) {
            current.push(found[l]);
          }

          break;
      }
    }
  }
  return current;
};


/* ------------------------------------------------------------------------- */
// Sabel.dump

Sabel.dump = function(element, output)
{
  if (typeof output == "string") {
    output = this.find("#" + output);
  } else if (output == undefined) {
    if (!(output = this.find("#dumpElement"))) {
      output = document.createElement("div");
      document.body.appendChild(output);
      output.setAttribute("id", "dumpElement");
    }
  }

  if (typeof element === "string") {
    output.innerHTML = element;
  } else {
    var buf = new Array()
    buf.push(element)
    for (var key in element) {
      try {
        buf.push(key +  " : " + element[key])
      } catch (e) {}
    }
    output.innerHTML = buf.join("<br />\n")
  }
};

/* ------------------------------------------------------------------------- */
// prototype

var Class = {
  create: function()
  {
    return function() {
      this.initialize.apply(this, arguments);
    }
  },

  extend: function(parent)
  {
    var c = Class.create();
    c.prototype = parent.prototype;
    return c;
  }
}

Object.extend = function() {
  if (arguments.length === 0) return {};

  var target = arguments[0], i = 1, obj;

  while (obj = arguments[i++]) {
    for (var prop in obj) target[prop] = obj[prop];
  }

  return target;
}

function $A(obj)
{
  var results = new Array(obj.length);
  for (var i=0, len=obj.length; i<len; i++) {
    //results.push(obj[i]);
    results[i] = obj[i];
  }
  return results;
}

Function.prototype.bind = function() {
  var method = this, args = $A(arguments), obj = args.shift();
  return function() {
    return method.apply(obj, args.concat($A(arguments)));
  }
}

Sabel.Effect = Class.create();

Sabel.Effect.prototype = {
  initialize: function(options)
  {
    this.events  = new Array();
    this.timer   = null;
    this.state   = 0;
    this.target  = 0;
    this.options = Object.extend(
      {interval: 20, duration: 500, toggle: false}, options);
  },

  add: function(event)
  {
    this.events[this.events.length] = event;
    return this;
  },

  run: function()
  {
    if (this.timer === null) {
      this.state = 0;
      this.target = 100;
      this.timer = setInterval(this._process.bind(this), this.options.interval);
    }
  },

  reverse: function()
  {
    if (this.timer === null) {
      this.state = 100;
      this.target = 0;
      this.timer = setInterval(this._process.bind(this), this.options.interval);
    }
  },

  toggle: function()
  {
    this.target === 0 ? this.run() : this.reverse();
  },

  pause: function()
  {
    clearInterval(this.timer);
    this.timer = null;
  },

  restart: function()
  {
    if (this.timer === null && this.state !== this.target)
      this.timer = setInterval(this._process.bind(this), this.options.interval);
  },

  _process: function()
  {
    var options = this.options, events = this.events;

    if (this.target === 100) {
      this.state += Math.floor((options.interval / options.duration)*100);
      if (this.state > this.target) this.state = this.target;
    } else {
      this.state -= Math.floor((options.interval / options.duration)*100);
      if (this.state < this.target) this.state = this.target;
    }

    var state = this.state / 100;
    for (var i = 0, length = events.length; i < length; i++) {
      events[i](state);
    }

    if (this.state == this.target) {
      clearInterval(this.timer);
      this.timer = null;
    }
  }
}

Sabel.Effect.BlindDown = function(element) {
  element = Sabel.get(element);

  var options = {from: 0,
                 to:   Sabel.Element.getDimensions(element).height};

  return function(state) {
    var height = Math.round(state * options.to);
    element.style.height = Math.round(state * options.to) + "px";
    element.style.visibility = (height === 0) ? "hidden" : "visible";
  }
}

Sabel.Effect.Fade = function(element, options) {
  element = Sabel.get(element);

  if (typeof options !== "object") options = {to: options}
  if (typeof options.from === "undefined") options.from = 0;

  if (Sabel.UserAgent.isIE) {
    return function(state) {
      var opacity = Math.round((options.from + (state * (options.to - options.from))) * 100);
      element.style.filter = "alpha(opacity=" + opacity + ")";
    }
  } else {
    return function(state) {
      var opacity = Math.round((options.from + (state * (options.to - options.from))) * 100) / 100;
      element.style.opacity = opacity;
    }
  }
}

Sabel.Effect.MoveX = function(element, options) {
  element = Sabel.get(element);

  if (typeof options !== "object") options = {to: options}
  if (typeof options.from === "undefined")
    options.from = parseInt(Sabel.Element.getStyle(element, "marginLeft")) | 0;

  return function(state) {
    element.style.marginLeft =
      options.from + Math.round(state * (options.to - options.from)) + "px";
  }
}

Sabel.Effect.HighLight = function(element, options) {
  element = Sabel.get(element);

  if (typeof options !== "object") options = {to: options}
  if (typeof options.from === "undefined") options.from = "#FFFFFF";

  return function(state) {
    var r = {f: parseInt(options.from.substr(1,2), 16), t: parseInt(options.to.substr(1,2), 16)};
    r = (r.f + Math.round(state * (r.t - r.f))).toString(16);
    var g = {f: parseInt(options.from.substr(3,2), 16), t: parseInt(options.to.substr(3,2), 16)};
    g = (g.f + Math.round(state * (g.t - g.f))).toString(16);
    var b = {f: parseInt(options.from.substr(5,2), 16), t: parseInt(options.to.substr(5,2), 16)};
    b = (b.f + Math.round(state * (b.t - b.f))).toString(16);

    element.style.backgroundColor = "#"+r+g+b;
  }
}

Sabel.Effect.ResizeX = function(element, options) {
  element = Sabel.get(element);

  if (typeof options !== "object") options = {to: options}
  if (typeof options.from === "undefined")
    options.from = element.clientWidth | element.offsetWidth;

  return function(state) {
    element.style.width = options.from + Math.round(state * (options.to - options.from)) + "px";
  }
}


Sabel.using("sabel.UserAgent");

if (typeof XMLHttpRequest === "undefined") {
  window.XMLHttpRequest = function()
  {
    try {
      return new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e1) {
      try {
        return new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e2) {
        return null;
      }
    }
  }
}

Sabel.Ajax = Class.create();

Sabel.Ajax.prototype = {
  initialize: function()
  {
    this.xmlhttp   = new XMLHttpRequest();
    this.completed = false;
  },

  Request: function(url, options)
  {
    this.setOptions(options);

    var xmlhttp = this.xmlhttp;
    var options = this.options;

    xmlhttp.onreadystatechange = this.onStateChange.bind(this);

    if (options.method.toLowerCase() === "get") {
      url += ((url.indexOf("?") !== -1) ? "&" : "?") + options.params;
    }

    xmlhttp.open(options.method, url, options.async);
    this.setRequestHeaders();
    xmlhttp.send(options.method.toLowerCase() === "post" ? options.params : "");
  },

  setOptions: function(options)
  {
    this.options = {
      method: "post",
      params: "",
      contentType: "application/x-www-form-urlencoded",
      charset: "UTF-8",
      onComplete: function(){},
      onSuccess: function(){},
      onFailure: function(){},
      async: true
    }
    this.options = Object.extend(this.options, options || {});
  },

  setRequestHeaders: function()
  {
    var headers = {
      "X-Requested-With": "XMLHttpRequest",
      "Accept": "text/javascript, text/html, application/xml, text/xml, */*"
    };
    var xmlhttp = this.xmlhttp;
    var options = this.options;

    if (options.method === "post") {
      headers["Content-Type"] = options.contentType + "; charset=" + options.charset;
    }

    if (typeof options.headers === "object") {
      headers = Object.extend(headers, options.headers);
    }

    for (var key in headers) xmlhttp.setRequestHeader(key, headers[key]);
  },

  isSuccess: function()
  {
    return (this.xmlhttp.status
            && (this.xmlhttp.status >= 200 && this.xmlhttp.status < 300));
  },

  onStateChange: function()
  {
    if (this.completed === true) return;
    if (this.xmlhttp.readyState === 4) {
      this.completed = true;
      var response = this.getResponses();
      this.options["on" + (this.isSuccess() ? "Success" : "Failure")](response);
      this.options.onComplete(response);
    }
  },

  getResponses: function()
  {
    var xmlhttp  = this.xmlhttp;
    var response = new Object();
    response.responseXML  = xmlhttp.responseXML;
    response.responseText = this.responseFilter(xmlhttp.responseText);
    response.status = xmlhttp.status;
    response.statusText = xmlhttp.statusText;
    return response;
  },

  responseFilter: function(text)
  {
    if (Sabel.UserAgent.isKHTML) {
      var esc = escape(text);
      if (esc.indexOf("%u") < 0 && esc.indexOf("%") > -1) {
        text = decodeURIComponent(esc);
      }
    }
    return text;
  }
}

Sabel.Event = {
  observes: new Array(),
  
  observe: function(element, handler, func)
  {
    if (element.addEventListener) {
      element.addEventListener(handler, func, false);
    } else if (element.attachEvent) {
      element.attachEvent("on" + handler, func);
    }
    this.observes[handler] = func;
  },
  
  stopObserve: function(element, handler)
  {
    if (element.removeEventListener) {
      element.removeEventListener(handler, this.observes[handler], false);
    } else if (element.detachEvent) {
      element.detachEvent("on" + handler, this.observes[handler]);
    }
    delete this.observes[handler];
  }
}

Sabel.UserAgent = {
  initialize: function()
  {
    var ua = navigator.userAgent.toLowerCase();

    this.isIE        = /msie/.test(ua) && !/opera/.test(ua);
    this.isMozilla   = (/mozilla/.test(ua) && !/(opera|compatible|konquator|webkit)/.test(ua));
    this.isOpera     = /opera/.test(ua);
    this.isSafari    = /webkit/.test(ua);
    this.isKonquator = /konquator/.test(ua);
    this.isKHTML     = /KHTML/.test(ua);
  }
}

Sabel.UserAgent.initialize();

Sabel.Logger = {
  log: function(log)
  {
    if (typeof console == "object" && console.log) {
      console.log(log);
    } else if (window.opera) {
      window.opera.postError(log);
    } else {
      //alert(log);
    }
  },

  debug: function(log)
  {
    if (typeof console == "object" && console.debug) {
      console.debug(log);
    } else if (window.opera) {
      window.opera.postError(log);
    } else {
      //alert(log);
    }
  }
};

Array.prototype.include = function(val) {
  for (var i = 0; i < this.length; i++) {
    if (this[i] == val) return true;
  }
  return false;
}

Sabel.Cookie = {
  set: function(key, value, option)
  {
    var string = key + "=" + escape(value);

    if (typeof option !== "object") option = { expire: option };

    if (option.expire) {
      var d = new Date();
      d.setTime(d.getTime() + option.expire * 1000);
      string += "; expires=" + d.toGMTString();
    }
    if (option.domain) string += "; domain="  + option.domain;
    if (option.path)   string += "; path="    + option.path;
    if (option.secure) string += "; secure";

    document.cookie = string;
  },

  get: function(key)
  {
    key = key + "=";
    var cs = document.cookie.split(";");
    for (var i = 0; i < cs.length; i++) {
      var c = cs[i].replace(/ /g, "");
      if (c.indexOf(key) === 0) return unescape(c.substring(key.length));
    }
    return null;
  },

  unset: function(key)
  {
    Sabel.Cookie.set(key, "", -1);
  },

  clear: function()
  {
    var cs = document.cookie.split(";");
    for (var i = 0; i < cs.length; i++) {
      Sabel.Cookie.set(cs[i].match(/\w+/), "", -1);
    }
  }
}


Sabel.Element = {
  getStyle: function(element, property, s)
  {
    element = Sabel.get(element);
 
    if (element.currentStyle) {
      return element.currentStyle[property];
    } else if (document.defaultView.getComputedStyle) {
      var style = document.defaultView.getComputedStyle(element, s);
      return style[property];
    }
  },
  
  getCumulativeTop: function(element)
  {
    var position = 0;
    var parent   = null;
    
    do {
      position += element.offsetTop;
      parent = element.offsetParent;
      
      if (Sabel.UserAgent.isIE || Sabel.UserAgent.isMozilla) {
        var border = parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
        position += border || 0;
        
        if (Sabel.UserAgent.isIE) {
          var padding = parseInt(Sabel.Element.getStyle(parent, "paddingTop"));
          if (padding > 0) {
            position += parseInt(Sabel.Element.getStyle(element, "marginTop")) || 0;
          }
        } else if (Sabel.UserAgent.isMozilla) {
          var of = Sabel.Element.getStyle(parent, "overflow");
          if (!["visible", "inherit"].include(of)) {
            position += border;
          }
        }
      }
      
      element = parent;
      if (element) {
        if (["BODY", "HTML"].include(element.tagName)) break;
      }
    } while (element);
    
    return position;
  },
  
  getCumulativeLeft: function(element)
  {
    var position = 0;
    var parent   = null;
    
    do {
      position += element.offsetLeft;
      parent = element.offsetParent;
      
      if (Sabel.UserAgent.isIE || Sabel.UserAgent.isMozilla) {
        var border = parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
        position += border || 0;
        if (Sabel.UserAgent.isMozilla) {
          var of = Sabel.Element.getStyle(parent, "overflow");
          if (!["visible", "inherit"].include(of)) { 
            position += border;
          }
        }
      }
      
      element = parent;
      if (element) {
        if (["BODY", "HTML"].include(element.tagName)) break;
      }
    } while (element);
    
    return position;
  },
  
  getOffsetTop: function(element)
  {
    var position = element.offsetTop;
    var parent   = element.offsetParent;
    
    if (Sabel.UserAgent.isIE || Sabel.UserAgent.isMozilla) {
      //var border = parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
      //position += border || 0;
      
      if (Sabel.UserAgent.isIE) {
        var padding = parseInt(Sabel.Element.getStyle(parent, "paddingTop"));
        if (padding > 0) {
          position += parseInt(Sabel.Element.getStyle(element, "marginTop")) || 0;
        }
      } else if (Sabel.UserAgent.isMozilla) {
        var border = parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
        var of = Sabel.Element.getStyle(parent, "overflow");
        if (!["visible", "inherit"].include(of)) {
          position += border;
        }
      }
    } else if (Sabel.UserAgent.isOpera) {
      position -= parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
    }
    
    return position;
  },
  
  getOffsetLeft: function(element)
  {
    var position = element.offsetLeft;
    var parent   = element.offsetParent;
    
    if (Sabel.UserAgent.isIE || Sabel.UserAgent.isMozilla) {
      //var border = parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
      //position += border || 0;
      
      if (Sabel.UserAgent.isIE) {
        var padding = parseInt(Sabel.Element.getStyle(parent, "paddingLeft"));
        if (padding > 0) {
          position += parseInt(Sabel.Element.getStyle(element, "marginLeft")) || 0;
        }
      } else if (Sabel.UserAgent.isMozilla) {
        var border = parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
        var of = Sabel.Element.getStyle(parent, "overflow");
        if (!["visible", "inherit"].include(of)) {
          position += border;
        }
      }
    } else if (Sabel.UserAgent.isOpera) {
      position -= parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
    }
    
    return position;
  },

  getOffsetPositions: function(element)
  {
    return {left: this.getOffsetLeft(element),
            top: this.getOffsetTop(element)};
  },

  getDimensions: function(element)
  {
    element = Sabel.get(element);

    var s  = element.style;
    var oV = element.style.visibility;
    var oP = element.style.positions;
    var oD = element.style.display;
    s.visibility = "hidden";
    s.positions  = "absolute";
    s.display    = "block";
    var dimensions = {width:  element.clientWidth,
                      height: element.clientHeight};
    s.visibility = oV;
    s.positions  = oP;
    s.display    = oD;
    return dimensions;
  }
}

Sabel.using("sabel.Element");
Sabel.using("sabel.UserAgent");

var Class = {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
}

Sabel.DragAndDrop = Class.create();

Sabel.DragAndDrop.prototype = {
  initialize: function(element, options)
  {
    element.style.cursor = options.cursor || "move";
    element.style.position = "absolute";

    this.element  = element;
    this.observes = new Array();
    this.initPos  = Sabel.Element.getOffsetPositions(element);
    this.setOptions(options || {});

    var self = this;
    this.observe(element, "mousedown", function(e) { self.mouseDown(e) });
  },

  setOptions: function(o)
  {
    this.options = {
      startCallback: o.startCallback ? o.startCallback : null,
      endCallback:   o.endCallback   ? o.endCallback   : null,
      moveCallback:  o.moveCallback  ? o.moveCallback  : null,
      rangeX: null, rangeY: null
    }
    if (o.x) this.setXConst(o.x);
    if (o.y) this.setYConst(o.y);
  },

  setXConst: function(range)
  {
    if (range.length < 2) return null; // @todo exception

    var startX = this.initPos.left;
    this.options.rangeX = {min: startX - range[0], max: startX + range[1]};
    if (range[2] !== undefined) this.options.gridX = range[2];
    return this;
  },

  setYConst: function(range)
  {
    if (range.length < 2) return null; // @todo exception

    var startY = this.initPos.top;
    this.options.rangeY = {min: startY - range[0], max: startY + range[1]};
    if (range[2] !== undefined) this.options.gridY = range[2];
    return this;
  },

  setGrid: function(grid)
  {
    this.options.gridX = grid[0];
    this.options.gridY = grid[1];
  },
  
  observe: function(element, handler, func, useCapture)
  {
    if (this.observes[handler]) return;

    if (element.addEventListener) {
      element.addEventListener(handler, func, useCapture || false);
    } else if (element.attachEvent) {
      element.attachEvent("on" + handler, func);
    }
    this.observes[handler] = func;
  },
  
  stopObserve: function(element, handler)
  {
    if (element.removeEventListener) {
      element.removeEventListener(handler, this.observes[handler], false);
    } else if (element.detachEvent) {
      element.detachEvent("on" + handler, this.observes[handler]);
    }
    delete this.observes[handler];
  },
  
  mouseDown: function(e)
  {
    if (window.event) e = window.event;
    if (Sabel.UserAgent.isIE) {
      e.returnValue = false;  // IE Hack.
    } else {
      e.preventDefault(); // Opera & Fx Hack.
    }

    this.startPos = Sabel.Element.getOffsetPositions(this.element);
    this.startX   = e.clientX;
    this.startY   = e.clientY;
    
    this.element.style.zIndex = "10000";

    this.observe(document, "mousemove", this.mouseMove.bind(this));
    this.observe(document, "mouseup",   this.mouseUp.bind(this));

    if (this.options.startCallback !== null) this.options.startCallback(this.element, e);
  },

  mouseUp: function(e)
  {
    if (window.event) e = window.event;
    this.element.style.zIndex = "1";
    this.stopObserve(document, "mousemove");
    this.stopObserve(document, "mouseup");

    if (this.options.endCallback !== null) this.options.endCallback(this.element, e);
    return false;
  },

  mouseMove: function(e)
  {
    if (window.event) e = window.event;
    if (Sabel.UserAgent.isIE) {
      e.returnValue = false; // IE Hack.
    } else {
      e.preventDefault(); // Opera & Fx Hack.
    }

    var options = this.options;
    var element = this.element;
    var moveX = e.clientX - this.startX;
    var moveY = e.clientY - this.startY;
    if (options.gridX) moveX -= (moveX % options.gridX);
    if (options.gridY) moveY -= (moveY % options.gridY);

    var xPos = this.startPos.left + moveX;
    var yPos = this.startPos.top  + moveY;

    if (options.rangeX !== null) xPos = Math.max(options.rangeX.min, Math.min(options.rangeX.max, xPos));
    if (options.rangeY !== null) yPos = Math.max(options.rangeY.min, Math.min(options.rangeY.max, yPos));

    element.style.top  = yPos + "px";
    element.style.left = xPos + "px";

    if (this.options.moveCallback !== null) this.options.moveCallback(this.element, e);
    return false;
  }
};

// @todo if (safari) else if (msie) ...
// @reference http://www.mikage.to/jquery/jquery_history.html
Sabel.History = {
  currentHash: "",
  callback: null,
  ignores: new Array(),

  init: function(callback)
  {
    this.callback = callback;
    this.test();

    setInterval(this.check.bind(this), 200);
  },

  check: function()
  {
    var current = location.hash;
    if (current !== this.currentHash) this.test();
  },

  load: function(hash)
  {
    hash = decodeURIComponent(hash.replace(/^#/, "")).replace(/^(\/|)/, "/");
    location.hash = encodeURIComponent(hash);
    this.currentHash = location.hash;
    this.callback(hash);
  },

  test: function()
  {
    var hash = decodeURIComponent(location.hash.replace(/^#/, ""));
    this.currentHash = location.hash;

    var length = this.ignores.length;
    var ignore = "";
    for (var i = 0; i < length; i++) {
      ignore = new RegExp(this.ignores[i]);
      if (ignore.test(hash)) return;
    }

    this.callback(hash);
  },

  addIgnore: function(hash)
  {
    this.ignores.push(hash.replace(/^(\/|)/, "/"));
  }
};

Sabel.Iterator = function(ary) {
  this.init(ary);
}
Sabel.Iterator.prototype = {
  index: 0,

  init: function(ary)
  {
    this.items = ary;
    this.index = 0;
  },

  hasNext: function()
  {
    return (this.index < this.items.length);
  },

  next: function()
  {
    return this.items[this.index++];
  }
}
