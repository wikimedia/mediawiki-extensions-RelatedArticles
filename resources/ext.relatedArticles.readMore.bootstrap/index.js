( function () {

	var data = require( './data.json' ),
		RelatedPagesGateway = require( './RelatedPagesGateway.js' ),
		relatedPages = new RelatedPagesGateway(
			new mw.Api(),
			mw.config.get( 'wgPageName' ),
			mw.config.get( 'wgRelatedArticles' ),
			data.useCirrusSearch,
			data.onlyUseCirrusSearch,
			data.descriptionSource
		),
		// Make sure this is never undefined as I'm paranoid
		LIMIT = mw.config.get( 'wgRelatedArticlesCardLimit', 3 );

	/**
	 * Load related articles when the user scrolls past half of the window height.
	 *
	 * @ignore
	 */
	function loadRelatedArticles() {
		var readMore = document.querySelector( '.read-more-container' ),
			isSupported = 'IntersectionObserver' in window;

		if ( !readMore || !isSupported ) {
			// The container is not in the HTML for some reason and cannot be queried.
			// See T281547
			return;
		}

		/**
		 * @param {Element} container
		 */
		function initRelatedArticlesModule( container ) {
			$.when(
				mw.loader.using( 'ext.relatedArticles.readMore' ),
				relatedPages.getForCurrentPage( LIMIT )
			).then( function ( require, pages ) {
				if ( pages.length ) {
					require( 'ext.relatedArticles.readMore' ).render(
						pages,
						readMore
					);
				} else if ( container.parentNode ) {
					container.parentNode.removeChild( container );
				}
			} );
		}

		var doc = document.documentElement;
		// IntersectionObserver will not work if the component is already visible on the page.
		// To handle this case, we compare scroll height to viewport height.
		if ( ( doc.scrollHeight / 2 ) < doc.clientHeight ) {
			// Load straight away. We are on a stub page.
			initRelatedArticlesModule( readMore );
			return;
		}
		// eslint-disable-next-line compat/compat
		var observer = /** @type {IntersectionObserver} */( new IntersectionObserver( function ( entries ) {
			if ( !entries[ 0 ].isIntersecting ) {
				return;
			}
			// @ts-ignore
			observer.unobserve( readMore );
			observer.disconnect();
			// @ts-ignore
			initRelatedArticlesModule( readMore );
		}, {
			rootMargin: '-100% 0% 0% 0%'
		} ) );
		observer.observe( readMore );
	}

	function showReadMore() {
		// try an initial load, in case of no scroll
		loadRelatedArticles();
	}

	$( showReadMore );
}() );
