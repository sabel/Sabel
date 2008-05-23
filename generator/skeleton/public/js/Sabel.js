/**
 * SabelJS 
 * Header
 *
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Hamanaka Kazuhiro <Hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

window.Sabel = {};

Sabel.PHP = {};

Sabel.emptyFunc = function() {};

Sabel.QueryObject = function(object) {
	this.data = object;
};

Sabel.QueryObject.prototype = {
	has: function(key) {
		return !!(this.data[key] !== undefined);
	},

	get: function(key) {
		return this.data[key] || null;
	},

	set: function(key, val) {
		this.data[key] = val;
		return this;
	},

	serialize: function() {
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
	}
};
Sabel.Uri = function(uri)
{
	uri = uri || location.href;

	function parse(query)
	{
		if (query === undefined) return {};
		var queries = query.split("&"), parsed = {};

		for (var i = 0, len = queries.length; i < len; i++) {
			if (queries[i] == "") continue;
			var q = queries[i].split("=");
			parsed[q[0]] = q[1] || "";
		}

		return new Sabel.QueryObject(parsed);
	}

	var result = Sabel.Uri.pattern.exec(uri);

	for (var i = 0, len = result.length; i < len; i++) {
		this[Sabel.Uri.keyNames[i]] = result[i] || "";
	}
	this['parseQuery'] = parse(this.query);
};

Sabel.Uri.pattern  = /^((\w+):\/\/(?:(\w+)(?::(\w+))?@)?([^:\/]*)(?::(\d+))?)(?:([^?#]+?)(?:\/(\w+\.\w+))?)?(?:\?((?:[^&#]+)(?:&[^&#]*)*))?(?:#([^#]+))?$/;
Sabel.Uri.keyNames = ['uri', 'url', 'protocol', 'user', 'password', 'domain', 'port', 'path', 'filename', 'query', 'hash'];

Sabel.Uri.prototype = {
	has: function(key)
	{
		return this.parseQuery.has(key);
	},

	get: function(key)
	{
		return this.parseQuery.get(key);
	},

	set: function(key, value)
	{
		this.parseQuery.set(key, value);
		return this;
	},

	getQueryObj: function() {
		return this.parseQuery;
	},

	toString: function() {
		return this.url + this.path + "/" + this.filename + "?" + this.parseQuery.serialize();
	}
};

Sabel.Environment = (function() {
	var scripts = document.getElementsByTagName("script");
	var uri = new Sabel.Uri(scripts[scripts.length - 1].src);

	this._env = parseInt(uri.get("ENVIRONMENT"));

	return this;
})();
Sabel.Environment.PRODUCTION  = 10;
Sabel.Environment.TEST        = 5;
Sabel.Environment.DEVELOPMENT = 1;

Sabel.Environment.isDevelopment = function() {
	return this._env === Sabel.Environment.DEVELOPMENT;
};

Sabel.Environment.isTest = function() {
	return this._env === Sabel.Environment.TEST;
};

Sabel.Environment.isProduction = function() {
	return this._env === Sabel.Environment.PRODUCTION;
};

Sabel.Window = {
	getWidth: function() {
		if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
			return document.body.clientWidth;
		} else if (Sabel.UserAgent.isSafari) {
			return window.innerWidth;
		} else {
			return document.documentElement.clientWidth;
		}
	},

	getHeight: function() {
		if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
			return document.body.clientHeight;
		} else if (Sabel.UserAgent.isSafari) {
			return window.innerHeight;
		} else {
			return document.documentElement.clientHeight;
		}
	},

	getScrollWidth: function() {
		if (document.compatMode === "CSS1Compat") {
			var width = document.documentElement.scrollWidth;
		} else {
			var width = document.body.scrollWidth;
		}
		var clientWidth = Sabel.Window.getWidth();
		return (clientWidth > width) ? clientWidth : width;
	},

	getScrollHeight: function() {
		if (document.compatMode === "CSS1Compat") {
			var height = document.documentElement.scrollHeight;
		} else {
			var height = document.body.scrollHeight;
		}
		var clientHeight = Sabel.Window.getHeight();
		return (clientHeight > height) ? clientHeight : height;
	},

	getScrollLeft: function() {
		if (document.compatMode === "CSS1Compat") {
			return document.documentElement.scrollLeft;
		} else {
			return document.body.scrollLeft;

		}
	},

	getScrollTop: function() {
		if (document.compatMode === "CSS1Compat") {
			return document.documentElement.scrollTop;
		} else {
			return document.body.scrollTop;
		}
	}
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

Sabel.Window.lineFeedCode = (Sabel.UserAgent.isIE) ? "\r" : "\n";


Sabel.Object = {
	_cache: new Array(),

	create: function(object, parent) {
		if (typeof object === "undefined") return {};

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


Sabel.Object.Methods = {

	isAtomic: function(object) {
		switch (object.constructor) {
		case String:
		case Number:
		case Boolean:
			return true;
		default:
			return false;
		}
	},

	isString: function(object) {
		return object.constructor === String;
	},

	isNumber: function(object) {
		return object.constructor === Number;
	},

	isBoolean: function(object) {
		return object.constructor === Boolean;
	},

	isArray: function(object) {
		return object.constructor === Array;
	},

	isFunction: function(object) {
		return object.constructor === Function;
	},

	clone: function(object) {
		return Sabel.Object.create(object);
	},

	getName: function(object) {
		return object.constructor;
	},

	hasMethod: function(object, method) {
		return (object[method] !== undefined);
	}
};

Sabel.Object.extend(Sabel.Object, Sabel.Object.Methods);
Sabel.Class = function() {
	if (typeof arguments[0] === "function") {
		var superKlass = arguments[0];
	} else {
		var superKlass = function() {};
	}
	var methods = Array.prototype.pop.call(arguments) || Sabel.Object;

	var tmpKlass = function() {};
	tmpKlass.prototype = superKlass.prototype;

	var subKlass = function() {
		this.__super__ = superKlass;
		if (typeof methods.init === "function") {
			methods.init.apply(this, arguments);
		} else {
			this.__super__.apply(this, arguments);
		}
		delete this.__super__;
	}

	subKlass.prototype = new tmpKlass;
	switch (subKlass.prototype.constructor) {
	case String: case Number: case Boolean:
		subKlass.prototype.toString = function() {
			return superKlass.toString.apply(superKlass, arguments);
		};
		subKlass.prototype.valueOf  = function() {
			return superKlass.valueOf.apply(superKlass, arguments);
		};
	}

	if (methods) {
		for (var name in methods) subKlass.prototype[name] = methods[name];

		var ms = ["toString", "valueOf"];
		for (var i = 0, len = ms.length; i < len; i++) {
			if (methods.hasOwnProperty(ms[i]))
				subKlass.prototype[ms[i]] = methods[ms[i]];
		}

		subKlass.prototype.constructor = subKlass;
		return subKlass;
	}
};

Sabel.String = new Sabel.Class(String, {
	init: function(string) {
		this._string = string;
	},

	toString: function() {
		return this._string;
	},

	valueOf: function() {
		return this._string;
	},

	_set: function(string) {
		return this._string = string;
	},

	chr: function() {
		return this._set(String.fromCharCode.apply(String, this._string.split(',')));
	},

	explode: function(delimiter) {
		return this._string.split(delimiter);
	},

	lcfirst: function() {
		var str = this._string;
		return this._set(str.charAt(0).toLowerCase() + str.substring(1));
	},

	ltrim: function() {
		return this._set(this._string.replace(/^\s+/, ""));
	},

	nl2br: function() {
		return this._set(this._string.replace(/(\r?\n)/g, "<br/>$1"));
	},

	ord: function() {
		return this._set(this._string.charCodeAt(0));
	},

	rtrim: function() {
		return this._set(this._string.replace(/\s+$/, ""));
	},

	repeat: function(multiplier) {
		var tmp = "";
		for (var i = 0; i < multiplier; i++) {
			tmp += this._string;
		}
		return this._set(tmp);
	},

	shuffle: function() {
		var tmp = this._string.split("");
		var i = tmp.length;

		while (i) {
			var j = Math.floor(Math.random() * i);
			var t = tmp[--i];
			tmp[i] = tmp[j];
			tmp[j] = t;
		}
		return tmp.join("");
	},

	sprintf: function(/* mixed args */) {
		var args = arguments;

		var i = 0, v, o;

		var pattern = /(^|[^%])%(?:([0-9]+)\$)?(-)?([0]|\'.)?([0-9]*)(?:\.([0-9]+))?([bcdfFosxX])/g
		var replaced = this.replace(pattern, function(all, pre, key, sign, padding, alignment, precision, match) {
			v = (key) ? args[--key] : args[i++];

			if (precision) precision = parseInt(precision);
			switch (match) {
			case "b":
				v = v.toString(2);
				break;
			case "c":
				v = String.fromCharCode(v);
				break;
			case "f": case "F":
				if (precision) v = parseFloat(v).toFixed(precision);
				break;
			case "o":
				v = v.toString(8);
				break;
			case "s":
				v = v.substring(0, precision || v.length);
				break;
			case "x":
				v = v.toString(16);
				break;
			case "X":
				v = v.toString(16).toUpperCase();
				break;
			}

			if (alignment) {
				var len = alignment - v.toString().length;
				var t = new Sabel.String(padding.charAt(padding.length - 1) || " ").repeat(len);

				v = (sign === "-") ? v + t : t + v;
			}

			return pre + v;
		});

		return replaced;
	},

	trim: function() {
		var str = this._string;
		return this._set(str.replace(/(^\s+|\s+$)/g, ""));
	},

	format: function(obj) {
		var pat = /(?:#\{(\w+)\}|%(\w+)%)/g;
		return this._string.replace(pat, function(target, key) { return obj[key] || ""; });
	},

	ucfirst: function() {
		var str = this._string;
		return this._set(str.charAt(0).toUpperCase() + str.substring(1));
	},


	capitalize: function() {
		var str = this._string;
		return this.ucfirst(str.toLowerCase());
	},

	camelize: function() {
		var str = this._string;
		return this._set(str.replace(/-([a-z])/g, function(dummy, match) {
			return match.toUpperCase();
		}));
	},

	decamelize: function() {
		return this._set(this._string.replace(/\w[A-Z]/g, function(match) {
			return match.charAt(0) + "-" + match.charAt(1).toLowerCase();
		}));
	},

	truncate: function(length, truncation) {
		truncation = truncation || "";

		return this._set(this._string.substring(0, length) + truncation);
	},

	clean: function() {
		return this._set(this._string.replace(/\s{2,}/g, " "));
	},

	toInt: function() {
		return parseInt(this._string, 10);
	},

	toFloat: function() {
		return parseFloat(this._string);
	}
});

