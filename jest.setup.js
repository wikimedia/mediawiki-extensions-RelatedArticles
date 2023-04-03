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

const { TextEncoder, TextDecoder } = require( 'util' );
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

wikimediaTestingUtils.setUp( /* disable QUnit sandboxing feature */ false );
