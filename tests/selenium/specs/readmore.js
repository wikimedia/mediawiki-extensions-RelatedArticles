'use strict';

const assert = require( 'assert' ),
	Api = require( 'wdio-mediawiki/Api' ),
	ReadMorePage = require( '../pageobjects/readmore.page' );

describe( 'ReadMore', function () {
	let bot;

	before( async () => {
		bot = await Api.bot();
	} );

	const name = 'Related Articles 1';

	// eslint-disable-next-line mocha/no-sibling-hooks
	before( async function () {
		// Create page needed for the tests
		const content = '{{#related:related_articles_2}}';
		await bot.edit( name, content );
	} );

	it( 'ReadMore is present in Minerva @daily', async function () {
		await ReadMorePage.openMobile( name );
		assert( await ReadMorePage.seeReadMore() );
	} );
} );
