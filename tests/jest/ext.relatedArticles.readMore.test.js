const { render } = require( '../../resources/ext.relatedArticles.readMore/index.js' );

describe( 'ext.relatedArticles.readMore.bootstrap', () => {
	it( 'renders without error', () => {
		const element = document.createElement( 'div' );

		render( [
			{
				title: 'Hello no description'
			},
			{
				title: 'Hello',
				description: 'Description',
				thumbnail: {
					source: 'hello.gif',
					width: 200,
					height: 200
				}
			},
			{
				title: 'Hello with extract',
				extract: 'Extract',
				thumbnail: {
					source: 'helloEx.gif',
					width: 200,
					height: 200
				}
			},
			{
				title: 'Hello with pageprops',
				pageprops: {
					description: 'Page props desc'
				}
			}
		], element );
		expect( element.innerHTML ).toMatchSnapshot();
	} );
} );
