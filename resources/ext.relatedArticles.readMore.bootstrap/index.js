( function ( $ ) {

	var relatedArticles = mw.config.get( 'wgRelatedArticles', [] ).slice( 0, 4 ),
		config = mw.config.get( [ 'skin', 'wgNamespaceNumber', 'wgMFMode', 'wgIsMainPage' ] ),
		module;

	/**
	 * Retrieves the data required to render a card.
	 *
	 * Given a title, the following additional information is retrieved
	 * from the API:
	 *
	 * * The ID of the page corresponding to the title
	 * * The thumbnail, if any
	 * * The Wikidata description, if any
	 *
	 * @private
	 *
	 * @param {string[]} titles
	 * @return {jQuery.Promise}
	 */
	function getData( titles ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			prop: 'pageimages|pageterms',
			wbptterms: 'description',
			pilimit: titles.length,
			'continue': '',

			titles: titles
		} ).then( function ( data ) {
			if ( !data.query || !data.query.pages ) {
				return [];
			}

			return $.map( data.query.pages, function ( page ) {
				var result = {
					id: page.pageid,
					title: page.title,
					thumbnail: page.thumbnail,
					description: undefined
				};

				if (
					page.terms &&
					page.terms.description &&
					page.terms.description.length > 0
				) {
					result.description = page.terms.description[ 0 ];
				}

				return result;
			} );
		} );
	}

	if (
		relatedArticles.length > 0 &&
		config.wgNamespaceNumber === 0 &&
		!config.wgIsMainPage &&
		config.skin === 'minerva' &&
		config.wgMFMode === 'beta'
	) {
		module = 'ext.relatedArticles.readMore.minerva';

		$( function () {
			$.when(
				mw.loader.using( module ),
				getData( relatedArticles )
			).done( function ( _, data ) {
				mw.track( 'ext.relatedArticles.init', { pages: data } );
			} );
		} );
	}

}( jQuery ) );
