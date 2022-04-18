const wikimediaTestingUtils = require( '@wikimedia/mw-node-qunit' );
const fn = () => {};

global.CSS = {
	escape: ( str ) => str
};

global.OO = {
	inheritClass: ( ClassNameObject ) => {
		ClassNameObject.super = fn;
		ClassNameObject.prototype.on = fn;
	},
	initClass: fn,
	EventEmitter: fn
};

wikimediaTestingUtils.setUp( /* disable QUnit sandboxing feature */ false );
