/**
 * SabelJS @VERSION
 * Header
 *
 *
 */

window.Sabel = {};

Sabel.Window = {};

Sabel.Window.getWidth = function() {
	if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
		// 後方互換モード
		return document.body.clientWidth;
	} else if (Sabel.UserAgent.isSafari) {
		// Safari 2.0.4 / Safari 3.0.4b (@todo Safari3は他のブラウザと同じにする)
		return window.innerWidth;
	} else {
		// 標準準拠モード
		return document.documentElement.clientWidth;
	}
};

Sabel.Window.getHeight = function() {
	if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
		// 後方互換モード
		return document.body.clientHeight;
	} else if (Sabel.UserAgent.isSafari) {
		// Safari 2.0.4 / Safari 3.0.4b (@todo Safari3は他のブラウザと同じにする)
		return window.innerHeight;
	} else {
		// 標準準拠モード
		return document.documentElement.clientHeight;
	}
};

Sabel.Window.getScrollWidth = function() {
	if (document.compatMode === "CSS1Compat") {
		var width = document.documentElement.scrollWidth;
	} else {
		var width = document.body.scrollWidth;
	}
	var clientWidth = Sabel.Window.getWidth();
	return (clientWidth > width) ? clientWidth : width;
};

Sabel.Window.getScrollHeight = function() {
	if (document.compatMode === "CSS1Compat") {
		var height = document.documentElement.scrollHeight;
	} else {
		var height = document.body.scrollHeight;
	}
	var clientHeight = Sabel.Window.getHeight();
	return (clientHeight > height) ? clientHeight : height;
};


Sabel.UserAgent = new function() {
	var ua = navigator.userAgent;
	this.ua = ua;

	this.isIE = false;
	this.isFirefox = false;
	this.isMozilla = false;
	this.isSafari  = false;
	this.isOpera   = false;

	/*@cc_on
	@if (@_jscript)
		this.isIE = true;
		function getVersion() {
			switch (@_jscript_version) {
				case 5.1: return "5.01";
				case 5.6: return "6.0";
				case 5.7: return "7.0";
				default: return @_jscript_version;
			}
		}
		this.version = getVersion();
	@else @*/
	if (this.isFirefox = /(?:Firefox|Minefield)\/([\d.]+)/.test(ua)) {
		this.version = parseFloat(RegExp.$1);
	} else if (this.isSafari = /Safari\/([\d.]+)/.test(ua)) {
		var build = parseInt(ua.substring(ua.lastIndexOf("/") + 1));
		if (build >= 523) {
			this.version = 3;
		} else if (build >= 412) {
			this.version = 2;
		} else if (build >= 100) {
			this.version = 1.1;
		} else if (build >= 85) {
			this.version = 1;
		}
	} else if (window.opera) {
		this.isOpera = true;
		if (typeof opera.version === "function") {
			this.version = opera.version();
		} else if (/Opera ([\d.]+)/.test(ua)) {
			this.version = parseFloat(RegExp.$1);
		}
	} else if (this.isMozilla = /Mozilla/.test(ua)) {
		this.version = "unknown";
	}
	/*@end @*/

	this.isWindows = /Win/.test(ua);
	this.isMac     = /Mac/.test(ua);
	this.isLinux   = /Linux/.test(ua);
	this.isBSD     = /BSD/.test(ua);
};


Sabel.Object = {
	_cache: new Array(),

	// @todo curry / ignoreMethods などのオプションつけたい
	create: function(object, parent) {
		if (typeof object === "undefined") return {};

		//object = (function() { return this; }).apply(object);
		object = Object(object);

		switch (typeof object) {
		case "function":
			return object;
		case "object":
			var func = function() {};
			func.prototype = object;
			if (parent) Sabel.Object.extend(func.prototype, parent, true);
			if (!func.prototype.isAtomic) Sabel.Object.extend(func.prototype, this.Methods, true);

			var obj = new func;
			if (obj.isAtomic()) {
				obj.toString = function() { return object.toString.apply(object, arguments); };
				obj.valueOf  = function() { return object.valueOf.apply(object, arguments); };
			}
		}

		return obj;
	},

	extend: function(child, parent, curry) {
		for (var prop in parent) {
			if (typeof child[prop] !== "undefined") continue;
			if (typeof parent[prop] !== "function") {
				child[prop] = parent[prop];
			} else {
				child[prop] = (curry === true) ? Sabel.Object._tmp(parent[prop]) : parent[prop];
			}
		}
		
		return child;
	},

	_tmp: function(method) {
		return this._cache[method] = this._cache[method] || function() {
			var args = new Array(this);
			args.push.apply(args, arguments);
			return method.apply(method, args);
		}
	}
};


