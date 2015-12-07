( function ( $, mw ) {

	var config = mw.config.get( [ 'skin', 'wgNamespaceNumber', 'wgMFMode', 'wgIsMainPage' ] ),
		relatedPages = new mw.relatedPages.RelatedPagesGateway(
			new mw.Api(),
			mw.config.get( 'wgPageName' ),
			mw.config.get( 'wgRelatedArticles' ),
			mw.config.get( 'wgRelatedArticlesUseCirrusSearch' ),
			mw.config.get( 'wgRelatedArticlesOnlyUseCirrusSearch' )
		),
		LIMIT = 3;

	if (
		config.wgNamespaceNumber === 0 &&
		!config.wgIsMainPage &&
		// any skin except minerva stable
		( config.skin !== 'minerva' || config.wgMFMode === 'beta' )
	) {
		$.when(
			// Note we load dependencies here rather than ResourceLoader
			// to avoid PHP exceptions when Cards not installed
			// which should never happen given the if statement.
			mw.loader.using( [ 'ext.cards', 'ext.relatedArticles.readMore' ] ),
			relatedPages.getForCurrentPage( LIMIT )
		).done( function ( _, pages ) {
			if ( pages.length ) {
				mw.track( 'ext.relatedArticles.init', pages );
			}
		} );
	}

}( jQuery, mediaWiki ) );
