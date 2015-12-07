( function ( $, mw ) {
	// Make sure 'ext.cards' is loaded. It may not be because of the race
	// condition in the bootstrap file.
	mw.loader.using( 'ext.cards' ).done( function () {
		var CardModel = mw.cards.CardModel,
			CardView = mw.cards.CardView,
			CardListView = mw.cards.CardListView;

		/**
		 * Generates `mw.cards.CardView`s from pages
		 *
		 * @param {Object[]} pages
		 * @return {mw.cards.CardView[]}
		 */
		function getCards( pages ) {
			return $.map( pages, function ( page ) {
				var result = {
					title: page.title,
					url: mw.util.getUrl( page.title ),
					hasThumbnail: false
				};

				if ( page.thumbnail ) {
					result.hasThumbnail = true;
					result.thumbnailUrl = page.thumbnail.source;
					result.isThumbnailPortrait = page.thumbnail.height >= page.thumbnail.width;
				}

				if (
					page.terms &&
					page.terms.description &&
					page.terms.description.length
				) {
					result.extract = page.terms.description[ 0 ];
				}

				return new CardView( new CardModel( result ) );
			} );
		}

		mw.trackSubscribe( 'ext.relatedArticles.init', function ( _, pages ) {
			var $readMore,
				cards;

			cards = new CardListView( getCards( pages ) );

			$readMore = $( '<aside class="ra-read-more post-content"></aside>' )
				.append( $( '<h2></h2>' ).text( mw.msg( 'relatedarticles-read-more-heading' ) ) )
				.append( cards.$el );

			$( '#content' ).append( $readMore );

			// the ReadMore code is ready
			mw.track( 'ext.relatedArticles.logReady', { $readMore: $readMore } );
		} );
	} );

}( jQuery, mediaWiki ) );