Sabel.Object.Methods = {};

Sabel.Object.Methods.isAtomic = function(object) {
	switch (object.constructor) {
		case String:
		case Number:
		case Boolean:
			return true;
		default:
			return false;
	}
};

Sabel.Object.Methods.isString = function(object) {
	return object.constructor === String;
};

Sabel.Object.Methods.isNumber = function(object) {
	return object.constructor === Number;
};

Sabel.Object.Methods.isBoolean = function(object) {
	return object.constructor === Boolean;
};

Sabel.Object.Methods.isArray = function(object) {
  return object.constructor === Array;
};

Sabel.Object.Methods.isFunction = function(object) {
	return object.constructor === Function;
};

Sabel.Object.Methods.clone = function(object) {
	return Sabel.Object.create(object);
};

Sabel.Object.Methods.getName = function(object) {
	return object.constructor;
};

Sabel.Object.Methods.hasMethod = function(object, method) {
	return (object[method] !== undefined);
};

Sabel.Object.extend(Sabel.Object, Sabel.Object.Methods);


Sabel.String = function(string) {
	return Sabel.Object.create(string, Sabel.String);
};

Sabel.String.format = function(string, obj) {
	return string.replace(/#\{(\w+)\}/g, function(target, key) { return obj[key]; });
};

Sabel.String.capitalize = function(string) {
	return Sabel.String.ucfirst(string.toLowerCase());
};

Sabel.String.ucfirst = function(string) {
	return string.charAt(0).toUpperCase() + string.substring(1);
};

Sabel.String.lcfirst = function(string) {
	return string.charAt(0).toLowerCase() + string.substring(1);
};

Sabel.String.trim = function(string) {
	return string.replace(/(^\s+|\s+$)/g, "");
};

Sabel.String.camelize = function(string) {
	return string.replace(/-([a-z])/g, function(str, match) {
		return match.toUpperCase()
	});
};

Sabel.String.decamelize = function(string) {
	return string.replace(/\w[A-Z]/g, function(match) {
		return match.charAt(0) + "-" + match.charAt(1).toLowerCase();
	});
};

Sabel.String.truncate = function(string, length, truncation) {
	truncation = truncation || "";
	length = length - truncation.length;

	return string.substring(0, length) + truncation;
};

Sabel.String.times = function(string, count) {
	var tmp = "";
	for (var i = 0; i < count; i++) {
		tmp += string;
	}
	return tmp;
};

Sabel.String.toInt = function(string) {
	return parseInt(string, 10);
};

Sabel.Object.extend(Sabel.String, Sabel.Object.Methods);


Sabel.Array = function(iterable) {
	if (typeof iterable === "undefined") {
		iterable = new Array();
	} else if (iterable.constructor === String) {
		iterable = new Array(iterable);
	} else if (iterable.toArray) {
		iterable = iterable.toArray();
	} else {
		//var iterable = Sabel.Array.map(
		var buf = new Array();
		Sabel.Array.each(iterable, function(v) { buf[buf.length] = v; });
		iterable = buf;
	}

	//return Sabel.Object.create(iterable, Sabel.Array, true);
	return Sabel.Object.extend(iterable, Sabel.Array, true);
};

Sabel.Array.each = function(array, callback) {
	for (var i = 0, len = array.length; i < len; i++) {
		callback(array[i]);
	}
	return array;
};

Sabel.Array.map = function(array, callback) {
	var results = new Array();
	for (var i = 0, len = array.length; i < len; i++) {
		results[i] = callback(array[i]);
	}
	return results;
};

Sabel.Array.callmap = function(array, method) {
	for (var i = 0, len = array. length; i < len; i++) {
		array[i][method]();
	}
	return array;
};

Sabel.Array.include = function(array, value) {
	for (var i = 0, len = array.length; i < len; i++) {
		if (array[i] === value) return true;
	}
	return false;
};

Sabel.Array.sum = function(array) {
	var result = 0;
	for (var i = 0, len = array.length; i < len; i++) {
		result += array[i];
	}
	return result;
};

Sabel.Object.extend(Sabel.Array, Sabel.Object.Methods);


Sabel.Function = function(method) {
	return Sabel.Object.create(method, Sabel.Function);
};

Sabel.Function.bind = function() {
	var args   = Sabel.Array(arguments);
	var method = args.shift(), object = args.shift();

	return function() {
		return method.apply(object, args.concat(Sabel.Array(arguments)));
	}
};

Sabel.Function.bindWithEvent = function() {
	var args   = Sabel.Array(arguments);
	var method = args.shift(), object = args.shift();

	return function(event) {
		return method.apply(object, [event || window.event].concat(args));
	}
};

Sabel.Function.delay = function(method, delay) {
	var args = Sabel.Array(arguments);
	method = args.shift();
	delay  = args.shift() || 1000;
	setTimeout(function() { method.apply(method, args); }, delay);
};

Sabel.Function.curry = function() {
	var args = Sabel.Array(arguments), method = args.shift();

	return function() {
		return method.apply(method, args.concat(Sabel.Array(arguments)));
	}
};

Sabel.Function.restraint = function(method, obj) {
	var methodArgs = Sabel.Function.getArgumentNames(method);
	var arglen     = methodArgs.length;

	var args = new Array(arglen);
	for (var i = 0; i < arglen; i++) {
		args[i] = obj[methodArgs[i]];
	}

	return function() {
		var ary = Sabel.Array(arguments);
		for (var i = 0; i < arglen; i++) {
			if (args[i] === undefined) args[i] = ary.shift();
		}
		method.apply(method, args);
	}
}

Sabel.Function.getArgumentNames = function(method) {
	var str = method.toString();
	argNames = str.match(/^[\s]*function[\s\w]*\((.*)\)/)[1].split(",");
	Sabel.Array.map(argNames, Sabel.String.trim);
	return (argNames[0] === "") ? new Array() : argNames;
};

Sabel.Object.extend(Sabel.Function, Sabel.Object.Methods);


Sabel.Dom = {};

Sabel.Dom.getElementById = function(element, extend) {
	if (typeof element === "string") {
		element = document.getElementById(element);
	}

	return (element) ? (extend === false) ? element : Sabel.Element(element) : null;
};

Sabel.Dom.getElementsByClassName = function(className, element, ext) {
	element = (element) ? Sabel.get(element, false) : document;

	if (element.getElementsByClassNam) {
		return element.getElementsByClassName(className);
	} else {
		var elms = element.getElementsByTagName("*");
		var pat = new RegExp("(?:^|\\s)" + className + "(?:\\s|$)");

		var buf = (ext) ? Sabel.Elements() : new Array();
		Sabel.Array.each(elms, function(elm) {
			if (pat.test(elm.className)) buf.push((ext) ? new Sabel.Element(elm) : elm);
		});
		return buf;
	}
};

Sabel.Dom.getElementsByXPath = function(xpath, context) {
	
};

Sabel.get   = Sabel.Dom.getElementById;
Sabel.find  = Sabel.Dom.getElementsByClassName;
Sabel.xpath = Sabel.Dom.getElementsByXPath;


Sabel.Element = function(element) {
	if (typeof element === "string") {
		element = document.createElement(element);
	} else if (typeof element !== "object") {
		element = Sabel.get(element, false);
	} else if (element._extended === true) {
		return element;
	}
	return Sabel.Object.extend(element, Sabel.Element, true);
};

Sabel.Element._extended = true;

Sabel.Element.get = function(element, id) {
	var elm = Sabel.get(id), parent = elm.parentNode;

	do {
		if (parent == element) return elm;
		if (Sabel.Array.include(["BODY", "HTML"], element.tagName)) break;
	} while (parent = parent.parentNode);

	return null;
};

Sabel.Element.show = function(element) {
	Sabel.get(element, false).style.display = "";
};

Sabel.Element.hide = function(element) {
	Sabel.get(element, false).style.display = "none";
};

Sabel.Element.hasClass = function(element, className) {
	element = Sabel.get(element, false);

	var pattern = new RegExp("(?:^|\\s)" + className + "(?:\\s|$)");
	return pattern.test(element.className);
};

Sabel.Element.getStyle = function(element, property) {
	element = Sabel.get(element, false);

	var style = element.currentStyle || document.defaultView.getComputedStyle(element, "");
	return style[property];
};

// @todo remake this.
Sabel.Element.setStyle = function(element, styles) {
	element = Sabel.get(element, false);

	if (typeof styles === "string") {
		element.style.cssText += ";" + styles;
	} else {
		for (var prop in styles) {
			var method = "set" + Sabel.String(prop).ucfirst();
			if (typeof Sabel.Element[method] !== "undefined") {
				Sabel.Element[method](element, styles[prop]);
			} else {
				element.style[prop] = styles[prop];
			}
		}
	}

	return element;
};

Sabel.Element.setHeight = function(element, value) {
	//return Sabel.get(element, false).style.height = value + "px";
	element = Sabel.get(element, false);
	element.style.height = value + "px";
	return element;
};

Sabel.Element.setOpacity = function(element, value) {
	element = Sabel.get(element, false);

	if (Sabel.UserAgent.isIE) {
		element.style.filter = "alpha(opacity=" + value * 100 + ")";
	} else {
		element.style.opacity = value;
	}
};

Sabel.Element.getCumulativeTop = function(element) {
	element = Sabel.get(element, false);

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
				if (!Sabel.Array.include(["visible", "inherit"], of)) {
					position += border;
				}
			}
		}

		element = parent;
		if (element) {
			if (Sabel.Array.include(["BODY", "HTML"], element.tagName)) break;
		}
	} while (element);

	return position;
};

