( function () {
	'use strict';

	var CardModel = require( '../../resources/ext.relatedArticles.readMore/CardModel.js' ),
		CardView = require( '../../resources/ext.relatedArticles.readMore/CardView.js' );

	QUnit.module( 'ext.relatedArticles.cards/CardView' );

	QUnit.test( '#_render escapes the thumbnailUrl model attribute', function ( assert ) {
		var model = new CardModel( {
				title: 'One',
				url: mw.util.getUrl( 'One' ),
				hasThumbnail: true,
				thumbnailUrl: 'http://foo.bar/\');display:none;"//baz.jpg',
				isThumbnailProtrait: false
			} ),
			view = new CardView( model ),
			style;

		style = view.$el.find( '.ext-related-articles-card-thumb' )
			.eq( 0 )
			.attr( 'style' );

		assert.strictEqual(
			style,
			"background-image: url(\"http://foo.bar/');display:none;\\\"//baz.jpg\");"
		);
	} );
}() );
