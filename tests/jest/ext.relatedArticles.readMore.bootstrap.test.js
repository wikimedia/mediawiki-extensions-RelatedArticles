const { getABTestToken } = require( '../../resources/ext.relatedArticles.readMore.bootstrap/index.js' );

describe( 'getABTestToken', () => {
	beforeEach( () => {
		// eslint-disable-next-line
		global.mw = {
			storage: {
				get: jest.fn(),
				set: jest.fn()
			},
			user: {
				sessionId: jest.fn().mockReturnValue( 'test-session-id' )
			}
		};
	} );

	it( 'should generate new token when no valid bucket exists', () => {
		mw.storage.get.mockReturnValue( null );
		const token = getABTestToken();
		expect( mw.user.sessionId ).toHaveBeenCalled();
		expect( token ).toEqual( 'test-session-id' );

	} );

	it( 'should not generate new token if locally stored bucket exists', () => {
		mw.storage.get.mockReturnValue( '123' );
		getABTestToken();
		expect( mw.user.sessionId ).not.toHaveBeenCalled();
	} );

} );