Sabel.Element.getOffsetTop = function(element) {
	element = Sabel.get(element, false);

	var position = element.offsetTop;

	if (Sabel.UserAgent.isOpera) {
		var parent = element.offsetParent;
		if (parent.nodeName !== "BODY") {
			position  -= parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
		}
	}

	return position;
};

Sabel.Element.getOffsetLeft = function(element) {
	element = Sabel.get(element, false);

	var position = element.offsetLeft;

	if (Sabel.UserAgent.isOpera) {
		var parent = element.offsetParent;
		if (parent.nodeName !== "BODY") {
			position  -= parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
		}
	}

	return position;
};

Sabel.Element.getOffsetPositions = function(element) {
	return {
		left: this.getOffsetLeft(element),
		top:  this.getOffsetTop(element)
	};
};

Sabel.Element.getDimensions = function(element) {
	element = Sabel.get(element, false);

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

	var dimensions = {
		width:  element.clientWidth,
		height: element.clientHeight
	};

	style.visibility = oldV;
	style.positions  = oldP;
	style.display    = oldD;

	return dimensions;
};

Sabel.Element.getWidth = function(element) {
	return Sabel.Element.getDimensions(element).width;
};

Sabel.Element.getHeight = function(element) {
	return Sabel.Element.getDimensions(element).height;
};

