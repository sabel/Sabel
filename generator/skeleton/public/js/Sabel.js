if (typeof window.Sabel !== "undefined") throw "Sabel is already loaded.";

window.Sabel = {
	version: "0.1.1"
};

Sabel.Window = {
	width:  document.documentElement.clientWidth,
	height: document.documentElement.clientHeight
};

Sabel.Dom = {
	getElementById: function(elem)
	{
		if (typeof elem === "string") elem = document.getElementById(elem);

		return (elem) ? Sabel.Element.create(elem) : null;
	}
};

Sabel.get = Sabel.Dom.getElementById;

Sabel.dump = function(element, output) {
	if (typeof output === "string") {
		output = Sabel.get(output);
	} else if (output === undefined) {
		if (!(output = Sabel.get("dumpElement"))) {
			output = document.createElement("div");
			document.body.appendChild(output);
			output.setAttribute("id", "dumpElement");
		}
	}

	if (typeof element === "string") {
		// @todo use Sabel.Element.update?
		output.innerHTML = element;
	} else {
		var buf = new Array();
		buf[buf.length] = element;
		for (var key in element) {
			try {
				buf[buf.length] = key + " : " + element[key];
			} catch (e) {}
		}
		// @todo use Sabel.Element.update?
		output.innerHTML = buf.join("<br />\n");
	}
};

Sabel.Logger = {
	log: function(text)
	{
		if (typeof console === "object") {
			console.log(text);
		} else {
			// @todo make this.
		}
	}
};

var ua = navigator.userAgent.toLowerCase();

Sabel.UserAgent = {
	isIE: /msie/.test(ua) && !window.opera,
	isMozilla: /mozilla/.test(ua) && !/(opera|compatible|konquator|webkit)/.test(ua),
	isOpera: /opera/.test(ua),
	isSafari: /webkit/.test(ua),
	isKonquator: /konquator/.test(ua),
	isKHTML: /KHTML/.test(ua)
};

delete ua;

Sabel.Iterator = function(array) { this.initialize(array) };
Sabel.Iterator.prototype = {
	items: new Array(),
	index: 0,

	initialize: function(array)
	{
		if (array instanceof NodeList) {
			var buf = new Array();
			for (var i = 0, len = array.length; i < len; i++) {
				buf[buf.length] = array.item(i);
			}
			array = buf;
		}

		this.items = array;
		this.index = 0;
	},

	hasNext: function()
	{
		return this.index < this.items.length;
	},

	next: function()
	{
		return this.items[this.index++];
	}
};

Sabel.Array = {
	has: function(array, value)
	{
		for (var i = 0, len = array.length; i < len; i++) {
			if (array[i] === value) return true;
		}
		return false;
	},

	create: function(iterable)
	{
		if (!iterable) {
			iterable = new Array();
		} else if (typeof iterable === "string") {
			iterable = new Array(iterable);
		} else if (iterable.toArray) {
			iterable = iterable.toArray();
		} else {
			var buf = new Array();
			for (var i = 0, len = iterable.length; i < len; i++) {
				buf[buf.length] = iterable[i];
			}
			iterable = buf;
		}

		return Sabel.Extends(iterable, this, true);
	}
};

Sabel.Object = {
	extend: function()
	{
		if (arguments.length === 0) return {};

		var child  = arguments[0], i = 1, parent;

		while (parent = arguments[i++]) {
			child = Sabel.Extends(child, parent);
		}

		return child;
	}
};

Sabel.Function = {
	bind: function(func)
	{
		var args = Sabel.Array.create(arguments);
		var method = args.shift(), object = args.shift();

		return function() {
			return method.apply(object, args.concat(Sabel.Array.create(arguments)));
		}
	},

	curry: function()
	{
		var args = Sabel.Array.create(arguments), method = args.shift();

		return function() {
			method.apply(null, Sabel.Array.create(arguments).concat(args));
		}
	},

	create: function(method)
	{
		return Sabel.Extends(method, this, true);
	}
};

Sabel.Extends = function(child, parent, curry) {
	// @todo rename method name.
	function tmp(method) {
		return function() {
			var args = new Array(this);
			args.push.apply(args, arguments);
			return method.apply(this, args);
		}
	}

	for (var prop in parent) {
		if (child[prop] !== undefined) continue;
		child[prop] = (curry === true) ? tmp(parent[prop]) : parent[prop];
	}
	return child;
};

Sabel.Element = {
	hasClassName: function(element, className)
	{
		element = Sabel.get(element);

		var re = new RegExp("(^|\\s)" + className + "(\\s|$)");
		return re.test(element.className);
	},

	getStyle: function(element, property)
	{
		element = Sabel.get(element);

		var style = element.currentStyle || document.defaultView.getComputedStyle(element, "");
		return style[property];
	},

	getOffsetTop: function(element)
	{
		element = Sabel.get(element);

		var position = element.offsetTop;

		if (Sabel.UserAgent.isOpera) {
			var parent = element.offsetParent;
			position -= parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
		}

		return position;
	},

	getOffsetLeft: function(element)
	{
		element = Sabel.get(element);

		var position = element.offsetLeft;

		if (Sabel.UserAgent.isOpera) {
			var parent = element.offsetParent;
			position -= parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
		}

		return position;
	},

	getDimensions: function(element)
	{
		element = Sabel.get(element);

		var style = element.style;

		if (style.display != 'none') {
			return {width: element.clientWidth, height: element.clientHeight};
		}

		var oldV = style.visibility;
		var oldP = style.positions;
		var oldD = style.display;

		style.visibility = "hidden";
		style.positions  = "absolute";
		style.display    = "block";

		var dimensions = {width: element.clientWidth,
						  height: element.clientHeight};

		style.visibility = oldV;
		style.positions  = oldP;
		style.display    = oldD;

		return dimensions;
	},

	getWidth: function(element)
	{
		return Sabel.get(element).getDimensions().width;
	},

	getHeight: function(element)
	{
		return Sabel.get(element).getDimensions().height;
	},

	create: function(element)
	{
		return Sabel.Extends(element, this, true);
	}
};

