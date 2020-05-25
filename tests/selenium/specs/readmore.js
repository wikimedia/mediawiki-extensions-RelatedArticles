'use strict';

var assert = require( 'assert' ),
	Api = require( 'wdio-mediawiki/Api' ),
	ReadMorePage = require( '../pageobjects/readmore.page' );

describe( 'ReadMore', function () {
	let bot;

	before( async () => {
		bot = await Api.bot();
	} );

	const name = 'Related Articles 1';

	before( function () {
		// Create page needed for the tests
		browser.call( async () => {
			const content = '{{#related:related_articles_2}}';
			await bot.edit( name, content );
		} );
	} );

	it( 'ReadMore is not present on Vector', function () {
		ReadMorePage.openDesktop( name );
		assert( !ReadMorePage.isCardVisible(), 'No related pages cards are shown' );
	} );

	it( 'ReadMore is present in Minerva', function () {
		ReadMorePage.openMobile( name );
		assert( ReadMorePage.seeReadMore() );
	} );
} );
