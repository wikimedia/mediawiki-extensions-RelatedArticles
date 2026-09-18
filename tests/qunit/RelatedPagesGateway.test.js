const RelatedPagesGateway = require( 'ext.relatedArticles.readMore/RelatedPagesGateway.js' );
const lotsaRelatedPages = [ 'A', 'B', 'C', 'D', 'E', 'F' ];
const relatedPages = {
	query: {
		pages: [
			{
				pageid: 123,
				title: 'Oh noes',
				ns: 0,
				thumbnail: {
					source: 'http://placehold.it/200x100'
				}
			}
		]
	}
};
const emptyRelatedPages = {
	query: {
		pages: []
	}
};

QUnit.module( 'ext.relatedArticles.gateway', ( hooks ) => {
	hooks.beforeEach( function () {
		this.api = new mw.Api();
	} );

	QUnit.test( 'getForCurrentPage [Cirrus-only with results]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', [], true );
		sinon.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		const results = await gateway.getForCurrentPage( 1 );
		assert.deepEqual( results, [ {
			ns: 0,
			title: 'Oh noes',
			pageid: 123,
			thumbnail: {
				source: 'http://placehold.it/200x100'
			}
		} ] );
	} );

	QUnit.test( 'getForCurrentPage [Cirrus-only empty]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', [], true );
		sinon.stub( this.api, 'get' ).returns( $.Deferred().resolve( emptyRelatedPages ) );

		const results = await gateway.getForCurrentPage( 1 );
		assert.deepEqual( results, [], 'empty array' );
	} );

	QUnit.test( 'getForCurrentPage [Editor curated empty and Cirrus empty]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', [], false );
		const spy = sinon.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		const results = await gateway.getForCurrentPage( 1 );
		assert.deepEqual( results, [], 'empty array' );
		assert.false( spy.called, 'No API request is made' );
	} );

	QUnit.test( 'getForCurrentPage [Editor curated results]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', [ { title: 1 } ], false );
		sinon.stub( this.api, 'get' ).returns( $.Deferred().resolve( relatedPages ) );

		const results = await gateway.getForCurrentPage( 1 );
		assert.strictEqual( results.length, 1, 'API still hit if Cirrus is disabled' );
	} );

	QUnit.test( 'getForCurrentPage [Fewer cards than limit]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', lotsaRelatedPages, true );
		// needed to get page images etc..
		const stub = sinon.stub( this.api, 'get' )
			.returns( $.Deferred().resolve( relatedPages ) );

		await gateway.getForCurrentPage( 20 );
		assert.strictEqual( stub.args[ 0 ][ 0 ].titles.length, 6, 'All cards without limit enforced' );
	} );

	QUnit.test( 'getForCurrentPage [More cards than limit]', async function ( assert ) {
		const gateway = new RelatedPagesGateway( this.api, 'Foo', lotsaRelatedPages, true );
		// needed to get page images etc..
		const stub = sinon.stub( this.api, 'get' )
			.returns( $.Deferred().resolve( relatedPages ) );

		await gateway.getForCurrentPage( 2 );
		assert.strictEqual( stub.args[ 0 ][ 0 ].titles.length, 2, 'Results restricted to limit' );
	} );

	QUnit.test( 'getForCurrentPage [wgRelatedArticlesOnlyUseCirrusSearch=true ignores curated pages]', async function ( assert ) {
		const wgRelatedArticles = [
			'Bar',
			'Baz',
			'Qux'
		];
		const gateway = new RelatedPagesGateway( this.api, 'Foo', wgRelatedArticles, true, true );
		const spy = sinon.stub( this.api, 'get' )
			.returns( $.Deferred().resolve( relatedPages ) );

		await gateway.getForCurrentPage( 1 );
		const parameters = spy.lastCall.args[ 0 ];
		assert.strictEqual(
			parameters.generator,
			'search',
			'hit the CirrusSearch API even if wgRelatedArticles is non-empty'
		);
	} );
} );
