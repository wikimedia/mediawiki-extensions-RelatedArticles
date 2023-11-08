'use strict';

const wikimediaTestingUtils = require( '@wikimedia/mw-node-qunit' );

const { TextEncoder, TextDecoder } = require( 'util' );
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

wikimediaTestingUtils.setUp( /* disable QUnit sandboxing feature */ false );