Sabel.String.prototype.chop = Sabel.String.prototype.rtrim;
Sabel.String.prototype.times = Sabel.String.prototype.repeat;

var methods = [
	"anchor", "big", "blink", "bold", "charAt", "charCodeAt", "concat",
	"decodeURI", "decodeURI_Component", "encodeURI", "encodeURI_Component",
	"enumerate", "escape", "fixed", "fontcolor", "fontsize", "fromCharCode",
	"getProperty", "indexOf", "italics", "lastIndexOf", "link", "localeCompare",
	"match", "replace", "resolve", "search", "slice", "small", "split", "strike",
	"sub", "substr", "substring", "sup", "toLocaleLowerCase", "toLocaleUpperCase",
	"toLowerCase", "toSource", "toUpperCase", "unescape", "uneval"
];
for (var i = 0, len = methods.length; i < len; i++) {
	var method = methods[i];
	Sabel.String.prototype[method] = (function(method) {
		return function() {
			return this._set(method.apply(this, arguments));
		}
	})(String.prototype[method]);
};
Sabel.Number = function(number) {
	return Sabel.Object.create(number, Sabel.Number)
};

Sabel.Number._units = ["", "k", "M", "G", "T", "P", "E", "Z", "Y"];
Sabel.Number.toHumanReadable = function(number, unit, ext) {
	if (typeof number !== "number") throw "number is not Number object.";
	var i = 0;

	while (number > unit) {
		i++;
		number = number / unit;
	}

	return number.toFixed(1) + Sabel.Number._units[i];
};

Sabel.Number.between = function(number, min, max) {
	return number >= min && number <= max;
};
Sabel.Array = function(iterable) {
	if (typeof iterable === "undefined") {
		iterable = new Array();
	} else if (iterable.constructor === String) {
		iterable = new Array(iterable);
	} else if (iterable.toArray) {
		iterable = iterable.toArray();
	} else {
		var buf = new Array();
		Sabel.Array.each(iterable, function(v) { buf[buf.length] = v; });
		iterable = buf;
	}

	return Sabel.Object.extend(iterable, Sabel.Array, true);
};

