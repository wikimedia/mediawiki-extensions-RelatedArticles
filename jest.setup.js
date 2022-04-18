var wikimediaTestingUtils = require( '@wikimedia/mw-node-qunit' );
var fn = function () {};

global.CSS = {
	escape: function ( str ) {
		return str;
	}
};

global.OO = {
	inheritClass: function ( ClassNameObject ) {
		ClassNameObject.super = fn;
		ClassNameObject.prototype.on = fn;
	},
	initClass: fn,
	EventEmitter: fn
};

wikimediaTestingUtils.setUp( /* disable QUnit sandboxing feature */ false );
