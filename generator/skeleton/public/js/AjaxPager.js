Sabel.PHP.AjaxPager = function(replaceId, pagerClass) {
	this.replaceId     = Sabel.get(replaceId, false);
	this.pagerSelector = "." + (pagerClass || "sbl_pager") + " a";

	this.ef = new Sabel.Effect({
		duration: 300
	}).add(new Sabel.Effect.Slide(this.replaceId, true));

	this.init();
	this.history = new Sabel.History(Sabel.Function.bind(this.callback, this));
};

Sabel.PHP.AjaxPager.prototype = {
	init: function() {
		var self = this;
		Sabel.find(this.pagerSelector).observe("click", function(evt) {
			try {
				if (this.pathname.lastIndexOf(this.search) > -1) {
					var path = "/" + this.pathname.replace(/^\//, "");
				} else {
					var path = "/" + this.pathname.replace(/^\//, "") + this.search;
				}
				self.history.load(path);
			}catch(e) {}
			Sabel.Event.preventDefault(evt);
		});
	},

	reinit: function() {
		this.init();
		var self = this;

		setTimeout(function() { self.ef.hide(); self.ef.play(true) }, 1000);
	},

	callback: function(uri) {
		this.uri = uri
		this.ef.reverse(true, Sabel.Function.bind(this.exec, this));
	},

	exec: function() {
		new Sabel.Ajax().updater(this.replaceId, this.uri,
								 {onComplete: Sabel.Function.bind(this.reinit, this)});
	}
};