Sabel.Array.each = function(array, callback) {
	for (var i = 0, len = array.length; i < len; i++) {
		callback(array[i], i);
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

Sabel.Array.concat = function(array, iterable) {
	if (iterable.length === undefined) return array;

	if (iterable.toArray) iterable = iterable.toArray();

	Sabel.Array.each(iterable, function(data) {
		array[array.length] = data;
	});
	return array;
};

Sabel.Array.inject = function(array, method) {
	var buf = new Array();
	Sabel.Array.each(array, function(data) {
		if (method(data) === true) buf[buf.length] = data;
	});
	return buf;
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
};

Sabel.Function.getArgumentNames = function(method) {
	var str = method.toString();
	argNames = str.match(/^[\s]*function[\s\w]*\((.*)\)/)[1].split(",");
	for (var i = 0, len = argNames.length; i < len; i++) {
		new Sabel.String(argNames[i]).trim();
	}
	return (argNames[0] === "") ? new Array() : argNames;
};

Sabel.Object.extend(Sabel.Function, Sabel.Object.Methods);


Sabel.Dom = {

	getElementById: function(element, extend) {
		if (typeof element === "string") {
			element = document.getElementById(element);
		}

		return (element) ? (extend === false) ? element : Sabel.Element(element) : null;
	},

	getElementsByClassName: function(className, element, ext) {
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
	},

	getElementsBySelector: function(selector) {
		if (document.querySelectorAll) {
			try {
				var elms = [];
				Sabel.Array.each(document.querySelectorAll(selector), function(el) {
					elms.push(el);
				});
				return Sabel.Elements(elms);
			} catch (e) {}
		}
		var s = Sabel.Dom.Selector;
		var selectors = selector.split(",");
		var elms = [];
		Sabel.Array.each(selectors, function(query) {
			var method = s._cache[query] || (s._cache[query] = s.convertToJSCode(query));
			elms = elms.concat(method([document]));
		});
		return Sabel.Elements(Sabel.Elements.unique(elms));
	}
};

Sabel.get   = Sabel.Dom.getElementById;
Sabel.find  = Sabel.Dom.getElementsBySelector;




Sabel.Dom.Selector = {
	patterns: {
		tagName: /^(\*|\w+)/,
		combinator: /^\s*(>|\+|~)\s*/,
		id: /^#(\w+)/,
		className: /^\.(\w+)/,
		pseudo: /^:([\w\-]+)(?:\(([^)]+)\))?/,
		attr: /^\[(\w+)([!~^$*|]?=)?([\'\"])?([^\'\"\]]+)?\3\]/,
		space: /^\s+/
	},

	cs: {
		xpath: {
		},

		base: {
			tagName: 'nodes = h.tagName(nodes, "#{1}", f); f = true;',
			combinator: 'nodes = h.combinator(nodes, "#{1}"); f = true;',
			id: 'nodes = h.id(nodes, "#{1}", f); f = false;',
			className: 'nodes = h.className(nodes, "#{1}", f); f = false;',
			pseudo: 'nodes = h.pseudo["#{1}"](nodes, "#{2}"); f = false;',
			attr: 'nodes = h.attr(nodes, "#{1}", "#{2}", "#{4}");',
			space: 'f = false;'
		}
	},

	handlers: {
		tagName: function(nodes, tagName, f) {
			var buf = [];
			if (f === true) {
				buf = Sabel.Array.inject(nodes, function(node) {
					if (node.tagName === tagName.toUpperCase()) return true;
				});
			} else {
				Sabel.Array.each(nodes, function(node) {
					buf = Sabel.Array.concat(buf, node.getElementsByTagName(tagName));
				});
			}
			return Sabel.Elements.unique(buf);
		},

		combinator: function(nodes, combName) {
			var buf = [];
			switch(combName) {
			case ">":
				Sabel.Array.each(nodes, function(node) {
					buf = Sabel.Array.concat(buf, Sabel.Element.getChildElements(node));
				});
				break;
			case "~":
				Sabel.Array.each(nodes, function(node) {
					if (node.__searched === true) return;
					while (node = node.nextSibling) {
						if (node.nodeType === 1) {
							buf.push(node);	
							node.__searched = true;
						}
					}
				});
				Sabel.Array.each(buf, function(node) {
					node.__searched = false;
				});
				break;
			case "+":
				Sabel.Array.each(nodes, function(node) {
					if (el = Sabel.Element.getNextSibling(node)) buf[buf.length] = el;
				});
				break;
			}
			return Sabel.Elements.unique(buf);
		},

		id: function(nodes, id, f) {
			if (f === true) {
				var buf = Sabel.Array.inject(nodes, function(node) {
					if (node.id === id) return true;
				});
				return Sabel.Elements.unique(buf);
			} else {
				var el = document.getElementById(id);
				for (var i = 0; i < nodes.length; i++) {
					if (Sabel.Element.contains(nodes[i], el)) return [el];
				}
				return [];
			}
		},

		className: function(nodes, className, f) {
			if (f === true) {
				var buf = Sabel.Array.inject(nodes, function(node) {
					if (Sabel.Element.hasClass(node, className)) return true;
				});
			} else {
				var buf = [];
				Sabel.Array.each(nodes, function(node) {
					buf = Sabel.Array.concat(buf, Sabel.Dom.getElementsByClassName(className, node, false));
				});
			}
			return Sabel.Elements.unique(buf);
		},

		pseudo: {
			root: function(nodes) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					if (node.parentNode === document) buf[buf.length] = node;
				});
				return buf;
			},

			"first-child": function(nodes) {
				var buf = [];
				Sabel.Array.each(nodes, function(node) {
					if (!Sabel.Element.getPreviousSibling(node)) buf[buf.length] = node;
				});
				return buf;
			},

			"last-child": function(nodes) {
				var buf = [];
				Sabel.Array.each(nodes, function(node) {
					if (!Sabel.Element.getNextSibling(node)) buf.push(node);
				});
				return buf;
			},

			"nth-child": function(nodes, pos) {
				if (pos === "odd") pos = "2n+1";
				else if (pos === "even") pos = "2n";

				var buf = new Array();
				if (ms = pos.match(/^(0n\+)?(\d+)$/)) {
					Sabel.Array.each(nodes, function(node) {
						var el = node.parentNode.firstChild, i = 0;
						while (el && i < ms[2]) {
							el = el.nextSibling;
							if (el.nodeType == 1) i++;
						}
						if (el == node) buf.push(node);
					});
					return buf;
				} else if (ms = pos.match(/^([+-])?(\d*)n([+-]\d+)?$/)) {
					var a = ms[2] || 1, b = new Sabel.String(ms[3] || 0).toInt();

					Sabel.Array.each(nodes, function(node) {
						var p = Sabel.Element.getNodeIndex(node);
						var i = p - b;
						if (ms[1] === "-") i = i * -1;
						if (i >= 0 && (i % a) === 0) buf.push(node);
					});
					return buf;
				}
			},

			"nth-last-child": function(nodes, pos) {
				return this.nth(nodes, pos, true);
			},

			"first-of-type": function(nodes) {
				return this.nth(nodes, "1", false, true);
			},

			"last-of-type": function(nodes) {
				return this.nth(nodes, "1", true, true);
			},

			"nth-of-type": function(nodes, pos) {
				return this.nth(nodes, pos, false, true);
			},

			"nth-last-of-type": function(nodes, pos) {
				return this.nth(nodes, pos, true, true);
			},

			"nth": function(nodes, pos, reverse, ofType) {
				if (pos === "odd") pos = "2n+1";
				else if (pos === "even") pos = "2n";

				var buf = new Array();
				if (ms = pos.match(/^(0n\+)?(\d+)$/)) {
					Sabel.Array.each(nodes, function(node) {
						var p = Sabel.Element.getNodeIndex(node, reverse, ofType);

						if (p == ms[2]) buf.push(node);
					});
				} else if (ms = pos.match(/^([+-])?(\d*)n([+-]\d+)?$/)) {
					var a = ms[2] || 1, b = new Sabel.String(ms[3] || 0).toInt();

					Sabel.Array.each(nodes, function(node) {
						var p = Sabel.Element.getNodeIndex(node, reverse, ofType);
						var i = p - b;
						if (ms[1] === "-") i = i * -1;
						if (i >= 0 && (i % a) === 0) buf.push(node);
					});
				}
				return buf;
			},


			"only-child": function(nodes) {
				var buf = new Array();

				Sabel.Array.each(nodes, function(node) {
					if (!Sabel.Element.getPreviousSibling(node) && !Sabel.Element.getNextSibling(node)) {
						buf.push(node);
					}
				});

				return buf;
			},

			"only-of-type": function(nodes) {
				var buf = new Array();

				Sabel.Array.each(nodes, function(node) {
					var elms = node.parentNode.childNodes, i = 0, elm, f = 0;
					while (elm = elms[i++]) {
						if (elm.tagName === node.tagName) f++;
						if (f > 1) return false;
					}
					buf.push(node);
					return;
					var elms = Sabel.Element.getChildElements(node.parentNode, node.tagName);

					if (elms.length === 1) buf.push(node);
				});

				return buf;
			},

			contains: function(nodes, text) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					var t = node.textContent || node.innerText || "";

					if (t.indexOf(text) >= 0) buf.push(node);
				});

				return buf;
			},

			empty: function(nodes) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					if (node.childNodes.length === 0) buf.push(node);
				});
				return buf;
			},

			"not": function(nodes, selector) {
				var elms = Sabel.Dom.Selector.convertToJSCode(selector, true)(nodes), buf=[];
				
				for (var i = 0, len = elms.length, elm; elm = elms[i]; i++) {
					elm._marked = true;
				}
				for (var i = 0, len = nodes.length, elm; elm = nodes[i]; i++){
					if (!elm._marked) buf.push(elm);
					elm._marked = false;
				}
				return buf;
			},

			lang: function(nodes, lang) {
				var buf = new Array();
				var pattern = new RegExp("^" + lang + "(-|$)");

				Sabel.Array.each(nodes, function(node) {
					var nodeLang = node.getAttribute("lang");
					if (nodeLang && pattern.test(nodeLang)) buf.push(node);
				});

				return buf;
			},

			enabled: function(nodes) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					if (node.disabled === false) buf.push(node);
				});
				return buf;
			},

			disabled: function(nodes) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					if (node.disabled === true) buf.push(node);
				});
				return buf;
			},

			checked: function(nodes) {
				var buf = new Array();
				Sabel.Array.each(nodes, function(node) {
					if (node.checked === true) buf.push(node);
				});
				return buf;
			}
		},

		attr: function(nodes, key, op, value) {
			var self = this;
			var buf = new Array();
			if (op) {
				Sabel.Array.each(nodes, function(node) {
					if (self.operators[op](node.getAttribute(key)||"", value) === true) buf.push(node);
				});
			} else {
				Sabel.Array.each(nodes, function(node) {
					if (Sabel.Element.hasAttribute(node, key) === true) buf.push(node);
				});
			}
			return buf;
		},

		operators: {
			"default": function(a) { return a !== null; },
			"=":  function(a, b) { return a === b; },
			"!=": function(a, b) { return a !== b; },
			"~=": function(a, b) { return (" "+a+" ").indexOf(" "+b+" ") >= 0; },
			"^=": function(a, b) { return a.indexOf(b) === 0; },
			"$=": function(a, b) { return a.indexOf(b) === (a.length - b.length); },
			"*=": function(a, b) { return a.indexOf(b) >= 0; },
			"|=": function(a, b) { return (a+"-").toLowerCase().indexOf((b+"-").toLowerCase()) === 0; }
		}
	}
};

