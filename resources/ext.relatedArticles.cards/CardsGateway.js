( function ( $, mw ) {
	'use strict';

	/**
	 * Default thumbnail width in pixels: 80px
	 * @readonly
	 */
	var THUMB_WIDTH = 80,
		CardModel = mw.cards.CardModel,
		CardView = mw.cards.CardView,
		CardListView = mw.cards.CardListView;

	/**
	 * @ignore
	 * @param {Object} thumb
	 * @return {string}
	 */
	function isValidThumbnail( thumb ) {
		return thumb.source.substr( 0, 7 ) === 'http://' || thumb.source.substr( 0, 8 ) === 'https://';
	}

	/**
	 * Gateway for interacting with an API
	 * It can be used to retrieve information about article(s). In the future
	 * it can also be used to update that information in the server.
	 *
	 * @class mw.cards.CardsGateway
	 * @param {Object} options
	 * @param {mw.Api} options.api an Api to use.
	 */
	function CardsGateway( options ) {
		this.api = options.api;
	}
	OO.initClass( CardsGateway );

	/**
	 * Fetch information about articleTitles from the API
	 * How to use:
	 *
	 *     @example
	 *     var gateway = new mw.cards.CardsGateway( { api: new mw.Api() } );
	 *
	 *     // '1' and '2' are page titles, while 200 is the desired thumbnail width
	 *     gateway.getCards( ['1', '2'], 200 ).done( function( cards ) {
	 *         $( '#bodyContent' ).append( cards.$el );
	 *     } );
	 *
	 * @param {string[]} articleTitles array of article titles
	 * @param {number} [thumbWidth] Thumbnail width in pixels. Defaults to
	 *  {@link THUMB_WIDTH}
	 * @return {jQuery.Deferred} the result resolves with a
	 *  {@link mw.cards.CardListView card list}
	 */
	CardsGateway.prototype.getCards = function ( articleTitles, thumbWidth ) {
		var article,
			cardViews = [],
			result = $.Deferred();

		if ( !articleTitles.length ) {
			result.resolve( new CardListView( cardViews ) );
			return result;
		}

		this.api.get( {
			action: 'query',
			prop: 'extracts|pageimages',
			explaintext: true,
			exlimit: articleTitles.length,
			exintro: true,
			exsentences: 1,
			pithumbsize: thumbWidth || THUMB_WIDTH,
			titles: articleTitles.join( '|' ),
			'continue': '',
			formatversion: 2
		} ).done( function ( data ) {
			if ( data.query && data.query.pages ) {
				cardViews = $.map( data.query.pages, function ( page ) {
					article = {
						title: page.title,
						url: mw.util.getUrl( page.title ),
						hasThumbnail: false
					};

					if ( page.thumbnail && isValidThumbnail( page.thumbnail ) ) {
						article.hasThumbnail = true;
						article.thumbnailUrl = page.thumbnail.source;
					}

					if ( page.extract ) {
						article.extract = page.extract;
					}

					return new CardView( new CardModel( article ) );
				} );
			}
			result.resolve( new CardListView( cardViews ) );
		} ).fail( function () {
			result.resolve( new CardListView( cardViews ) );
		} );

		return result;
	};

	mw.cards.CardsGateway = CardsGateway;
}( jQuery, mediaWiki ) );