Sabel.Cookie = {
	set: function(key, value, option)
	{
		var cookie = key + "=" + escape(value);

		if (typeof option !== "object") option = { expire: option };

		if (option.expire) {
			var date = new Date();
			date.setTime(date.getTime() + option.expire * 1000);
			cookie += "; expires=" + date.toGMTString();
		}
		if (option.domain) cookie += "; domain=" + option.domain;
		if (option.path)   cookie += "; path="   + options.path;
		if (option.secure) cookie += "; secure";

		document.cookie = cookie;
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
		this.set(key, "", -1);
	},

	clear: function()
	{
		var cs = document.cookie.split(";");
		for (var i = 0, len = cs.length; i < len; i++) {
			Sabel.Cookie.unset(cs[i].match(/\w+/));
		}
	}
};

Sabel.Event = function(element, eventName, handler) {
	element = Sabel.get(element);

	this.element   = element;
	this.eventName = eventName;
	this.handler   = handler;

	Sabel.Event._events.push(this);

	this.start();
};
Sabel.Event.prototype = {
	start: function()
	{
		if (this.isActive !== true) {
			var element = this.element;

			if (element.addEventListener) {
				element.addEventListener(this.eventName, this.handler, false);
			} else if (element.attachEvent) {
				element.attachEvent("on" + this.eventName, this.handler);
			} else {
				// @todo make this?
			}
			this.isActive  = true;
		}
	},

	stop: function()
	{
		if (this.isActive === true) {
			var element = this.element;

			if (element.removeEventListener) {
				element.removeEventListener(this.eventName, this.handler, false);
			} else if (element.detachEvent) {
				element.detachEvent("on" + this.eventName, this.handler);
			} else {
				// @todo make this?
			}
			this.isActive = false;
		}
	}
};

Sabel.Event._events = new Array();

Sabel.Event.create = function(element, eventName, handler) {
	return new Sabel.Event(element, eventName, handler);
};

Sabel.Event.destroy = function() {
	var events = Sabel.Event._events;
	for (var i = 0, len = events.length; i < len; i++) events[i].stop();
};
if (window.attachEvent) window.attachEvent("onunload", Sabel.Event.destroy);

if (typeof window.XMLHttpRequest === "undefined") {
	window.XMLHttpRequest = function() {
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
};

Sabel.Ajax = function() { this.initialize.apply(this, arguments); };
Sabel.Ajax.prototype = {
	initialize: function()
	{
		this.xmlhttp   = new XMLHttpRequest();
		this.completed = false;
	},

	request: function(url, options)
	{
		var xmlhttp = this.xmlhttp;
		var options = this.setOptions(options);

		//xmlhttp.onreadystatechange = this.onStageChange.bind(this);
		xmlhttp.onreadystatechange = Sabel.Function.bind(this.onStateChange, this);

		if (options.method === "get") {
			url += ((url.indexOf("?") !== -1) ? "&" : "?") + options.params;
		}

		xmlhttp.open(options.method, url, options.async);
		this.setRequestHeaders();
		xmlhttp.send((options.method === "post") ? options.params : "");
	},

	updater: function(element, url, options)
	{
		if (options === undefined) options = {};

		var onComplete = options.onComplete || function() {};
		options.onComplete = function(response) {
			Sabel.get(element).innerHTML = response.responseText;
			onComplete(response);
		}

		this.request(url, options);
	},

	setOptions: function(options)
	{
		var defaultOptions = {
			method: "post",
			params: "",
			contentType: "application/x-www-form-urlencoded",
			charset: "UTF-8",
			onComplete: function(){},
			onSuccess: function(){},
			onFailure: function(){},
			async: true
		};
		options = Sabel.Object.extend(options, defaultOptions);
		options.method = options.method.toLowerCase();
		return (this.options = options);
	},

	setRequestHeaders: function()
	{
		var headers = {
			"X_Requested-With": "XMLHttpRequest",
			"Accept": "text/javascript, text/html, application/xml, text/xml, */*"
		};
		var xmlhttp = this.xmlhttp;
		var options = this.options;

		if (options.method === "post") {
			headers["Content-Type"] = options.contentType + "; charset=" + options.charset;
		}

		if (typeof options.headers === "object") {
			headers = Sabel.Object.extend(options.headers, headers);
		}

		for (var key in headers) xmlhttp.setRequestHeader(key, headers[key]);
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

	isSuccess: function()
	{
		var status = this.xmlhttp.status;
		return (status && (status >= 200 && status < 300));
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
};

//Sabel.Effect = function() { this.initialize.apply(this, arguments) };



