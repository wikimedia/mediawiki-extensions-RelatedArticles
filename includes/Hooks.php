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
	 * @param Parser $parser
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
	 * @param Parser $parser
	 *
	 * @return string Always <code>''</code>
	 */
	public static function onFuncRelated( Parser $parser ) {
		$parserOutput = $parser->getOutput();
		$relatedPages = $parserOutput->getExtensionData( 'RelatedArticles' );
		if ( !$relatedPages ) {
			$relatedPages = array();
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
	 * Handler for the <code>ParserClearState</code> hook.
	 *
	 * Empties the internal list so that related pages are not passed on to future
	 * ParserOutput's - note that {{#related:Foo}} appends and can be used multiple times
	 * in the page.
	 *
	 * @param Parser $parser
	 * @return boolean Always <code>true</code>
	 */
	public static function onParserClearState( Parser &$parser ) {
		$parserOutput = $parser->getOutput();

		$parserOutput->setExtensionData( 'RelatedArticles', array() );

		// FIXME: Remove in 30 days (T115698)
		$parserOutput->unsetProperty( 'RelatedArticles' );

		return true;
	}

	/**
	 * Passes the related pages list from the cached parser output
	 * object to the output page for rendering.
	 *
	 * The list of related pages will be retrieved using
	 * <code>ParserOutput#getExtensionData</code>.
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
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
	 * Handler for the <code>UnitTestsList</code> hook.
	 *
	 * Adds the path to this extension's PHPUnit test suite to the set of
	 * paths.
	 *
	 * @param array $paths
	 * @return boolean Always <code>true</code>
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/../tests/phpunit';

		return true;
	}

	/**
	 * Register QUnit tests.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array $modules
	 * @param ResourceLoader $rl
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( &$modules, &$rl ) {
		$boilerplate = array(
			'localBasePath' => __DIR__ . '/../tests/qunit/',
			'remoteExtPath' => 'RelatedArticles/tests/qunit',
			'targets' => array( 'desktop', 'mobile' ),
		);

		$modules['qunit']['ext.relatedArticles.readMore.gateway.tests'] = $boilerplate + array(
			'scripts' => array(
				'ext.relatedArticles.readMore.gateway/test_RelatedPagesGateway.js',
			),
			'dependencies' => array(
				'ext.relatedArticles.readMore.gateway',
			),
		);
		return true;
	}
}
