// See https://meta.wikimedia.org/wiki/Schema:RelatedArticles
( function ( $ ) {
	var $readMore,
		schemaRelatedPages,
		skin = mw.config.get( 'skin' ),
		$window = $( window );

	/**
	 * Check if at least half of the element's height and half of its width are in viewport
	 *
	 * @method
	 * @param {jQuery.Object} $el - element that's being tested
	 * @return {boolean}
	 */
	function isElementInViewport( $el ) {
		var windowHeight = $window.height(),
			windowWidth = $window.width(),
			windowScrollLeft = $window.scrollLeft(),
			windowScrollTop = $window.scrollTop(),
			elHeight = $el.height(),
			elWidth = $el.width(),
			elOffset = $el.offset();

		return (
			( windowScrollTop + windowHeight >= elOffset.top + elHeight / 2 ) &&
			( windowScrollLeft + windowWidth >= elOffset.left + elWidth / 2 ) &&
			( windowScrollTop <= elOffset.top + elHeight / 2 )
		);
	}

	/**
	 * Log when ReadMore is seen by the user
	 */
	function logReadMoreSeen() {
		if ( isElementInViewport( $readMore ) ) {
			$window.off( 'scroll', logReadMoreSeen );
			schemaRelatedPages.log( { eventName: 'seen' } );
		}
	}

	mw.trackSubscribe( 'ext.relatedArticles.logReady', function ( _, data ) {
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