Sabel.Dom.Selector.convertToJSCode = function(selector, force) {
	var patterns = Sabel.Dom.Selector.patterns;
	var cs = Sabel.Dom.Selector.cs.base;
	var prev, pattern, m;
  // @todo bug fix;
	var buf = ['sbl_func = function(nodes) { var h = Sabel.Dom.Selector.handlers, f = '+(force||'false')+';'];

	while (selector && selector !== prev) {
		prev = selector;

		for (var prop in patterns) {
			pattern = patterns[prop];

			if (m = selector.match(pattern)) {
				buf.push(new Sabel.String(cs[prop]).format(m));
				selector = selector.replace(m[0], "");
				break;
			}
		}
	}
	buf.push("return nodes; }");

	return eval(buf.join(""));
};

Sabel.Dom.Selector._cache = {};
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

	var pattern = new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)");
	return pattern.test(element.className);
};

Sabel.Element.addClass = function(element, className) {
	if (Sabel.Element.hasClass(element, className)) return element;

	element = Sabel.get(element, false);
	element.className = element.className + " " + className;
};

Sabel.Element.removeClass = function(element, className) {
	element = Sabel.get(element, false);
	element.className = element.className.replace(
		new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)"), " "
	);

	return element;
};

Sabel.Element.hasAttribute = function(element, attribute) {
	element = Sabel.get(element, false);
	if (element.hasAttribute) return element.hasAttribute(attribute);
	var node = element.getAttributeNode(attribute);
	return node && node.specified;
};

if (Sabel.UserAgent.isIE) {
	Sabel.Element.getStyle = function(element, property) {
		element = Sabel.get(element, false);
		property = (property === "float") ? "styleFloat" : new Sabel.String(property).camelize();

		var style = element.currentStyle;
		return style[property];
	};
} else {
	Sabel.Element.getStyle = function(element, property) {
		element = Sabel.get(element, false);
		// Operaでelementがwindowだった時の対策
		if (element.nodeType === undefined) return null;
		property = (property === "float") ? "cssFloat" : new Sabel.String(property).camelize();

		var css = document.defaultView.getComputedStyle(element, "")
		return css[property];
	};
}

Sabel.Element.setStyle = function(element, styles) {
	element = Sabel.get(element, false);

	if (typeof styles === "string") {
		element.style.cssText += ";" + styles;
	} else {
		for (var prop in styles) {
			var method = "set" + new Sabel.String(prop).ucfirst();
			if (typeof Sabel.Element[method] !== "undefined") {
				Sabel.Element[method](element, styles[prop]);
			} else {
				element.style[prop] = styles[prop];
			}
		}
	}

	return element;
};

Sabel.Element.deleteStyle = function(element, styles) {
	element = Sabel.get(element, false);

	if (typeof styles === "string") {
		element.style[styles] = "";
	} else {
		for (var i = 0, key; key = styles[i]; i++) {
			element.style[key] = "";
		}
	}

	return element;
};

Sabel.Element.insertAfter = function(element, newChild, refChild) {
	elemenet = Sabel.get(element, false);
	if (element.lastChild == refChild) {
		element.appendChild(newChild);
	} else {
		element.insertBefore(newChild, refChild.nextSibling);
	}
	return element;
};

Sabel.Element.setHeight = function(element, value) {
	element = Sabel.get(element, false);
	if (value !== "" && typeof value === "number") value = value + "px";
	element.style.height = value;
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

			if (Sabel.UserAgent.isMozilla) {
				var of = Sabel.Element.getStyle(parent, "overflow");
				if (!Sabel.Array.include(["visible", "inherit"], of)) {
					position += border;
				}
			}
		}

		element = parent;
		if (element) {
			if (Sabel.Array.include(["BODY", "HTML"], element.tagName)) {
				if (Sabel.UserAgent.isIE) {
					position += parseInt(Sabel.Element.getStyle(element, "marginTop")) || 0;
				}
				break;
			}
		}
	} while (element);

	return position;
};

