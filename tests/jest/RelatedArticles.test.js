let RelatedArticles;
describe( 'RelatedArticles', () => {
	beforeEach( () => {
		RelatedArticles = require( '../../resources/ext.relatedArticles.readMore/RelatedArticles.js' );
	} );

	it( 'renders with cards', () => {
		const html = RelatedArticles( {
			cards: [
				{
					id: '4',
					label: 'Title',
					value: 'Title',
					description: 'Description'
				}
			]
		} );
		expect(
			html
		).toContain( 'class="cdx-card"' );
	} );
} );
