<?php

namespace RelatedArticles;

use Parser;
use OutputPage;
use ParserOutput;

class Hooks {

	/**
	 * Handler for the <code>ParserFirstCallInit</code> hook.
	 *
	 * Registers the <code>related</code> parser function (see
	 * {@see Hooks::onFuncRelated}).
	 *
	 * @param Parser &$parser Paser object
	 * @return boolean Always <code>true</code>
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'related', 'RelatedArticles\\Hooks::onFuncRelated' );

		return true;
	}

	/**
	 * The <code>related</code> parser function.
	 *
	 * Appends the arguments to the internal list so that it can be used
	 * more that once per page.
	 * We don't use setProperty here is there is no need
	 * to store it as a page prop in the database, only in the cache.
	 *
	 * @todo Test for uniqueness
	 * @param Parser $parser Parser object
	 *
	 * @return string Always <code>''</code>
	 */
	public static function onFuncRelated( Parser $parser ) {
		$parserOutput = $parser->getOutput();
		$relatedPages = $parserOutput->getExtensionData( 'RelatedArticles' );
		if ( !$relatedPages ) {
			$relatedPages = [];
		}
		$args = func_get_args();
		array_shift( $args );

		// Add all the related pages passed by the parser function
		// {{#related:Test with read more|Foo|Bar}}
		foreach ( $args as $relatedPage ) {
			$relatedPages[] = $relatedPage;
		}
		$parserOutput->setExtensionData( 'RelatedArticles', $relatedPages );

		return '';
	}

	/**
	 * Passes the related pages list from the cached parser output
	 * object to the output page for rendering.
	 *
	 * The list of related pages will be retrieved using
	 * <code>ParserOutput#getExtensionData</code>.
	 *
	 * @param OutputPage &$out the OutputPage object
	 * @param ParserOutput $parserOutput ParserOutput object
	 * @return boolean Always <code>true</code>
	 */
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parserOutput ) {
		$related = $parserOutput->getExtensionData( 'RelatedArticles' );

		if ( $related ) {
			$out->setProperty( 'RelatedArticles', $related );
		}

		return true;
	}

	/**
	 * Register QUnit tests.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array &$modules array of javascript testing modules
	 * @param \ResourceLoader &$rl Resource Loader
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( &$modules, &$rl ) {
		$boilerplate = [
			'localBasePath' => __DIR__ . '/../tests/qunit/',
			'remoteExtPath' => 'RelatedArticles/tests/qunit',
			'targets' => [ 'desktop', 'mobile' ],
		];

		$modules['qunit']['ext.relatedArticles.cards.tests'] = $boilerplate + [
			'dependencies' => [
				'ext.relatedArticles.cards'
			],
			'scripts' => [
				'ext.relatedArticles.cards/CardModel.js',
				'ext.relatedArticles.cards/CardView.js',
			]
		];

		$modules['qunit']['ext.relatedArticles.readMore.gateway.tests'] = $boilerplate + [
			'scripts' => [
				'ext.relatedArticles.readMore.gateway/test_RelatedPagesGateway.js',
			],
			'dependencies' => [
				'ext.relatedArticles.readMore.gateway',
			],
		];
		return true;
	}
}