Sabel.Element.getCumulativeLeft = function(element) {
	element = Sabel.get(element, false);

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
				if (!Sabel.Array.include(["visible", "inherit"], of)) {
					position += border;
				}
			}
		}

		element = parent;
		if (element) {
			if (Sabel.Array.include(["BODY", "HTML"], element.tagName)) {
				if (Sabel.UserAgent.isIE) {
					position += parseInt(Sabel.Element.getStyle(element, "marginLeft")) || 0;
				}
				break;
			}
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
	if (element.nodeType !== 1) return {};

	var style = element.style;

	if (Sabel.Element.getStyle(element, "display") !== "none") {
		return {width: element.offsetWidth, height: element.offsetHeight};
	}

	var oldV = style.visibility;
	var oldP = style.positions;
	var oldD = "none";

	style.visibility = "hidden";
	style.positions  = "absolute";
	style.display    = "block";

	var dimensions = {
		width:  element.offsetWidth,
		height: element.offsetHeight
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

Sabel.Element.update = function(element, contents) {
	element = Sabel.get(element, false);

	var newEl = document.createElement(element.nodeName);
	newEl.id  = element.id;
	newEl.className = element.className;
	newEl.innerHTML = contents;

	element.parentNode.replaceChild(newEl, element);

	return Sabel.get(newEl);
};

Sabel.Element.observe = function(element, eventName, handler, useCapture, scope) {
	element = Sabel.get(element, false);
	if (element._events === undefined) element._events = {};
	if (element._events[eventName] === undefined) element._events[eventName] = new Array();

	var evt = new Sabel.Event(element, eventName, handler, useCapture, scope);
	element._events[eventName].push(evt);

	return evt;
	//return element;
};

Sabel.Element.stopObserve = function(element, eventName, handler) {
	element = Sabel.get(element, false);
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

Sabel.Element.getChildElements = function(element, tagName) {
	var buf = new Array(), element = Sabel.get(element, false);
	Sabel.Array.each(element.childNodes, function(elm) {
		if (elm.nodeType === 1) {
			if (tagName === undefined || tagName === elm.tagName) buf[buf.length] = elm;
		}
	});
	return buf;
};

Sabel.Element.getFirstChild = function(element) {
	return Sabel.Element.getChildElements(element)[0];
};

Sabel.Element.getLastChild = function(element) {
	var elms = Sabel.Element.getChildElements(element);
	return elms[elms.length - 1];
};

Sabel.Element.getNextSibling = function(element) {
	while (element = element.nextSibling) {
		if (element.nodeType === 1) return element;
	}
	return null;
};

Sabel.Element.getPreviousSibling = function(element) {
	while (element = element.previousSibling) {
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

Sabel.Element.getNodeIndex = function(element, reverse, ofType) {
	if (ofType === true) {
		return Sabel.Element._getOfTypeNodeIndex(element, reverse);
	} else{
		return Sabel.Element._getNodeIndex(element,reverse);
	};
};

Sabel.Element._getNodeIndex = function(element, reverse) {
	var parentNode = element.parentNode;

	var childNodes = parentNode.childNodes;
	var propName = (reverse === true) ? "__cachedLastIdx"
	                                  : "__cachedIdx";
	if (parentNode.__cachedLength === childNodes.length) {
		if (element[propName]) {
			return element[propName];
		}
	}

	if (reverse === true) {
		childNodes = new Sabel.Array(childNodes).reverse();
	}

	parentNode.__cachedLength = childNodes.length;
	for (var i = 0, idx = 1, child; child = childNodes[i]; i++) {
		if (child.nodeType == 1) child[propName] = idx++;
	}

	return element[propName];
};

Sabel.Element._getOfTypeNodeIndex = function(element, reverse) {
	var parentNode = element.parentNode;
	var childNodes = parentNode.childNodes;
	var propName   = (reverse === true) ? "__cachedOfTypeIdx"
	                                    : "__cachedLastOfTypeIdx";
	
	if (parentNode.__cachedLength === childNodes.length) {
		if (element[propName]) {
			return element[propName];
		}
	}

	if (reverse === true) {
		childNodes = new Sabel.Array(childNodes).reverse();
	}
	
	parentNode.__cachedLength = childNodes.length;
	for (var i = 0, idx = 1, child; child = childNodes[i]; i++) {
		if (child.tagName === element.tagName && child.nodeType === 1) {
			child[propName] = idx++;
		}
	}
	
	return element[propName];
};

Sabel.Element.contains = function(element, other) {
	if (element === document) element = document.body;
	if (element.contains) {
		// IE, Opera, Safari
		return element.contains(other);
	} else {
		// Firefox
		return !!(element.compareDocumentPosition(other) & element.DOCUMENT_POSITION_CONTAINED_BY);
	}
};

Sabel.Object.extend(Sabel.Element, Sabel.Object.Methods);

Sabel.Elements = function(elements) {
	if (typeof elements === "undefined") {
		elements = new Sabel.Array();
	} else if (elements._extended === true) {
		return elements;
	} else if (elements.constructor !== Array) {
		return null;
	} else {
		elements = new Sabel.Array(elements);
	}

	return Sabel.Object.extend(elements, Sabel.Elements, true);
};

Sabel.Elements._extended = true;

Sabel.Elements.add = function(elements, element) {
	elements[elements.length] = element;
};

Sabel.Elements.item = function(elements, pos) {
	var elm = elements[pos];

	return (elm) ? new Sabel.Element(elm) : null;
};

Sabel.Elements.observe = function(elements, eventName, handler, useCapture, scope) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.observe(elm, eventName, handler, useCapture, scope);
	});
};

Sabel.Elements.stopObserve = function(elements, eventName, handler) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.stopObserve(elm, eventName, handler);
	});
};

Sabel.Elements.unique = function(elements) {
	var finds = Sabel.Array.inject(elements, function(elm) {
		if (elm._searched === true) return false;
		elm._searched = true;
		return true;
	});

	Sabel.Array.each(finds, function(elm) { elm._searched = false; });
	return finds;
};

Sabel.Object.extend(Sabel.Elements, Sabel.Object.Methods);

Sabel.Iterator = function(iterable) {
	this.items = Sabel.Array(iterable);
	this.index = -1;
};

Sabel.Iterator.prototype = {
	hasPrev: function() {
		return this.index > 0;
	},

	hasNext: function() {
		return this.index < this.items.length-1;
	},

	prev: function() {
		return this.index > -1 ? this.items[--this.index] || null : null;
	},

	next: function() {
		return this.hasNext() ? this.items[++this.index] || null : null;
	}
};


if (typeof window.XMLHttpRequest === "undefined") {
	window.XMLHttpRequest = function() {
		var http;
		var objects = ["Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.3.0", "Msxml2.XMLHTTP", "Microsoft.XMLHTTP"];
		for (var i = 0, obj; obj = objects[i]; i++) {
			try {
				http = new ActiveXObject(obj);
				window.XMLHttpRequest = function() {
					return new ActiveXObject(obj);
				}
				break;
			} catch (e) {}
		}
		return http;
	}
};

Sabel.Ajax = function() {
	this.init.apply(this, arguments);
};

