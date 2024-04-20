'use strict';
const CARD_SELECTOR = '.ext-related-articles-card',
	Page = require( 'wdio-mediawiki/Page' ),
	READ_MORE_MODULE_NAME = 'ext.relatedArticles.readMore.bootstrap';

class ReadMorePage extends Page {

	get mobileView() {
		return $( '#footer-places-mobileview' );
	}

	openDesktop( name ) {
		super.openTitle( name );
		this.resourceLoaderModuleStatus( READ_MORE_MODULE_NAME, 'loading' );
	}

	openMobile( name ) {
		super.openTitle( name );
		this.mobileView.click();
		this.resourceLoaderModuleStatus( READ_MORE_MODULE_NAME, 'ready' );
	}

	get extCardsCard() {
		return $( '.ext-related-articles-card' );
	}

	get readMore() {
		this.readMoreCodeIsLoaded();
		this.extCardsCard.waitForExist( { timeout: 2000 } );
		return this.extCardsCard;
	}

	isCardVisible() {
		return $( CARD_SELECTOR ).isDisplayed();
	}

	readMoreCodeIsLoaded() {
		browser.waitUntil( async () => {
			return await browser.execute( async ( status ) => {
				return await mw && mw.loader && mw.loader.getState( 'ext.relatedArticles.readMore' ) === status;
			}, 'ready' );
		}, 2000, 'Related pages did not load' );
	}

	resourceLoaderModuleStatus( moduleName, moduleStatus ) {
		return browser.waitUntil( async () => {
			return await browser.execute( async ( module ) => {
				return await mw && mw.loader && mw.loader.getState( module.name ) === module.status;
			}, { status: moduleStatus, name: moduleName } );
		}, 10000, 'Related pages did not load' );
	}

	seeReadMore() {
		$( CARD_SELECTOR ).waitForExist( { timeout: 10000 } );
		return true;
	}

}
module.exports = new ReadMorePage();
