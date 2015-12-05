( function ( $ ) {

	var config = mw.config.get( [ 'skin', 'wgNamespaceNumber', 'wgMFMode', 'wgIsMainPage' ] ),
		relatedPages = new mw.relatedPages.RelatedPagesGateway(
			new mw.Api(),
			mw.config.get( 'wgPageName' ),
			mw.config.get( 'wgRelatedArticles' ),
			mw.config.get( 'wgRelatedArticlesUseCirrusSearch' ),
			mw.config.get( 'wgRelatedArticlesOnlyUseCirrusSearch' )
		),
		LIMIT = 3,
		debouncedLoad = $.debounce( 100, function () {
			loadRelatedArticles();
		} ),
		$window = $( window ),
		/**
		 * Threshold value to load related articles - after about half scroll
		 */
		scrollThreshold = ( $( document ).height() / 2 ) - $window.height();

	function loadRelatedArticles() {
		if ( $window.scrollTop() > scrollThreshold ) {
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
			// detach handler to stop subsequent loads on scroll
			$window.off( 'scroll', debouncedLoad );
		}
	}
	if (
		config.wgNamespaceNumber === 0 &&
		!config.wgIsMainPage &&
		// any skin except minerva stable
		( config.skin !== 'minerva' || config.wgMFMode === 'beta' )
	) {
		// try related articles load on scroll
		$window.on( 'scroll', debouncedLoad );
		// try an initial load, in case of no scroll
		loadRelatedArticles();
	}

}( jQuery ) );
