( function ( $ ) {

	var CardsGateway = mw.cards.CardsGateway;

	QUnit.module( 'ext.relatedArticles.cards/CardsGateway' );

	QUnit.test( '#getCards resolves with empty list of cards when no titles are given', 1, function ( assert ) {
		var cards = new CardsGateway( { api: new mw.Api() } );

		return cards.getCards( [] ).then( function ( cards ) {
			assert.ok( cards.cardViews.length === 0 );
		} );
	} );

	QUnit.test( '#getCards processes the result of the API call', 5, function ( assert ) {
		var api = new mw.Api(),
			cards = new CardsGateway( { api: api } ),
			result = {
				query: {
					pages: [
						{
							pageid: 1,
							ns: 0,
							title: 'One'
						},
						{
							pageid: 2,
							ns: 0,
							title: 'Two',
							extract: 'This is the second page.'
						},
						{
							pageid: 3,
							ns: 0,
							title: 'Three',
							thumbnail: {
								source: 'http://foo.bar/baz.jpg',
								width: 50,
								height: 38
							},
							pageimage: 'baz.jpg'
						},

						// [T118553] Thumbnail URLs must start with
						// "http[s]://".
						{
							pageid: 4,
							ns: 0,
							title: 'Four',
							thumbnail: {
								source: '//foo.bar/baz/qux.jpg',
								width: 50,
								height: 38
							},
							pageimage: 'qux.jpg'
						}
					]
				}
			};

		this.sandbox.stub( api, 'get' ).returns( $.Deferred().resolve( result ) );

		return cards.getCards( [ 'One', 'Two', 'Three', 'Four' ] ).then( function ( cards ) {
			assert.ok( cards.cardViews.length === 4 );

			// One: no extract; no thumbnail.
			assert.deepEqual( cards.cardViews[ 0 ].model.attributes, {
				title: 'One',
				url: mw.util.getUrl( 'One' ),
				hasThumbnail: false
			} );

			// Two: no thumbnail.
			assert.deepEqual( cards.cardViews[ 1 ].model.attributes, {
				title: 'Two',
				url: mw.util.getUrl( 'Two' ),
				hasThumbnail: false,
				extract: 'This is the second page.'
			} );

			// Three: no extract.
			assert.deepEqual( cards.cardViews[ 2 ].model.attributes, {
				title: 'Three',
				url: mw.util.getUrl( 'Three' ),
				hasThumbnail: true,
				thumbnailUrl: 'http://foo.bar/baz.jpg'
			} );

			// Four: invalid thumbnail URL.
			assert.deepEqual( cards.cardViews[ 3 ].model.attributes, {
				title: 'Four',
				url: mw.util.getUrl( 'Four' ),
				hasThumbnail: false
			} );
		} );
	} );

	QUnit.test( '#getCards resolves with empty list of cards when the API call fails', 2, function ( assert ) {
		var api = new mw.Api(),
			cards = new CardsGateway( { api: api } ),
			getStub = this.sandbox.stub( api, 'get' ),
			done1 = assert.async(),
			done2 = assert.async();

		getStub.returns( $.Deferred().reject() );

		cards.getCards( [ 'Foo' ] ).then( function ( cards ) {
			assert.ok( cards.cardViews.length === 0 );
		} )
		.always( done1 );

		// The API call can succeed but return no results, which should
		// also be handled as a failure.
		getStub.returns( $.Deferred().resolve( {
			query: {}
		} ) );

		cards.getCards( [ 'Foo' ] ).then( function ( cards ) {
			assert.ok( cards.cardViews.length === 0 );
		} )
		.always( done2 );
	} );
}( jQuery ) );
