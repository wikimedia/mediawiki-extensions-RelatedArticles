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
	mw.config.get( 'wgRelatedArticles' ),
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
	return pages.map( function ( page ) {
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
 */
function render( pages, el, heading, isContainerSmall ) {
	el.innerHTML = RelatedArticles( {
		isContainerSmall,
		heading,
		cards: getCards( pages )
	} );
}

/**
 * @param {HTMLElement} container to initialize into
 */
function init( container ) {
	relatedPages.getForCurrentPage( LIMIT ).then( ( /** @type {MwApiPageObject[]} */ pages ) => {
		if ( pages.length ) {
			render(
				pages,
				container,
				mw.msg( 'relatedarticles-read-more-heading' ),
				// Take up multiple columns if possible
				false
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