Sabel.Element.remove = function(element) {
	element = Sabel.get(element, false);
	element.parentNode.removeChild(element);
};

// @todo remake this.
Sabel.Element.update = function(element, contents) {
	element = Sabel.get(element, false);

	var newEl = document.createElement(element.nodeName);
	newEl.id  = element.id;
	newEl.className = element.className;
	newEl.innerHTML = contents;

	element.parentNode.replaceChild(newEl, element);

	return Sabel.get(newEl);
};

Sabel.Element.observe = function(element, eventName, handler) {
	if (element._events === undefined) element._events = {};
	if (element._events[eventName] === undefined) element._events[eventName] = new Array();

	var evt = new Sabel.Event(element, eventName, handler);
	element._events[eventName].push(evt);

	return evt;
	//return element;
};

// @todo STOPしたイベントをどうする？
Sabel.Element.stopObserve = function(element, eventName, handler) {
	var events = (element._events) ? element._events[eventName] : null;
	if (events.constructor === Array) {
		if (typeof handler === "function") {
			Sabel.Array.each(events, function(e) { if (e.getHandler() === handler) e.stop(); });
		} else {
			Sabel.Array.each(events, function(e) { e.stop(); });
		}
	}

	return element;
};

// @todo refactoring.
Sabel.Element.analyze = function(element) {
	var as = Sabel.get(element, false).attributes;

	if (Sabel.UserAgent.isIE) {
		var def = document.createElement(element.nodeName);

		var buf = new Array(), attr, defAttr, i = 0;
		while (attr = as[i++]) {
			if (typeof attr.nodeValue !== "string") continue;

			defAttr = def.getAttributeNode(attr.nodeName);
			if (defAttr != null && attr.nodeValue === defAttr.nodeValue) continue;

			buf[buf.length] = attr.nodeName + '="' + attr.nodeValue + '"';
		}
	} else {
		var buf = new Array(), attr, i = 0;
		while (attr = as[i++]) {
			buf[buf.length] = attr.nodeName + '="' + attr.nodeValue + '"';
		}
	}

	return buf.join(" ");
};

Sabel.Element.getChildElements = function(element) {
	var buf = new Array();
	Sabel.Array.each(element.childNodes, function(elm) {
		if (elm.nodeType === 1) buf[buf.length] = elm;
	});
	return buf;
};

Sabel.Element.getNextSibling = function(element) {
	while (element = element.nextSibling) {
		if (element.nodeType === 1) return element;
	}
	return null;
};

Sabel.Element.getPreviousSiblings = function(element) {
	var buf = new Array();
	while (element = element.previousSibling) {
		if (element.nodeType === 1) buf[buf.length] = element;
	}
	return buf;
};

