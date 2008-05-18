/* Sabel JS - %VERSION%
 *
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @author     Ebine yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Hamanaka Kazuhiro <Hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
/*---------------------------------------------------------------------------*/

Sabel.PHP.AjaxUploader = function() {
	this.init.apply(this, arguments)
};

Sabel.PHP.AjaxUploader.prototype = {
	IFRAME_NAME: "sbl_uploader_target_iframe",
	message: "preparing transfer...",
	updateInterval: 200,  // msec

	incrementValue: 0,
	totalSize:      0,
	currentSize:    0,
	uploadedSize:   0,
	currentPercent: 0.0,

	interval: 700,
	intervalIncr: 100,
	lastTime: null,

	reqCount: 0,
	ajaxReqTime: 0,
	totalReqTime: 0,

	_bindedGetProgress: null,
	_bindedShowProgress: null,

	init: function(form, uri, progress) {
		var self = this;

		this.timer    = null;
		this.tTimer   = null;
		this.interval = 700;

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
		this.tTimer = setTimeout(this._bindedGetProgress, this.updateInterval);
	},

	endProgress: function() {
		clearInterval(this.timer);
		clearInterval(this.tTimer);

		this.currentPercent = 100.0;
		this._exec();
	},

	getProgress: function() {
		this.ajaxReqTime = new Date().getTime();
		this.ajax.request(this.uri,
		                  { method: 'get',
		                    //timeout: 1000,
		                    headers: { "If-Modified-Since": new Date(0) },
		                    onSuccess: this._bindedShowProgress,
		                    onTimeout: this._bindedShowProgress
		                  });
	},

	showProgress: function(res) {
		if (res) {
			this.reqCount++;
			this.totalReqTime += (new Date().getTime() - this.ajaxReqTime);

			eval("var json = " + res.responseText);
			if (json.done == 1) return this.endProgress();

			if (this.currentPercent == 0.0) {  // first response
				this.progressTexts[0].innerHTML = "";
				this.totalSize = json.total;
				this.timer = setInterval(Sabel.Function.bind(this.progress, this), this.updateInterval);
			}

			var uploaded = json.current - this.uploadedSize;
			this.uploadedSize = json.current;
			var percent = this.uploadedSize / this.totalSize * 100;

			var time = new Date().getTime();
			var reqTime = time - this.lastTime;
			this.lastTime = time;

			var totalTime = reqTime / (uploaded / this.totalSize);
			var refreshNum = ((this.totalReqTime / this.reqCount) + this.interval) / this.updateInterval;
			this.incrementValue = (this.updateInterval * 100 / totalTime) + ((percent - this.currentPercent) / refreshNum);
			if (this.incrementValue < 0) this.incrementValue = 0.01;
			
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