Sabel.Ajax.prototype = {
	init: function() {
		this.xmlhttp   = new XMLHttpRequest();
		this.completed = false;
	},
	
	request: function(url, options) {
		var xmlhttp = this.xmlhttp;
		var options = this.setOptions(options);

		this.completed = false;
		this._abort();

		if (options.method === "get") {
			url += ((url.indexOf("?") !== -1) ? "&" : "?") + options.params;
		}

		xmlhttp.open(options.method, url, options.async);
		xmlhttp.onreadystatechange = Sabel.Function.bind(this.onStateChange, this);

		this.setRequestHeaders();
		xmlhttp.send((options.method === "post") ? options.params : "");
		if (options.timeout) this.timer = setTimeout(Sabel.Function.bind(this.abort, this), options.timeout);
	},

	abort: function() {
		if (this._abort()) this.options.onTimeout.apply(this.options.scope);
	},

	_abort: function() {
		var xmlhttp = this.xmlhttp;
		if (xmlhttp.readyState !== 4) {
			xmlhttp.onreadystatechange = Sabel.emptyFunc;
			xmlhttp.abort();

			return true;
		}
		return false;
	},

	updater: function(element, url, options) {
		options = options || {};

		var onComplete = options.onComplete || function() {};
		options.onComplete = function(response) {
			Sabel.get(element).innerHTML = response.responseText;
			onComplete(response);
		}

		this.request(url, options);
	},

	setOptions: function(options) {
		if (options === undefined) options = {};

		var defaultOptions = {
			method: "post",
			params: "",
			contentType: "application/x-www-form-urlencoded",
			charset: "UTF-8",
			onComplete: function(){},
			onSuccess: function(){},
			onFailure: function(){},
			onTimeout: function(){},
			scope: null,
			async: true
		};
		Sabel.Object.extend(options, defaultOptions);
		options.method = options.method.toLowerCase();
		return (this.options = options);
	},

	setRequestHeaders: function() {
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
	},

	onStateChange: function() {
		if (this.completed === true) return;

		if (this.xmlhttp.readyState === 4) {
			this.completed = true;
			clearTimeout(this.timer);

			var options  = this.options;
			var response = this.getResponses();
			options["on" + (this.isSuccess() ? "Success" : "Failure")].call(options.scope, response);
			options.onComplete.call(options.scope, response);

			this.xmlhttp.onreadystatechange = Sabel.emptyFunc;
		}
	},

	getResponses: function() {
		var xmlhttp  = this.xmlhttp;
		var response = new Object();
		response.responseXML  = xmlhttp.responseXML;
		response.responseText = this.responseFilter(xmlhttp.responseText);
		response.status = xmlhttp.status;
		response.statusText = xmlhttp.statusText;
		return response;
	},

	isSuccess: function() {
		var status = this.xmlhttp.status;
		return (status && (status >= 200 && status < 300));
	},

	responseFilter: function(text) {
		if (Sabel.UserAgent.isKHTML) {
			var esc = escape(text);
			if (esc.indexOf("%u") < 0 && esc.indexOf("%") > -1) {
				text = decodeURIComponent(esc);
			}
		}
		return text;
	}
};
Sabel.History = function() {
	this.init.apply(this, arguments);
};