Sabel.Element.getNextSiblings = function(element) {
	var buf = new Array();
	while (element = element.nextSibling) {
		if (element.nodeType === 1) buf[buf.length] = element;
	}
	return buf;
};

Sabel.Object.extend(Sabel.Element, Sabel.Object.Methods);


Sabel.Elements = function(elements) {
	if (typeof elements === "undefined") {
		elements = new Sabel.Array();
	} else if (elements._extended === true) {
		return elements;
		// @todo nodelistとかのチェックが必要
	} else if (elements.constructor !== Array) {
		// @todo どうする？
		// @todo stringやElementだとSabel.Element返す？
		// @todo それともthrow Exception?
		return null;
	} else {
		elements = new Sabel.Array(elements);
	}

	return Sabel.Object.extend(elements, Sabel.Elements, true);
};

Sabel.Elements.add = function(elements, element) {
	elements.push(element);
};

Sabel.Elements.item = function(elements, pos) {
	return new Sabel.Element(elements[pos]);
};

Sabel.Elements.observe = function(elements, eventName, handler) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.observe(elm, eventName, handler);
	});
};

Sabel.Elements.stopObserve = function(elements, eventName, handler) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.stopObserve(elm, eventName, handler);
	});
};

Sabel.Elements._extended = true;

Sabel.Object.extend(Sabel.Elements, Sabel.Object.Methods);


Sabel.Iterator = function(iterable) {
	this.items = Sabel.Array(iterable);
	this.index = 0;
};

Sabel.Iterator.prototype.hasNext = function() {
	return this.index < this.items.length-1;
};

Sabel.Iterator.prototype.next = function() {
	return this.items[this.index++];
};


if (typeof window.XMLHttpRequest === "undefined") {
	window.XMLHttpRequest = function() {
		var objects = ["Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.3.0", "Msxml2.XMLHTTP", "Microsoft.XMLHTTP"], http;
		for (var i = 0, len = object.length; i < len; i++) {
			try {
				http = new ActiveXObject(objects[i]);
				window.XMLHttpRequest = function() {
					return new ActiveXOjbect(objects[i]);
				}
			} catch (e) {}
		}
	}
};

Sabel.Ajax = function() {
	this.init.apply(this, arguments);
};

Sabel.Ajax.prototype.init = function() {
	this.xmlhttp   = new XMLHttpRequest();
	this.completed = false;
};

Sabel.Ajax.prototype.request = function(url, options) {
	var xmlhttp = this.xmlhttp;
	var options = this.setOptions(options);

	xmlhttp.onreadystatechange = Sabel.Function.bind(this.onStateChange, this);

	if (options.method === "get") {
		url += ((url.indexOf("?") !== -1) ? "&" : "?") + options.params;
	}

	xmlhttp.open(options.method, url, options.async);
	this.setRequestHeaders();
	xmlhttp.send((options.method === "post") ? options.params : "");
};

Sabel.Ajax.prototype.updater = function(element, url, options) {
	var onComplete = options.onComplete || function() {};
	options.onComplete = function(response) {
		// @todo use sabel.element.update method
		Sabel.get(element).innerHTML = response.responseText;
		onComplete(response);
	}

	this.request(url, options);
};

Sabel.Ajax.prototype.setOptions = function(options) {
	if (options === undefined) options = {};

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
	Sabel.Object.extend(options, defaultOptions);
	options.method = options.method.toLowerCase();
	return (this.options = options);
};

Sabel.Ajax.prototype.setRequestHeaders = function() {
	// @todo */* どうする？
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
		headers = Sabel.Object.extend(options.headers, headers);
	}

	for (var key in headers) {
		xmlhttp.setRequestHeader(key, headers[key]);
	}
};

Sabel.Ajax.prototype.onStateChange = function() {
	if (this.completed === true) return;

	if (this.xmlhttp.readyState === 4) {
		this.completed = true;

		var response = this.getResponses();
		this.options["on" + (this.isSuccess() ? "Success" : "Failure")](response);
		this.options.onComplete(response);
	}
};

Sabel.Ajax.prototype.getResponses = function() {
	var xmlhttp  = this.xmlhttp;
	var response = new Object();
	response.responseXML  = xmlhttp.responseXML;
	response.responseText = this.responseFilter(xmlhttp.responseText);
	response.status = xmlhttp.status;
	response.statusText = xmlhttp.statusText;
	return response;
};

Sabel.Ajax.prototype.isSuccess = function() {
	var status = this.xmlhttp.status;
	return (status && (status >= 200 && status < 300));
};

