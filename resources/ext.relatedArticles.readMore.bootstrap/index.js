( function ( $, mw ) {

	var config = mw.config.get( [ 'skin', 'wgNamespaceNumber', 'wgMFMode',
			'wgIsMainPage', 'wgAction' ] ),
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
		$window = $( window );

	/**
	 * Load related articles when the user scrolls past half of the window height.
	 *
	 * @ignore
	 */
	function loadRelatedArticles() {
		/**
		 * Threshold value to load related articles - after about half scroll
		 */
		var scrollThreshold = ( $( document ).height() / 2 ) - $window.height();

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

	/**
	 * Is the current page a diff page?
	 *
	 * @ignore
	 * @return {boolean}
	 */
	function isDiffPage() {
		var queryParams = new mw.Uri( window.location.href ).query;

		return !!(
			queryParams.type === 'revision' ||
			queryParams.hasOwnProperty( 'diff' ) ||
			queryParams.hasOwnProperty( 'oldid' )
		);
	}

	if (
		config.wgNamespaceNumber === 0 &&
		!config.wgIsMainPage &&
		// T120735
		config.wgAction === 'view' &&
		!isDiffPage() &&
		// any skin except minerva stable
		( config.skin !== 'minerva' || config.wgMFMode === 'beta' )
	) {
		// try related articles load on scroll
		$window.on( 'scroll', debouncedLoad );
		// try an initial load, in case of no scroll
		loadRelatedArticles();
	}

}( jQuery, mediaWiki ) );
