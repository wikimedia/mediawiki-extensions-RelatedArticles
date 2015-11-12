( function ( $ ) {

	var config = mw.config.get( [ 'skin', 'wgNamespaceNumber', 'wgMFMode', 'wgIsMainPage' ] ),
		relatedPages = new mw.relatedArticles.RelatedPagesGateway(
			new mw.Api(),
			mw.config.get( 'wgPageName' ),
			mw.config.get( 'wgRelatedArticles' ),
			mw.config.get( 'wgRelatedArticlesUseCirrusSearch' ),
			mw.config.get( 'wgRelatedArticlesOnlyUseCirrusSearch' )
		),
		LIMIT = 4;

	if (
		config.wgNamespaceNumber === 0 &&
		!config.wgIsMainPage &&
		config.skin === 'minerva' &&
		config.wgMFMode === 'beta'
	) {
		$.when(
			// Note we load dependencies here rather than ResourceLoader
			// to avoid PHP exceptions when MobileFrontend not installed
			// which should never happen given the if statement.
			mw.loader.using( [ 'mobile.pagelist.scripts', 'ext.relatedArticles.readMore.minerva' ] ),
			relatedPages.getForCurrentPage( LIMIT )
		).done( function ( _, pages ) {
			if ( pages.length ) {
				mw.track( 'ext.relatedArticles.init', pages );
			}
		} );
	}

}( jQuery ) );
