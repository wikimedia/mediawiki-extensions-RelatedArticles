const config = require( './config.json' );

/**
 * Load related articles when the user scrolls past half of the window height.
 *
 * @ignore
 */
function loadRelatedArticles() {
	const readMore = document.querySelector( '.read-more-container' );

	/**
	 * @param {Element} container
	 */
	function initRelatedArticlesModule( container ) {
		$.when(
			mw.loader.using( 'ext.relatedArticles.readMore' )
		).then( (
			/** @type {Function} */ require
		) => {
			require( 'ext.relatedArticles.readMore' ).init(
				container
			);
		} );
	}

	/**
	 * @param {Element} node
	 */
	const emptySearchHook = ( node ) => {
		let relatedContainer = node.querySelector( '.ext-related-empty-search' );
		if ( !relatedContainer ) {
			relatedContainer = document.createElement( 'div' );
			relatedContainer.setAttribute( 'class', 'ext-related-empty-search' );
			node.appendChild( relatedContainer );
		}
		relatedContainer.removeAttribute( 'style' );
		initRelatedArticlesModule( relatedContainer );
	};

	/**
	 * Enables recommendations in search results.
	 */
	function loadEmptySearchHook() {
		mw.hook( 'ext.MobileFrontend.searchOverlay.empty' ).add( emptySearchHook );
	}

	const experiment = config.RelatedArticlesABTestEnrollment;
	const group = mw.experiments.getBucket(
		experiment,
		mw.user.sessionId()
	);
	const WEB_AB_TEST_ENROLLMENT_HOOK = 'mediawiki.web_AB_test_enrollment';
	if ( experiment.enabled && group.indexOf( '-unsampled' ) === -1 ) {
		mw.hook( WEB_AB_TEST_ENROLLMENT_HOOK ).fire( {
			group,
			experimentName: experiment.name
		} );
		if ( group === 'experimentEnabled' ) {
			loadEmptySearchHook();
		}
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
		initRelatedArticlesModule( readMore );
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
		initRelatedArticlesModule( readMore );
	} ), {
		rootMargin: '-100% 0% 0% 0%'
	} ) );
	observer.observe( readMore );
}

$( loadRelatedArticles );