Sabel.Ajax.prototype.responseFilter = function(text) {
	if (Sabel.UserAgent.isKHTML) {
		var esc = escape(text);
		if (esc.indexOf("%u") < 0 && esc.indexOf("%") > -1) {
			text = decodeURIComponent(esc);
		}
	}
	return text;
};


Sabel.Form = function(form) {
	this.form = Sabel.get(form);

	var elms = this.form.getElementsByTagName("*");
	var buf = {};
	Sabel.Array.each(elms, function(e) {
		var method = Sabel.Form.Elements[e.tagName.toLowerCase()], value;
		if (method && (value = method(e)) !== null) {
			if (buf[e.name]) {
				if (!Sabel.Object.isArray(buf[e.name])) {
					buf[e.name] = [buf[e.name]];
				}
				buf[e.name].push(value);
			} else {
				buf[e.name] = value;
			}
		}
	});

	//this.data = buf;
	this.queryObj = new Sabel.Util.QueryObject(buf);
}

Sabel.Form.prototype.getQueryObj = function() {
	return this.queryObj;
};

Sabel.Form.prototype.has = function(key) {
	return this.queryObj.has(key);
	//return !!(this.data[key] !== undefined);
};

Sabel.Form.prototype.get = function(key) {
	return this.queryObj.get(key);
	return this.data[key] || null;
};

Sabel.Form.prototype.set = function(key, val) {
	return this.queryObj.set(key, val);
	this.data[key] = val;
};

Sabel.Form.prototype.serialize = function() {
	return this.queryObj.serialize();
	/*
	var data = this.data, buf = new Array();
	for (var key in data) {
		if (Sabel.Object.isArray(data[key])) {
			Sabel.Array.each(data[key], function(val) {
				buf[buf.length] = key + "=" + encodeURIComponent(val);
			});
		} else {
			buf[buf.length] = key + "=" + encodeURIComponent(data[key]);
		}
	}

	return buf.join("&");
	*/
};

Sabel.Object.extend(Sabel.Form, Sabel.Object.Methods);

Sabel.Form.Elements = {
	input: function(element) {
		switch(element.type.toLowerCase()) {
		case "radio":
		case "checkbox":
			return (element.checked) ? element.value : null;
		case "submit":
		case "reset":
		case "button":
		case "image":
			return null;
		default:
			return element.value;
		}
	},

	select: function(element) {
		switch (element.type) {
		case "select-multiple":
			var buf = [];
			Sabel.Array.each(element.options, function(el) {
				if (el.selected) buf[buf.length] = el.value;
			});
			return buf;
		default:
			return element.value;
		}
	},

	textarea: function(element) {
		return element.value;
	}
};
Sabel.Event = function(element, eventName, handler) {
	element = Sabel.get(element, false);

	this.element   = element;
	this.eventName = eventName;
	this.handler   = handler;
	this.isActive  = false;
	this.eventId   = Sabel.Events.add(this);

	this.start();
};

Sabel.Event.prototype.start = function() {
	if (this.isActive === false) {
		var element = this.element;

		if (element.addEventListener) {
			element.addEventListener(this.eventName, this.handler, false);
		} else if (element.attachEvent) {
			element.attachEvent("on" + this.eventName, this.handler);
		}
		this.isActive = true;
	}
};

Sabel.Event.prototype.stop = function() {
	if (this.isActive === true) {
		var element = this.element;

		if (element.removeEventListener) {
			element.removeEventListener(this.eventName, this.handler, false);
		} else if (element.detachEvent) {
			element.detachEvent("on" + this.eventName, this.handler);
		}
		this.isActive = false;
	}
};

Sabel.Event.prototype.getHandler = function() {
	return this.handler;
};

Sabel.Event.stopPropagation = function(evt) {
  evt.stopPropagation();
};

Sabel.Event.preventDefault = function(evt) {
  evt.preventDefault();
};

if (Sabel.UserAgent.isIE) {
  Sabel.Event.stopPropagetion = function(evt) {
    (evt || window.event).cancelBubble = true;
  };

  Sabel.Event.preventDefault = function(evt) {
    (evt || window.event).preventDefault = true;
  };
}

Sabel.Events = {};

Sabel.Events._events = new Array();

Sabel.Events.add = function(evtObj) {
	var len = Sabel.Events._events.length;
	Sabel.Events._events[len] = evtObj;

	return len;
};

Sabel.Events.stop = function(eventId) {
	Sabel.Events._events[eventId].stop();
};

Sabel.Events.stopAll = function() {
	var events = Sabel.Events._events;

	Sabel.Array.callmap(events, "stop");
};


