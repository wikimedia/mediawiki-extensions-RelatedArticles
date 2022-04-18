( function () {
	'use strict';

	const CardModel = require( '../../resources/ext.relatedArticles.readMore/CardModel.js' );

	QUnit.module( 'ext.relatedArticles.cards/CardModel' );

	QUnit.test( '#set', function ( assert ) {
		let model = new CardModel( {} );

		model.on( 'change', function ( attributes ) {
			assert.strictEqual(
				attributes.foo,
				'bar',
				'It emits an event with the attribute that has changed.'
			);
		} );
		model.set( 'foo', 'bar' );

		model = new CardModel( {} );
		model.on( 'change', function () {
			assert.ok( false, 'It doesn\'t emit an event when silenced.' );
		} );

		model.set( 'foo', 'bar', true );
	} );

	QUnit.test( '#get', function ( assert ) {
		const model = new CardModel( {} );

		model.set( 'foo', 'bar' );
		assert.strictEqual( model.get( 'foo' ), 'bar', 'Got the correct value.' );
		assert.strictEqual( model.get( 'x' ), undefined, 'Got the correct value.' );
	} );
}() );
