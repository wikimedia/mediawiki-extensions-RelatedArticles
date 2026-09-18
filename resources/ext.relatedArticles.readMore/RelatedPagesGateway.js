// eslint-disable-next-line spaced-comment
/// <reference path="../mw.ts" />

/**
 * @class RelatedPagesGateway
 * @param {MwApi} api
 * @param {string} currentPage The page that the editorCuratedPages relate to
 * @param {string[]} editorCuratedPages A list of pages curated by editors for the current page
 * @param {boolean} useCirrusSearch Whether to hit the API when no editor-curated pages are available
 * @param {boolean} [onlyUseCirrusSearch=false] Whether to ignore the list of editor-curated pages
 * @param {boolean|string} [descriptionSource=false] Source to get the page description from
 */
function RelatedPagesGateway(
	api,
	currentPage,
	editorCuratedPages,
	useCirrusSearch,
	onlyUseCirrusSearch,
	descriptionSource
) {
	this.api = api;
	this.currentPage = currentPage;
	this.useCirrusSearch = useCirrusSearch;
	this.descriptionSource = descriptionSource;
	this.editorCuratedPages = !onlyUseCirrusSearch ? editorCuratedPages : [];

}

/**
 * @ignore
 * @param {MwApiQueryResponse} result
 * @return {MwApiPageObject[]}}
 */
function getPages( result ) {
	return result && result.query && result.query.pages ? result.query.pages : [];
}

/**
 * Get metadata about a given list of pages
 *
 * @param {MwApiActionQuery} params API query parameters. This must set 'action=query',
 *  and one of 'titles' or 'generator'. The caller may assume prop=pageimages gets added
 *  automatically.
 * @return {Promise<MwApiPageObject[]>}
 */
RelatedPagesGateway.prototype.getPagesFromApi = function ( params ) {
	const parameters = /** @type {MwApiActionQuery} */ Object.assign( {
		formatversion: 2,
		origin: '*',
		prop: 'pageimages',
		piprop: 'thumbnail',
		pithumbsize: 160 // FIXME: Revert to 80 once pithumbmode is implemented
	}, params );

	switch ( this.descriptionSource ) {
		case 'wikidata':
			parameters.prop += '|description';
			break;
		case 'textextracts':
			parameters.prop += '|extracts';
			parameters.exsentences = '1';
			parameters.exintro = '1';
			parameters.explaintext = '1';
			break;
		case 'pagedescription':
			parameters.prop += '|pageprops';
			parameters.ppprop = 'description';
			break;
	}

	// Cast from jQuery.Promise to native Promise
	return Promise.resolve( this.api.get( parameters ).then( getPages ) );
};

/**
 * Gets the related pages for the list of pages
 *
 * If there are related pages assigned to this page using the `related`
 * parser function, then they are returned.
 *
 * If there aren't any related pages assigned to the page, then the
 * CirrusSearch extension's {@link https://www.mediawiki.org/wiki/Help:CirrusSearch#morelike: "morelike:" feature}
 * is used. If the CirrusSearch extension isn't installed, then the API
 * call will fail gracefully and no related pages will be returned.
 * Thus the dependency on the CirrusSearch extension is soft.
 *
 * Related pages will have the following information:
 *
 * * The ID of the page corresponding to the title
 * * The thumbnail, if any
 * * The page description, if any
 *
 * @param {number} limit of pages to get. Should be between 1-20.
 * @return {Promise<MwApiPageObject[]>}
 */
RelatedPagesGateway.prototype.getForCurrentPage = function ( limit ) {
	const relatedPages = this.editorCuratedPages.slice( 0, limit );
	if ( relatedPages.length ) {
		return this.getPagesFromApi( {
			action: 'query',
			pilimit: relatedPages.length,
			continue: '',
			titles: relatedPages
		} );
	}
	if ( this.useCirrusSearch ) {
		const parameters = /** @type {MwApiActionQuery} */( {
			action: 'query',
			pilimit: limit,
			generator: 'search',
			gsrsearch: `morelike:${ this.currentPage }`,
			gsrnamespace: mw.config.get( 'wgNamespaceNumber' ),
			gsrlimit: limit,
			gsrqiprofile: 'classic_noboostlinks',
			// Currently, if you're logged in, then the API uses your language by default ard so responses
			// are always private i.e. they shouldn't be cached in a shared cache and can be cached by the
			// browser.
			//
			// By make the API use the language of the content rather than that of the user, the API
			// reponse is made public, i.e. they can be cached in a shared cache.
			//
			// See T97096 for more detail and discussion.
			uselang: 'content',

			// Instruct shared caches that the response will become stale in 24 hours.
			smaxage: 86400,

			// Instruct the browser that the response will become stale in 24 hours.
			maxage: 86400
		} );
		return this.getPagesFromApi( parameters );
	}

	return Promise.resolve( [] );
};

module.exports = RelatedPagesGateway;
