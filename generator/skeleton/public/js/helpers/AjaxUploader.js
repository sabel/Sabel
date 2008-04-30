Sabel.PHP.AjaxUploader = function() {
	this.init.apply(this, arguments)
};

Sabel.PHP.AjaxUploader.prototype = {
	IFRAME_NAME: "sbl_uploader_target_iframe",
	message: "preparing transfer...",

	incrementValue: 0,
	totalSize:      0,
	currentSize:    0,
	uploadedSize:   0,
	currentPercent: 0.0,

	interval: 750,
	intervalIncr: 150,
	lastTime: null,

	_bindedGetProgress: null,
	_bindedShowProgress: null,

	init: function(form, uri, progress) {
		var self = this;

		this.timer    = null;
		this.tTimer   = null;
		this.interval = 750;

		this.uri  = uri;
		this.ajax = new Sabel.Ajax();

		this._bindedGetProgress  = Sabel.Function.bind(this.getProgress, this);
		this._bindedShowProgress = Sabel.Function.bind(this.showProgress, this);

		Sabel.get(form).observe("submit", function(evt) {
			self._createIframe();

			var elm = evt.srcElement || this;
			elm.setAttribute("target", self.IFRAME_NAME);
			Sabel.get(progress).appendChild(self._createProgressField());
			self.startProgress();
		});
	},

	setMessage: function(msg) {
		this.message = msg;
	},

	startProgress: function() {
		this.progressTexts[0].innerHTML = this.message;
		this.lastTime = new Date().getTime();
		this.tTimer = setTimeout(this._bindedGetProgress, 100);
	},

	endProgress: function() {
		clearInterval(this.timer);
		clearInterval(this.tTimer);

		this.currentPercent = 100.0;
		this._exec();
	},

	getProgress: function() {
		this.ajax.request(this.uri,
		                  { method: 'get',
		                    timeout: 500,
		                    headers: { "If-Modified-Since": new Date(0) },
		                    onSuccess: this._bindedShowProgress,
		                    onTimeout: this._bindedShowProgress
		                  });
	},

	showProgress: function(res) {
		if (res) {
			eval("var json = " + res.responseText);

			if (json.done == 1) return this.endProgress();

			if (this.currentPercent == 0.0) {  // first response
				this.progressTexts[0].innerHTML = "";
				this.totalSize = json.total;
				this.timer = setInterval(Sabel.Function.bind(this.progress, this), 100);
			}

			var uploaded = json.current - this.uploadedSize;
			this.uploadedSize = json.current;

			var percent = this.uploadedSize / this.totalSize * 100;
			var time = new Date().getTime();
			var reqTime = time - this.lastTime;
			this.lastTime = time;

			var rate = (100 * reqTime) / (uploaded / this.totalSize * 100);

			this.incrementValue = 10000 / rate + ((percent - this.currentPercent) / 100);
			this.interval = Math.min(2000, this.interval + this.intervalIncr)
		}
		this.tTimer = setTimeout(this._bindedGetProgress, this.interval);
	},

	progress: function() {
		this.currentPercent = Math.min(99.9, this.currentPercent + this.incrementValue);

		this._exec();
	},

	_exec: function() {
		var percent = this.currentPercent;
		Sabel.Element.setStyle(this.progressElm, {"width": percent + "%"});

		var percentText = percent.toFixed(1) + '&nbsp;%';
		Sabel.Array.each(this.progressTexts, function(el) {
			el.innerHTML = percentText;
		});

		this.statusText.innerHTML = Sabel.Number.toHumanReadable(this.totalSize * percent / 100, 1024) + "byte / "
		                          + Sabel.Number.toHumanReadable(this.totalSize, 1024) + "byte ( "
		                          + Sabel.Number.toHumanReadable(this.totalSize * this.incrementValue / 10 * 8, 1024) + "bps )";
	},

	_createProgressField: function() {
		var div, progress, text, status;

		(div = document.createElement("div")).className = "sbl_progress_border";
		(progress = document.createElement("div")).className = "sbl_progress_bar";

		(text = document.createElement("span")).className  = "sbl_progress_text";
		(status = document.createElement("div")).className = "sbl_progress_status";

		div.appendChild(text.cloneNode(false))
		div.appendChild(progress).appendChild(text);
		div.appendChild(status)

		this.progressElm   = progress;
		this.progressTexts = Sabel.Dom.getElementsByClassName("sbl_progress_text", div);
		this.statusText    = status;

		return div;
	},

  _createIframe: function() {
		try {
			var iframe = document.createElement('<iframe name="' + this.IFRAME_NAME + '">')
		} catch (e) {
			var iframe = document.createElement("iframe");
			iframe.setAttribute("name", this.IFRAME_NAME);
		}
		Sabel.Element.setStyle(iframe, {display: "none"});

		document.body.appendChild(iframe);
		new Sabel.Event(iframe, "load", Sabel.Function.bind(this.endProgress, this));

		return iframe;
  }
};
