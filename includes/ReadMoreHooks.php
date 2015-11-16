<?php

namespace RelatedArticles;

use OutputPage;
use ResourceLoader;
use Skin;
use ConfigFactory;

class ReadMoreHooks {
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

	/**
	 * Handler for the <code>MakeGlobalVariablesScript</code> hook.
	 *
	 * Sets the value of the <code>wgRelatedArticles</code> global variable
	 * to the list of related articles in the cached parser output.
	 *
	 * @param array $vars
	 * @param OutputPage $out
	 * @return boolean Always <code>true</code>
	 */
	public static function onMakeGlobalVariablesScript( &$vars, OutputPage $out ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'RelatedArticles' );

		$vars['wgRelatedArticles'] = $out->getProperty( 'RelatedArticles' );

		$vars['wgRelatedArticlesUseCirrusSearch'] = $config->get( 'RelatedArticlesUseCirrusSearch' );
		$vars['wgRelatedArticlesOnlyUseCirrusSearch'] =
			$config->get( 'RelatedArticlesOnlyUseCirrusSearch' );

		return true;
	}

	/**
	 * Handler for the <code>BeforePageDisplay</code> hook.
	 *
	 * Adds the <code>ext.relatedArticles.readMore.bootstrap</code> module
	 * to the output when:
	 *
	 * <ol>
	 *   <li><code>$wgRelatedArticlesShowReadMore</code> is truthy</li>
	 *   <li>
	 *     The output is being rendered with any skin except the
	 *     <code>SkinMinerva<code> skin, i.e. the user is currently
	 *     not viewing the page on the mobile set in stable mode
	 *   </li>
	 *   <li>The page is in mainspace</li>
	 * </ol>
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean Always <code>true</code>
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'RelatedArticles' );
		$showReadMore = $config->get( 'RelatedArticlesShowReadMore' );

		$title = $out->getContext()->getTitle();

		if (
			$showReadMore &&
			get_class( $skin ) !== 'SkinMinerva' &&
			$title->inNamespace( NS_MAIN ) &&
			!$title->isMainPage()
		) {

			$out->addModules( array( 'ext.relatedArticles.readMore.bootstrap' ) );
		}

		return true;
	}

	/**
	 * EventLoggingRegisterSchemas hook handler.
	 *
	 * Registers our EventLogging schemas so that they can be converted to
	 * ResourceLoaderSchemaModules by the EventLogging extension.
	 *
	 * If the module has already been registered in
	 * onResourceLoaderRegisterModules, then it is overwritten.
	 *
	 * @param array $schemas The schemas currently registered with the EventLogging
	 *  extension
	 * @return bool Always true
	 */
	public static function onEventLoggingRegisterSchemas( &$schemas ) {
		// @see https://meta.wikimedia.org/wiki/Schema:RelatedArticles
		$schemas['RelatedArticles'] = 14496900;

		return true;
	}

	/**
	 * ResourceLoaderGetConfigVars hook handler for setting a config variable
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array $vars
	 * @return boolean
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'RelatedArticles' );
		$vars['wgRelatedArticlesLoggingSamplingRate'] =
			$config->get( 'RelatedArticlesLoggingSamplingRate' );

		return true;
	}

	/**
	 * Register the "ext.relatedArticles.readMore" module.
	 * Optionally update the dependencies and scripts if EventLogging is installed.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return boolean
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		$dependencies = array(
			"mediawiki.user",
			"mediawiki.util"
		);
		$scripts = array(
			"resources/ext.relatedArticles.readMore/index.js"
		);

		if ( class_exists( 'EventLogging' ) ) {
			$dependencies[] = "ext.eventLogging.Schema";
			$scripts[] = "resources/ext.relatedArticles.readMore/eventLogging.js";
		}

		$resourceLoader->register(
			"ext.relatedArticles.readMore",
			array(
				"dependencies" => $dependencies,
				"scripts" => $scripts,
				"styles" => array(
					"resources/ext.relatedArticles.readMore/readMore.less"
				),
				"messages" => array(
					"relatedarticles-read-more-heading"
				),
				"targets" => array(
					"mobile"
				),
				"localBasePath" => __DIR__ . "/..",
				"remoteExtPath" => "RelatedArticles"
			)
		);

		return true;
	}
}