Sabel.History.prototype = {
	currentHash: "",
	callback: null,
	timer: null,

	init: function(callback) {
		this.callback = callback || function() {}
		var hash = this._getHash(document);

		if (hash !== "") this.callback(hash);

		this.timer = setInterval(Sabel.Function.bind(this._check, this), 300);
	},

	load: function(hash) {
		this._setHash(hash.replace(/^#/, ""), true);
	},

	_check: function() {
		var hash = this._getHash(document);

		if (hash !== this.currentHash) {
			this._setHash(hash);
		}
	},

	_setHash: function(hash, isUpdate) {
		if (isUpdate === true) location.hash = "#" + hash;
		this.currentHash = hash;
		if (hash !== "") this.callback(hash);
	},

	_getHash: function(target) {
		return new Sabel.Uri(target.location.href).hash.replace(/^[^#]*#/, "");
	}
};

if (Sabel.UserAgent.isIE) {
	Sabel.History.prototype.init = function(callback) {
		this.callback = callback || function() {}
		var hash = this._getHash(document);

		this.iframe = document.createElement('<iframe id="sbl_history_frame" style="display: none;">');
		document.body.appendChild(this.iframe);
		var doc = this.iframe.contentWindow.document;
		doc.open();
		doc.close();
		this._setHash(doc, hash, false);

		this.timer = setInterval(Sabel.Function.bind(this._check, this), 300);
	};

	Sabel.History.prototype.load = function(hash) {
		var doc = this._getIframe();
		hash = hash.replace(/^#/, "");

		this._setHash(document, hash);
		doc.open();
		doc.close();
		this._setHash(doc, hash);

		this.callback(hash);
	};

	Sabel.History.prototype._check = function() {
		var hash = this._getHash(this._getIframe());

		if (hash !== this.currentHash) {
			this._setHash(document, hash);
			if (hash !== "") this.callback(hash);
		}
	};

	Sabel.History.prototype._setHash = function(target, hash, isUpdate) {
		target.location.hash = "#" + hash;
		if (isUpdate !== false) this.currentHash = hash;
	};

	Sabel.History.prototype._getIframe = function() {
		return this.iframe.contentWindow.document;
	};
}

Sabel.Form = function(form) {
	this.form = Sabel.get(form, false);

	var elms = this.form.getElementsByTagName("*");
	var buf = {};
	Sabel.Array.each(elms, function(el) {
		var method = Sabel.Form.Elements[el.tagName.toLowerCase()], value;
		if (method && (value = method(el)) !== null) {
			if (buf[el.name]) {
				if (!Sabel.Object.isArray(buf[el.name])) {
					buf[el.name] = [buf[el.name]];
				}
				buf[el.name].push(value);
			} else {
				buf[el.name] = value;
			}
		}
	});

	this.queryObj = new Sabel.QueryObject(buf);
};

Sabel.Form.prototype = {
	getQueryObj: function() {
		return this.queryObj;
	},

	has: function(key) {
		return this.queryObj.has(key);
	},

	get: function(key) {
		return this.queryObj.get(key);
	},

	set: function(key, val) {
		return this.queryObj.set(key, val);
	},

	serialize: function() {
		return this.queryObj.serialize();
	}
};

Sabel.Object.extend(Sabel.Form, Sabel.Object.Methods);

Sabel.Validator = function(formElm, errField) {
	this.errField = Sabel.get(errField||"sbl_errmsg", false);
	this.validators = new Object();

	Sabel.Element.observe(formElm, "submit", Sabel.Function.bindWithEvent(this.validate, this));
};

Sabel.Validator.prototype = {
	add: function(elm, func, errMsg) {
		elm = Sabel.get(elm, false);
		var name = elm.name;
		var validators = this.validators;

		if (validators[name] === undefined) {
			validators[name] = new Sabel.Validator.Element(elm);
		}

		validators[name].add(func, errMsg);
	},

	validate: function(e) {
		var validators = this.validators;
		var errors = [], v;
		for (var name in validators) {
			v = validators[name];
			if (v.validate() === false) errors.push(v.errMsg);
		}

		var status = !(errors.length);
		if (status === false) {
			this.insertMessage(errors);
			Sabel.Event.preventDefault(e);
		} else {
			this.clearMessageField();
		}
	},

	insertMessage: function(errors) {
		this.clearMessageField();

		this.errField.appendChild(this.getErrorMessage(errors));
		Sabel.Element.setStyle(this.errField, {display: "inline"});

		var yPos = Sabel.Element.getCumulativeTop(this.errField) - 20;
		window.scroll(0, yPos);
	},

	clearMessageField: function() {
		Sabel.Element.setStyle(this.errField, {display: "none"});
		this.errField.innerHTML = "";
	},

	getErrorMessage: function(errors) {
		var ul = document.createElement("ul");
		Sabel.Array.each(errors, function(err) {
			var li = document.createElement("li");
			li.appendChild(document.createTextNode(err));
			ul.appendChild(li);
		});
		return ul;
	}
};

Sabel.Event = function(element, eventName, handler, useCapture, scope) {
	element = Sabel.get(element, false);

	this.element    = element;
	this.eventName  = eventName;
	this.handler    = function(evt) {
		handler.call(scope || this, evt || window.event);
	};
	this.useCapture = useCapture;
	this.isActive   = false;
	this.eventId    = Sabel.Events.add(this);

	this.start();
};

Sabel.Event.prototype = {
	start: function() {
		if (this.isActive === false) {
			var element = this.element;

			if (element.addEventListener) {
				element.addEventListener(this.eventName, this.handler, this.useCapture);
			} else if (element.attachEvent) {
				element.attachEvent("on" + this.eventName, this.handler);
			}
			this.isActive = true;
		}
	},

	stop: function() {
		if (this.isActive === true) {
			var element = this.element;

			if (element.removeEventListener) {
				element.removeEventListener(this.eventName, this.handler, this.useCapture);
			} else if (element.detachEvent) {
				element.detachEvent("on" + this.eventName, this.handler);
			}
			this.isActive = false;
		}
	},

	getHandler: function() {
		return this.handler;
	}
};

Sabel.Event.stopPropagation = function(evt) {
	evt.stopPropagation();
};

Sabel.Event.preventDefault = function(evt) {
	evt.preventDefault();
};

if (Sabel.UserAgent.isIE) {
	Sabel.Event.stopPropagation = function(evt) {
		(evt || window.event).cancelBubble = true;
	};
	
	Sabel.Event.preventDefault = function(evt) {
		(evt || window.event).returnValue = false;
	};
}
Sabel.Events = {

	_events: new Array(),

	add: function(evtObj) {
		var len = Sabel.Events._events.length;
		Sabel.Events._events[len] = evtObj;

		return len;
	},

	stop: function(eventId) {
		Sabel.Events._events[eventId].stop();
	},

	stopAll: function() {
		var events = Sabel.Events._events;

		Sabel.Array.callmap(events, "stop");
	}
};


Sabel.KeyEvent = new Sabel.Class({
	_lists: {},

	init: function(element) {
		element = this.element = Sabel.get(element) || document;

		new Sabel.Event(element, "keydown", this.keyDownHandler, false, this);
	},

	add: function(key, func, scope) {
		key = this.createCharCode(key);		
		this._lists[key] = Sabel.Function.bind(func, scope || this.element);

		return this;
	},

	remove: function(key) {
		key = this.createCharCode(key);
		delete this._lists[key];

		return this;
	},

	keyDownHandler: function(e) {
		var key  = this.getCharCode(e);
		var func = this._lists[key];
		if (func) {
			func(e);
			Sabel.Event.preventDefault(e);
		}
	},

	createCharCode: function(key) {
		if (typeof key === 'string') {
			switch (key.length) {
			case 1:
				key = key.charCodeAt(0);
				break;
			case 3:
				key = key.charCodeAt(2) + Sabel.KeyEvent._CTRL_CODE;
				break;
			default:
				// @todo throw exception?
			}
		}
		return key;
	},

	getCharCode: function(e) {
		if (e.type === "keydown") {
			var kc = e.keyCode;
			if (Sabel.Number.between(kc, 65, 90)) {
				if (e.shiftKey === false)
					kc += 32;
			}
			if (kc !== 17 && e.ctrlKey === true) kc += Sabel.KeyEvent._CTRL_CODE;
			return kc;
		}

		return null;
	}
});

Sabel.KeyEvent._CTRL_CODE    = 1000;
Sabel.KeyEvent.KEY_BACKSPACE = 8;
Sabel.KeyEvent.KEY_TAB       = 9;
Sabel.KeyEvent.KEY_ENTER     = 13;
Sabel.KeyEvent.KEY_ESC       = 27;
Sabel.KeyEvent.KEY_SPACE     = 32;
Sabel.KeyEvent.KEY_PAGEUP    = 33;
Sabel.KeyEvent.KEY_PAGEDOWN  = 34;
Sabel.KeyEvent.KEY_END       = 35;
Sabel.KeyEvent.KEY_HOME      = 36;
Sabel.KeyEvent.KEY_LEFT      = 37;
Sabel.KeyEvent.KEY_UP        = 38;
Sabel.KeyEvent.KEY_RIGHT     = 39;
Sabel.KeyEvent.KEY_DOWN      = 40;
Sabel.KeyEvent.KEY_INSERT    = 45;
Sabel.KeyEvent.KEY_DELETE    = 46;
Sabel.KeyEvent.KEY_F1        = 112;
Sabel.KeyEvent.KEY_F2        = 113;
Sabel.KeyEvent.KEY_F3        = 114;
Sabel.KeyEvent.KEY_F4        = 115;
Sabel.KeyEvent.KEY_F5        = 116;
Sabel.KeyEvent.KEY_F6        = 117;
Sabel.KeyEvent.KEY_F7        = 118;
Sabel.KeyEvent.KEY_F8        = 119;
Sabel.KeyEvent.KEY_F9        = 120;
Sabel.KeyEvent.KEY_F10       = 121;
Sabel.KeyEvent.KEY_F11       = 122;
Sabel.KeyEvent.KEY_F12       = 123;

Sabel.Effect = function() {
	this.init.apply(this, arguments);
};

Sabel.Effect.prototype = {
	init: function(options) {
		options = options || {};

		this.callback = options.callback || function() {};
		this.interval = options.interval || 20;
		this.duration = options.duration || 1000;
		this.step = this.interval / this.duration;
		this.state  = null;
		this.target = 0;
		this.timer  = null;
		this.effects = Sabel.Array();
	},

	add: function(effect , reverse) {
		reverse = reverse || false;
		this.effects.push({func: effect, reverse: reverse});
		return this;
	},

	play: function(force) {
		if (this.state === 1 && force !== true) {
			return this;
		} else if (this.state === 0 || this.state === null) {
			this.set(0, 1);
			this._run();
		} else if (force === true) {
			var state = (this.state === 1) ? 0 : this.state;
			this.set(state, 1)
			this._run();
		} else if (this.timer === null) {
			this.set(this.state, 1);
			this._run();
		}
		return this;
	},

	reverse: function(force) {
		if (this.state === 0 && force !== true) {
			return this;
		} else if (this.state === 1 || this.state === null) {
			this.set(1, 0);
			this._run();
		} else if (force === true) {
			var state = (this.state === 0) ? 1 : this.state;
			this.set(state, 0)
			this._run();
		} else if (this.timer === null) {
			this.set(this.state, 0);
			this._run();
		}
		return this;
	},

	toggle: function() {
		this.set(this.state, 1 - this.target);
		this._run();
		return this;
	},

	pause: function() {
		this._clear();
		return this;
	},

	resume: function() {
		this._run();
		return this;
	},

	show: function() {
		this.set(1, 1);
		this.execEffects();
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.end((ef.reverse === true) ? 1 - state : state);
		});

		return this;
	},

	hide: function() {
		this.set(0, 0);
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.end(0);
		});

		return this;
	},

	set: function(from, to) {
		this.state  = from;
		this.target = to;
		this._clear();
	},

	execEffects: function() {
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.exec((ef.reverse === true) ? 1 - state : state);
		});
	},

	_run: function() {
		var state = this.state;
		if (state == 1 || state == 0) {
			this.effects.each(function(ef) {
				ef.func.start((ef.reverse === true) ? 1 - state : state);
			});
		}
		this.timer = setInterval(Sabel.Function.bind(this._exec, this), this.interval);
	},

	_exec: function() {
		var mv = (this.state > this.target ? -1 : 1) * this.step;
		this.state += mv;
		if (this.state >= 1 || this.state <= 0) {
			this.set(this.target, this.target);
		}

		this.execEffects();

		if (this.state == 1 || this.state == 0) {
			var state = this.state;
			this.effects.each(function(ef) {
				ef.func.end((ef.reverse === true) ? 1 - state : state);
			});
			this.callback(!this.state);
		}
	},

	_clear: function() {
		clearInterval(this.timer);
		this.timer = null;
	}
};