Sabel.Effect = function() {
	this.init.apply(this, arguments);
};

Sabel.Effect.prototype = {
	init: function(options) {
		options = options || {};

		this.interval = options.interval || 20;
		this.duration = options.duration || 1000;
		this.step = this.interval / this.duration;
		this.state  = 0;
		this.target = 0;
		this.timer  = null;
		this.effects = Sabel.Array();
	},

	add: function(effect , reverse) {
		reverse = reverse || false;
		this.effects.push({func: effect, reverse: reverse});
	},

	play: function(force) {
		if (this.state === 0 || this.state === 1) {
			this.set(0, 1);
			this._run();
		} else if (force === true) {
			this.set(this.state, 1);
			this._run();
		}
	},

	reverse: function(force) {
		if (this.state === 0 || this.state === 1) {
			this.set(1, 0);
			this._run();
		} else if (force === true) {
			this.set(this.state, 0);
			this._run();
		}
	},

	toggle: function() {
		this.set(this.state, 1 - this.target);
		this._run();
	},

	show: function() {
		this.set(1, 1);
		this.execEffects();
	},

	hide: function() {
		this.set(0, 0);
		this.execEffects();
	},

	set: function(from, to) {
		this.state  = from;
		this.target = to;
		this._clear();
	},

	execEffects: function() {
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func((ef.reverse === true) ? 1 - state : state);
		});
	},

	_run: function() {
		this.timer = setInterval(Sabel.Function.bind(this._exec, this), this.interval);
	},

	_exec: function() {
		var mv = (this.state > this.target ? -1 : 1) * this.step;
		this.state += mv;
		if (this.state >= 1 || this.state <= 0) {
			this.set(this.target, this.target);
		}

		this.execEffects();
	},

	_clear: function() {
		clearInterval(this.timer);
	}
};
Sabel.Util = {};

Sabel.dump = function(element, limit, output)
{
	var br = (Sabel.UserAgent.isIE) ? "\r" : "\n";
	limit  = limit || 1;

	if (!output) {
		output = document.createElement("pre");
		output.style.border = "1px solid #ccc";
		output.style.color  = "#333";
		output.style.background = "#fff";
		output.style.margin = "5px";
		output.style.padding = "5px";
		document.body.appendChild(output);
	}

	output.appendChild(document.createTextNode((function(element, ind) {
		var indent = Sabel.String.times("  ", ind);
		if (Sabel.Element.isString(element)) {
			return "string(" + element.length + ') "' + element + '"';
		} else if (Sabel.Element.isNumber(element)) {
			return "int(" + element + ")";
		} else if (Sabel.Element.isBoolean(element)) {
			return "bool(" + element + ")";
		} else if (Sabel.Element.isFunction(element)) {
			return element.toString().replace(/(\n)/g, br + Sabel.String.times("  ", ind-1));
		} else if (Sabel.Element.isAtomic(element)) {
			return element;
		} else {
			if (ind > limit) return element + "...";

			var buf = new Array();
			buf[buf.length] = "object() {";
			for (var key in element) {
				try {
					buf[buf.length] = indent + '["' + key + '"]=>' + br +
					indent + arguments.callee(element[key], ind+1);
				} catch (e) {}
			}
			buf[buf.length] = Sabel.String.times("  ", ind-1)+ "}";
			return buf.join(br);
		}
	})(element, 1)));
}


Sabel.Effect.Fade = function(element) {
	element = Sabel.get(element, false);

	return function(state) {
		Sabel.Element.setOpacity(element, state);
	}
};


Sabel.Effect.Slide = function(element) {
	element = Sabel.get(element);
	var maxHeight = element.getHeight();

	return function(state) {
		var height = state * maxHeight;

		element.setStyle({height: height});

		if (height === 0) {
			if (element.getStyle("visibility") !== "hidden") {
				element.setStyle({visibility: "hidden"});
			}
		} else {
			if (element.getStyle("visibility") !== "visible") {
				element.setStyle({visibility: "visible"});
			}
		}
	}
};


Sabel.Util.QueryObject = function(object) {
	this.data = object;
};

Sabel.Util.QueryObject.prototype.has = function(key) {
	return !!(this.data[key] !== undefined);
};

Sabel.Util.QueryObject.prototype.get = function(key) {
	return this.data[key] || null;
};

Sabel.Util.QueryObject.prototype.set = function(key, val) {
	this.data[key] = val;
};

Sabel.Util.QueryObject.prototype.serialize = function() {
	var data = this.data, buf = new Array();
	for (var key in data) {
		if (Sabel.Object.isArray(data[key])) {
			Sabel.Array.each(data[key], function(val) {
				buf[buf.length] = key + "=" + encodeURIComponent(val);
			});
		} else {
			buf[buf.length] = key + "=" + encodeURIComponent(data[key]);
		}
	}

	return buf.join("&");
};

