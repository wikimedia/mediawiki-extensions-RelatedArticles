( function () {
	/**
	 * Load related articles when the user scrolls past half of the window height.
	 *
	 * @ignore
	 */
	function loadRelatedArticles() {
		const readMore = document.querySelector( '.read-more-container' );

		if ( !readMore ) {
			// The container is not in the HTML for some reason and cannot be queried.
			// See T281547
			return;
		}

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
}() );
