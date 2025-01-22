// eslint-disable-next-line spaced-comment
/// <reference path="./codex.ts" />
const RelatedArticles = require( './RelatedArticles.js' );
const data = require( './data.json' );
const RelatedPagesGateway = require( './RelatedPagesGateway.js' );
const relatedPages = new RelatedPagesGateway(
	new mw.Api( {
		ajax: {
			url: data.searchUrl
		}
	} ),
	mw.config.get( 'wgPageName' ),
	Object.keys( mw.config.get( 'wgRelatedArticles', {} ) ),
	data.useCirrusSearch,
	data.onlyUseCirrusSearch,
	data.descriptionSource
);
const LIMIT = mw.config.get( 'wgRelatedArticlesCardLimit', 3 );

/**
 * Generates suggestion objects from pages
 *
 * @param {MwApiPageObject[]} pages
 * @return {Codex.ListTitleObject[]}
 */
function getCards( pages ) {
	return pages.map( ( page ) => {
		const result = {
			id: page.title,
			label: page.title,
			url: mw.util.getUrl( page.title, {
				wprov: 'rarw1'
			} ),
			thumbnail: page.thumbnail ? {
				width: page.thumbnail.width,
				height: page.thumbnail.height,
				url: page.thumbnail.source
			} : undefined,
			description: ( page.description || page.extract ||
				( page.pageprops ? page.pageprops.description : '' ) )
		};

		return result;
	} );
}

/**
 * @param {MwApiPageObject[]} pages
 * @param {Element} el
 * @param {string} heading
 * @param {boolean} isContainerSmall
 * @param {string} [clickEventName]
 */
function render( pages, el, heading, isContainerSmall, clickEventName ) {
	el.innerHTML = RelatedArticles( {
		isContainerSmall,
		heading,
		cards: getCards( pages ),
		clickEventName
	} );
	el.addEventListener( 'click', ( ev ) => {
		const target = /** @type {HTMLElement} */( ev.target );
		const link = target.closest( 'a[data-event-name]' );
		if ( link ) {
			mw.hook( 'ext.relatedArticles.click' ).fire( clickEventName );
		}
	} );
}

/**
 * @param {HTMLElement} container to initialize into
 * @param {string} [clickEventName] that fires when cards are clicked
 */
function init( container, clickEventName ) {
	relatedPages.getForCurrentPage( LIMIT ).then( ( /** @type {MwApiPageObject[]} */ pages ) => {
		if ( pages.length ) {
			render(
				pages,
				container,
				mw.msg( 'relatedarticles-read-more-heading' ),
				// Take up multiple columns if possible
				false,
				clickEventName
			);
		} else if ( container.parentNode ) {
			container.parentNode.removeChild( container );
		}
	} );
}

module.exports = {
	init,
	render,
	getCards,
	test: {
		relatedPages
	}
};
