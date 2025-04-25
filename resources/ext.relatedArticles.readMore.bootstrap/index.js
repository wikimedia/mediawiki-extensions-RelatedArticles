const CLICK_EVENT_FOOTER = 'relatedArticles.footer';

/**
 * Load related articles when the user scrolls past half of the window height.
 *
 * @ignore
 */
function loadRelatedArticles() {
	const readMore = document.querySelector( '.read-more-container' );

	/**
	 * @param {Element} container
	 * @param {string} clickEventName that fires when cards are clicked
	 */
	function initRelatedArticlesModule( container, clickEventName ) {
		$.when(
			mw.loader.using( 'ext.relatedArticles.readMore' )
		).then( (
			/** @type {Function} */ require
		) => {
			require( 'ext.relatedArticles.readMore' ).init(
				container,
				clickEventName
			);
		} );
	}

	if ( !readMore ) {
		// The container is not in the HTML for some reason and cannot be queried.
		// See T281547
		return;
	}
	const doc = document.documentElement;
	// IntersectionObserver will not work if the component is already visible on the page.
	// To handle this case, we compare scroll height to viewport height.
	if ( ( doc.scrollHeight / 2 ) < doc.clientHeight ) {
		// Load straight away. We are on a stub page.
		initRelatedArticlesModule( readMore, CLICK_EVENT_FOOTER );
		return;
	}
	// eslint-disable-next-line compat/compat
	const observer = /** @type {IntersectionObserver} */( new IntersectionObserver( ( ( entries ) => {
		if ( !entries[ 0 ].isIntersecting ) {
			return;
		}
		// @ts-ignore
		observer.unobserve( readMore );
		observer.disconnect();
		// @ts-ignore
		initRelatedArticlesModule( readMore, CLICK_EVENT_FOOTER );
	} ), {
		rootMargin: '-100% 0% 0% 0%'
	} ) );
	observer.observe( readMore );
}

$( loadRelatedArticles );
