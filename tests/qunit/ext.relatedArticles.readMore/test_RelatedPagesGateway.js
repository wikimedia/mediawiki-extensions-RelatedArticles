( function ( M, $ ) {
	var RelatedPagesGateway = mw.relatedArticles.RelatedPagesGateway,
		relatedPages = {
			query: {
				pages: {
					123: {
						id: 123,
						title: 'Oh noes',
						ns: 0,
						thumbnail: {
							source: 'http://placehold.it/200x100'
						}
					}
				}
			}
		},
		emptyRelatedPages = {
			query: {
				pages: {
				}
			}
		};

	QUnit.module( 'RelatedArticles readMore - Related pages api', {
		setup: function () {
			this.api = new mw.Api();
		}
	} );

	QUnit.test( 'Returns an array with the results when api responds', 2, function ( assert ) {
		var gateway = new RelatedPagesGateway( this.api, 'Foo', null, true );
		this.sandbox.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		gateway.getForCurrentPage( 1 ).then( function ( results ) {
			assert.ok( $.isArray( results ), 'Results must be an array' );
			assert.strictEqual( results[ 0 ].title, 'Oh noes' );
		} );
	} );

	QUnit.test( 'Empty related pages is handled fine.', 2, function ( assert ) {
		var gateway = new RelatedPagesGateway( this.api, 'Foo', null, true );
		this.sandbox.stub( this.api, 'get' ).returns( $.Deferred().resolve( emptyRelatedPages ) );

		gateway.getForCurrentPage( 1 ).then( function ( results ) {
			assert.ok( $.isArray( results ), 'Results must be an array' );
			assert.strictEqual( results.length, 0 );
		} );
	} );

	QUnit.test( 'Empty related pages with no cirrus search is handled fine. No API request.', 3, function ( assert ) {
		var gateway = new RelatedPagesGateway( this.api, 'Foo', [], false ),
			spy = this.sandbox.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		gateway.getForCurrentPage( 1 ).then( function ( results ) {
			assert.ok( $.isArray( results ), 'Results must be an array' );
			assert.ok( !spy.called, 'API is not invoked' );
			assert.strictEqual( results.length, 0 );
		} );
	} );

	QUnit.test( 'Related pages from editor curated content', 1, function ( assert ) {
		var gateway = new RelatedPagesGateway( this.api, 'Foo', [ { title: 1 } ], false );
		this.sandbox.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		gateway.getForCurrentPage( 1 ).then( function ( results ) {
			assert.strictEqual( results.length, 1,
				'API still hit despite cirrus being disabled.' );
		} );
	} );

}( mw.mobileFrontend, jQuery ) );
