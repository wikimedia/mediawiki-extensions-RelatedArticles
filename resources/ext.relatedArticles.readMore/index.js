( function () {
	/**
	 * Renders the related articles.
	 */
	function main() {
		var CardModel = require( './CardModel.js' ),
			CardView = require( './CardView.js' ),
			CardListView = require( './CardListView.js' );

		/**
		 * Generates `mw.cards.CardView`s from pages
		 *
		 * @param {Object[]} pages
		 * @return {mw.cards.CardView[]}
		 */
		function getCards( pages ) {
			return pages.map( function ( page ) {
				var result = {
					title: page.title,
					url: mw.util.getUrl( page.title ),
					hasThumbnail: false,
					extract: ( page.description || page.extract ||
						( page.pageprops ? page.pageprops.description : '' ) )
				};

				if ( page.thumbnail ) {
					result.hasThumbnail = true;
					result.thumbnailUrl = page.thumbnail.source;
					result.isThumbnailPortrait = page.thumbnail.height >= page.thumbnail.width;
				}

				return new CardView( new CardModel( result ) );
			} );
		}

		mw.trackSubscribe( 'ext.relatedArticles.init', function ( _, pages ) {
			var $readMore,
				cards;

			cards = new CardListView( getCards( pages ) );

			$readMore = $( '<aside>' ).addClass( 'ra-read-more noprint' )
				.append( $( '<h2>' ).text( mw.msg( 'relatedarticles-read-more-heading' ) ) )
				.append( cards.$el );

			// eslint-disable-next-line no-jquery/no-global-selector
			$( '.read-more-container' ).append( $readMore );
		} );
	}
	main();

}() );
