// eslint-disable-next-line spaced-comment
/// <reference path="../mw.ts" />

/**
 * @class RelatedPagesGateway
 * @param {MwApi} api
 * @param {string} currentPage the page that the editorCuratedPages relate to
 * @param {string[]|null} editorCuratedPages a list of pages curated by editors for the current page
 * @param {boolean} useCirrusSearch whether to hit the API when no editor-curated pages are available
 * @param {boolean} [onlyUseCirrusSearch=false] whether to ignore the list of editor-curated pages
 * @param {boolean|string} [descriptionSource=false] source to get the page description from
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

	if ( onlyUseCirrusSearch ) {
		editorCuratedPages = [];
	}

	this.editorCuratedPages = editorCuratedPages || [];

}

/**
 * @param {JQuery.Promise<any>} jQP
 * @return {Promise<any>}
 */
const toPromise = ( jQP ) => new Promise( ( resolve, reject ) => {
	jQP.then( ( pages ) => resolve( pages ), ( e ) => reject( e ) );
} );

/**
 * @ignore
 * @param {MwApiQueryResponse} result
 * @return {MwApiPageObject[]}}
 */
function getPages( result ) {
	return result && result.query && result.query.pages ? result.query.pages : [];
}

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
 * @param {MwApiActionQuery} params for api
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

	return toPromise(
		this.api.get( parameters )
			.then( getPages )
	);
};

/**
 * Gets the related pages for the list of pages
 *
 * @param {string[]} titles
 * @return {Promise<MwApiPageObject[]>}
 */
RelatedPagesGateway.prototype.getPages = function ( titles ) {
	return this.getPagesFromApi( {
		action: 'query',
		pilimit: titles.length,
		continue: '',
		titles
	} );
};

/**
 * Gets the related pages for the list of pages
 *
 * @param {number} limit of pages to get. Should be between 1-20.
 * @return {Promise<MwApiPageObject[]>}
 */
RelatedPagesGateway.prototype.getForCurrentPage = function ( limit ) {
	const relatedPages = this.editorCuratedPages.slice( 0, limit );
	if ( relatedPages.length ) {
		return this.getPages( relatedPages );
	} else if ( this.useCirrusSearch ) {
		const parameters = /** @type {MwApiActionQuery} */( {
			action: 'query'
		} );
		parameters.pilimit = limit;

		parameters.generator = 'search';
		parameters.gsrsearch = `morelike:${this.currentPage}`;
		parameters.gsrnamespace = '0';
		parameters.gsrlimit = limit;
		parameters.gsrqiprofile = 'classic_noboostlinks';

		// Currently, if you're logged in, then the API uses your language by default ard so responses
		// are always private i.e. they shouldn't be cached in a shared cache and can be cached by the
		// browser.
		//
		// By make the API use the language of the content rather than that of the user, the API
		// reponse is made public, i.e. they can be cached in a shared cache.
		//
		// See T97096 for more detail and discussion.
		parameters.uselang = 'content';

		// Instruct shared caches that the response will become stale in 24 hours.
		parameters.smaxage = 86400;

		// Instruct the browser that the response will become stale in 24 hours.
		parameters.maxage = 86400;
		return this.getPagesFromApi( parameters ).then( ( pages ) => Promise.resolve( pages ) );
	} else {
		return Promise.resolve( [] );
	}
};

module.exports = RelatedPagesGateway;