Sabel.Util = {};

Sabel.dump = function(element, limit)
{
	var br = (Sabel.UserAgent.isIE) ? "\r" : "\n";
	limit  = limit || 1;

	output = document.createElement("pre");
	output.style.border = "1px solid #ccc";
	output.style.color  = "#333";
	output.style.background = "#fff";
	output.style.margin = "5px";
	output.style.padding = "5px";

	output.appendChild(document.createTextNode((function(element, ind) {
		var space = new Sabel.String("  ");
		var indent = space.times(ind);
		if (typeof element === "undefined") {
			return "undefined";
		} else if (Sabel.Element.isString(element)) {
			return "string(" + element.length + ') "' + element + '"';
		} else if (Sabel.Element.isNumber(element)) {
			return "int(" + element + ")";
		} else if (Sabel.Element.isBoolean(element)) {
			return "bool(" + element + ")";
		} else if (Sabel.Element.isFunction(element)) {
			return element.toString().replace(/(\n)/g, br + space.times(ind - 1));
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
			buf[buf.length] = space.times(ind - 1)+ "}";
			return buf.join(br);
		}
	})(element, 1)));

	document.body.appendChild(output);
};

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
Sabel.Validator.Int = function(option) {
	option = option || {};
	return function(value) {
		if (option.min && value && value < option.min) return false;
		if (option.max && value && value > option.max) return false;

		// 8進数が動かない。
		if (value == "" || parseInt(value) == value) return true;

		return false;
	};
};

Sabel.Validator.Float = function(option) {
	option = option || {};
	return function(value) {
		if (option.min && value && value < option.min) return false;
		if (option.max && value && value > option.max) return false;

		if (value == "" || parseFloat(value) == value) return true;
		return false;
	};
};

Sabel.Validator.String = function(option) {
	return function(value) {
		if (option.min && value.length < option.min) return false;
		if (option.max && value.length > option.max) return false;

		return true;
	};
};

Sabel.Validator.Must = function() {
	return function(value) {
		if (value === "") return false;
		return true;
	};
};

Sabel.Validator.Regex = function(pattern) {
	return function(value) {
		if (value.search(pattern) !== -1) return false;
		return true;
	};
};
Sabel.Validator.Element = function(element) {
	this.validations = new Array();
	this.element = Sabel.get(element, false);
	this.errMsg = "";
};
Sabel.Validator.Element.prototype = {
	add: function(func, errMsg) {
		this.validations.push({
			func: func,
			errMsg: errMsg
		});

		return this;
	},

	validate: function() {
		var validations = this.validations;
		for (var i = 0, v; v = validations[i]; i++) {
			if (v.func(this.element.value) === false) {
				this.errMsg = v.errMsg;
				return false;
			}
		}
		return true;
	}
};

Sabel.Effect.Fade = function() {
	this.init.apply(this, arguments);
};
Sabel.Effect.Fade.prototype = {
	init: function(element) {
/*
		if (Sabel.Environment.isDevelopment &&
			Sabel.Element.getStyle(element, "display") === "none") {
			alert("CSSでdisplayがnoneにセットされています。\nこのままだと正常に動作しないので、削除して下さい。");
		}
*/

		this.element = Sabel.get(element, false);
	},

	start: function(state) {
		this.exec(state);
		Sabel.Element.show(this.element);
	},

	end: function(state) {
		if (state === 0) {
			this.exec(1);
			Sabel.Element.hide(this.element);
		}
	},

	exec: function(state) {
		Sabel.Element.setOpacity(this.element, state);
	}
};


Sabel.Effect.Slide = function() {
	this.init.apply(this, arguments);
};

Sabel.Effect.Slide.prototype = {
	init: function(element) {
/*
		if (Sabel.Environment.isDevelopment &&
			Sabel.Element.getStyle(element, "display") === "none") {
			alert("CSSでdisplayがnoneにセットされています。\nこのままだと正常に動作しないので、削除して下さい。");
		}
*/

		this.element = Sabel.get(element, false);
	},

	start: function(state) {
		var elm = this.element;

		this.elementHeight   = Sabel.Element.getHeight(elm);

		this.styleHeight     = elm.style.height || "";
		this.defaultPosition = Sabel.Element.getStyle(elm, "position");
		this.defaultOverflow = Sabel.Element.getStyle(elm, "overflow");

		var height = state * this.elementHeight;
		var style = {
			overflow: "hidden",
			display: "",
			height: height
		};
		if (this.defaultPosition !== "absolute") style.position = "relative";
		Sabel.Element.setStyle(elm, style);

		this.exec(state);
	},
	end: function(state) {
		var style = {
			position: this.defaultPosition,
			overflow: this.defaultOverflow,
			height: this.styleHeight
		};

		if (state === 0) style.display = "none";
		Sabel.Element.setStyle(this.element, style);
	},

	exec: function(state) {
		var element = this.element;
		var height = state * this.elementHeight;
		Sabel.Element.setStyle(element, {height: height});
	}
};
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
		if (range.length < 2) return null;

		var startX = this.initPos.left;
		this.options.rangeX = {min: startX - range[0], max: startX + range[1]};
		if (range[2] !== undefined) this.options.gridX = range[2];
		return this;
	},

	setYConst: function(range)
	{
		if (range.length < 2) return null;

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

Sabel.Widget = {};

Sabel.Widget.Overlay = function(option) {
	option = option || {};
	var div = document.createElement("div");
	if (option.id) div.setAttribute("id", option.id);

	div.style.cssText += "; background-color: #000; position: absolute; top: 0px; left: 0px; opacity: 0.70; -moz-opacity: 0.70; filter: alpha(opacity=70); z-index: 100;";

	this.div = Sabel.Element(div);;
	document.body.appendChild(div);
	this.show();
};

Sabel.Widget.Overlay.prototype = {
	div: null,

	show: function() {
		this.setStyle();
		this.div.show();
	},

	hide: function() {
		this.div.hide();
	},

	setStyle: function() {
		var height = Sabel.Window.getScrollHeight();
		var width  = Sabel.Window.getScrollWidth();

		this.div.style.width  = width  + "px";
		this.div.style.height = height + "px";
	}
};