Sabel.Util.Uri = function(uri)
{
	uri = uri || location.href;

	function parse(query)
	{
		if (query === undefined) return {};
		var querys = query.split("&"), parsed = {};

		for (var i = 0, len = querys.length; i < len; i++) {
			if (querys[i] == "") continue;
			var q = querys[i].split("=");
			parsed[q[0]] = q[1] || "";
		}

		return new Sabel.Util.QueryObject(parsed);
	}

	var result = Sabel.Util.Uri.pattern.exec(uri);

	for (var i = 0, len = result.length; i < len; i++) {
		this[Sabel.Util.Uri.keyNames[i]] = result[i] || "";
	}
	this['parseQuery'] = parse(this.query);
}

Sabel.Util.Uri.pattern  = /^(\w+):\/\/(?:(\w+)(?::(\w+))?@)?([^:\/]*)(?::(\d+))?(?:([^?#]+?)(?:\/(\w+\.\w+))?)?(?:\?((?:[^&#]+)(?:&[^&#]*)*))?(?:#([^#]+))?$/;
Sabel.Util.Uri.keyNames = ['url', 'protocol', 'user', 'password', 'domain', 'port', 'directory', 'filename', 'query', 'hash'];

Sabel.Util.Uri.prototype = {
	// @todo remove ?
	has: function(key)
	{
		return this.parseQuery.has(key);
	},

	getQueryObj: function() {
		return this.parseQuery;
	}
}

Sabel.DragAndDrop = function() { this.initialize.apply(this, arguments); };

Sabel.DragAndDrop.prototype = {
  initialize: function(element, options)
  {
    options = options || {};
    element = Sabel.get(element, false);

    element.style.cursor = options.cursor || "move";
    //element.style.position = "absolute";

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
		e = e || window.event;
    if (Sabel.UserAgent.isIE) {
      e.returnValue = false;  // IE Hack.
    } else {
      e.preventDefault(); // Opera & Fx Hack.
    }

    this.startPos = Sabel.Element.getOffsetPositions(this.element);
    this.startX   = e.clientX;
    this.startY   = e.clientY;
    
    this.element.style.zIndex = "10000";

		var self = this;
    this.observe(document, "mousemove", function(e) { self.mouseMove(e) });
    this.observe(document, "mouseup", function(e) { self.mouseUp(e) });

    if (this.options.startCallback !== null) this.options.startCallback(this.element, e);
  },

  mouseUp: function(e)
  {
		e = e || window.event;
    this.element.style.zIndex = "1";
    this.stopObserve(document, "mousemove");
    this.stopObserve(document, "mouseup");

    if (this.options.endCallback !== null) this.options.endCallback(this.element, e);
    return false;
  },

  mouseMove: function(e)
  {
		e = e || window.event;
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


Sabel.widget = {};

Sabel.widget.Overlay = function(option) {
	this.div = document.createElement("div");
	this.div.setAttribute("id", option.id);
	this.div.style.cssText += "; background-color: #000; position: absolute; top: 0px; left: 0px; opacity: 0.70; -moz-opacity: 0.70; filter: alpha(opacity=70); z-index: 100;";

	this.setStyle();
	document.body.appendChild(this.div);
};

Sabel.widget.Overlay.prototype = {
	div: null,

	setStyle: function() {
		var height = Sabel.Window.getScrollHeight();
		var width  = Sabel.Window.getScrollWidth();
		/*
		if (window.innerHeight != undefined && window.scrollMaxY != undefined) { // Fx
			var height = window.innerHeight + window.scrollMaxY;
			var width  = window.innerWidth  + window.scrollMaxX;
		} else {
			if (document.compatMode == "CSS1Compat") {
			} else {
			}
			if (document.body.clientHeight > document.body.scrollHeight) { // IE Strict
				var height = document.body.clientHeight;
			} else if (document.body.offsetHeight > document.body.scrollHeight) {
				var height = document.body.offsetHeight;
			} else if (document.documentElement.clientHeight > document.body.scrollHeight) { // Opera, Safari
				var height = document.documentElement.clientHeight;
			} else {
				var height = document.body.scrollHeight;
			}
			var width = document.body.scrollWidth;
		}
		*/
		this.div.style.width  = width  + "px";
		this.div.style.height = height + "px";
		/*
		alert(document.body.scrollWidth);
		alert(document.documentElement.scrollWidth);
		*/
	}
};


