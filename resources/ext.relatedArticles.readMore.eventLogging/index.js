// See https://meta.wikimedia.org/wiki/Schema:RelatedArticles
( function ( $, mw ) {
	var $readMore,
		schemaRelatedPages,
		skin = mw.config.get( 'skin' ),
		$window = $( window );

	if ( !$.isFunction( navigator.sendBeacon ) ) {
		return;
	}

	/**
	 * Log when ReadMore is seen by the user
	 */
	function logReadMoreSeen() {
		if ( mw.viewport.isElementInViewport( $readMore.get( 0 ) ) ) {
			$window.off( 'scroll', logReadMoreSeen );
			schemaRelatedPages.log( { eventName: 'seen' } );
		}
	}

	schemaRelatedPages = new mw.eventLog.Schema(
		'RelatedArticles',
		// not sampled if the config variable is not set
		mw.config.get( 'wgRelatedArticlesLoggingSamplingRate', 0 ),
		{
			pageId: mw.config.get( 'wgArticleId' ),
			skin: ( skin === 'minerva' ) ? skin + '-' +  mw.config.get( 'wgMFMode' ) : skin,
			// We cannot depend on the uniqueness of mw.user.generateRandomSessionId(),
			// thus append the timestamp. See mw.user documentation for more info.
			userSessionToken: mw.user.generateRandomSessionId() +
				( new Date() ).getTime().toString()
		}
	);

	mw.trackSubscribe( 'ext.relatedArticles.logEnabled', function ( _, data ) {
		schemaRelatedPages.log( {
			eventName: data.isEnabled ? 'feature-enabled' : 'feature-disabled'
		} );
	} );

	mw.trackSubscribe( 'ext.relatedArticles.logReady', function ( _, data ) {
		$readMore = data.$readMore;

		// log ready
		schemaRelatedPages.log( { eventName: 'ready' } );

		// log when ReadMore is seen by the user
		$window.on(
			'scroll',
			$.debounce( 250, logReadMoreSeen )
		);
		logReadMoreSeen();

		// track the clicked event
		// TODO: This should be moved into the PageList component or, preferably, the CardList/Card views.
		$readMore.on( 'click', 'a', function () {
			var index = $( this ).parents( 'li' ).index();

			schemaRelatedPages.log( {
				eventName: 'clicked',
				clickIndex: index + 1
			} );
		} );
	} );

}( jQuery, mediaWiki ) );
