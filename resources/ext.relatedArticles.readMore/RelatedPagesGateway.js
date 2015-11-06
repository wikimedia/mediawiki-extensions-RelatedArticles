( function ( $ ) {

	// FIXME: Move into separate file as this module becomes larger.
	mw.relatedArticles = {};

	/**
	 * @class RelatedPagesGateway
	 * @param {mw.Api} api
	 * @param {string} currentPage the page that the editorCuratedArticles relate to
	 * @param {Array} editorCuratedArticles a list of articles curated by editors for the current page
	 * @param {boolean} useCirrusSearch whether to hit the API when no editor curated articles are available
	 */
	function RelatedPagesGateway( api, currentPage, editorCuratedArticles, useCirrusSearch ) {
		this.api = api;
		this.currentPage = currentPage;
		this.editorCuratedArticles = editorCuratedArticles || [];
		this.useCirrusSearch = useCirrusSearch;
	}
	OO.initClass( RelatedPagesGateway );

	/**
	 * Gets the related pages for the current page.
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
	 * * The Wikidata description, if any
	 *
	 * @method
	 * @param {number} limit of articles to get
	 * @return {jQuery.Promise}
	 */
	RelatedPagesGateway.prototype.getForCurrentPage = function ( limit ) {
		var parameters = {
				action: 'query',
				formatversion: 2,
				prop: 'pageimages|pageterms',
				piprop: 'thumbnail',
				pithumbsize: 80,
				wbptterms: 'description'
			},
			relatedArticles = ( this.editorCuratedArticles ).slice( 0, limit );

		if ( relatedArticles.length ) {
			parameters.pilimit = relatedArticles.length;
			parameters[ 'continue' ] = ''; // jscs:ignore requireDotNotation

			parameters.titles = relatedArticles;
		} else if ( this.useCirrusSearch ) {
			parameters.pilimit = limit;

			parameters.generator = 'search';
			parameters.gsrsearch = 'morelike:' + this.currentPage;
			parameters.gsrnamespace = '0';
			parameters.gsrlimit = limit;
		} else {
			return $.Deferred().resolve( [] );
		}

		return this.api.get( parameters )
			.then( getPages )
			.then( processPages );
	};

	/**
	 * @ignore
	 */
	function getPages( result ) {
		return result && result.query && result.query.pages ? result.query.pages : [];
	}

	/**
	 * @ignore
	 */
	function processPages( pages ) {
		return $.map( pages, function ( page ) {
			var result = {
				id: page.pageid,
				isMissing: !page.pageid,
				title: page.title,
				thumbnail: page.thumbnail,
				wikidataDescription: undefined
			};

			if (
				page.terms &&
				page.terms.description &&
				page.terms.description.length > 0
			) {
				result.wikidataDescription = page.terms.description[ 0 ];
			}

			return result;
		} );
	}

	mw.relatedArticles.RelatedPagesGateway = RelatedPagesGateway;
}( jQuery ) );
