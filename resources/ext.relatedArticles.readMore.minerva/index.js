( function ( $ ) {

	var MOBILE_WEB_WATCHING_FUNNEL = 'read-more';

	/**
	 * Converts the set of pages generated in the init script into a set of
	 * `Page`s, suitable for use with `WatchstarPageList`.
	 *
	 * @param {Object[]} pages
	 * @return {Page[]}
	 */
	function convertPages( pages ) {
		var Page = mw.mobileFrontend.require( 'mobile.startup/Page' );

		return $.map( pages, function ( rawPage ) {
			return new Page( rawPage );
		} );
	}

	mw.trackSubscribe( 'ext.relatedArticles.init', function ( _, pages ) {
		var WatchstarPageList = mw.mobileFrontend.require( 'mobile.pagelist.scripts/WatchstarPageList' ),
			pageList,
			$container = $( '#content' ),
			$readMore;

		pageList = new WatchstarPageList( {
			pages: convertPages( pages ),

			// FIXME: When the user clicks any watchstar, a
			// MobileWebWatching event is logged. Watchstar, which
			// logs the event, has a sensible default value for
			// MobileWebWatching.funnel, but it's overwritten
			// by WatchstarPageList.
			funnel: MOBILE_WEB_WATCHING_FUNNEL,

			api: new mw.Api()
		} );

		$readMore = $( '<aside class="ra-read-more post-content"></aside>' );
		$readMore.append(
			$( '<h2></h2>' ).text( mw.msg( 'relatedarticles-read-more-heading' ) )
		);

		$readMore.append( pageList.$el );

		$container.append( $readMore );

		// the ReadMore code is ready
		mw.track( 'ext.relatedArticles.logReady', { $readMore: $readMore } );
	} );

}( jQuery ) );