<?php

namespace RelatedArticles;

use OutputPage;
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

		$modules['qunit']['ext.relatedArticles.readMore.tests'] = $boilerplate + array(
			'scripts' => array(
				'ext.relatedArticles.readMore/test_RelatedPagesGateway.js',
			),
			'dependencies' => array(
				'ext.relatedArticles.readMore',
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
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'relatedarticles' );

		$vars['wgRelatedArticles'] = $out->getProperty( 'RelatedArticles' );
		$vars['wgRelatedArticlesUseCirrusSearch'] = $config->get( 'RelatedArticlesUseCirrusSearch' );

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
	 *     The output is being rendered with the
	 *     <code>SkinMinervaBeta<code> skin, i.e. the user is currently
	 *     viewing the page on the mobile set in beta mode
	 *   </li>
	 *   <li>The page is in mainspace</li>
	 * </ol>
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean Always <code>true</code>
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'relatedarticles' );
		$showReadMore = $config->get( 'RelatedArticlesShowReadMore' );

		$title = $out->getContext()->getTitle();

		if (
			$showReadMore &&
			get_class( $skin ) === 'SkinMinervaBeta' &&
			$title->inNamespace( NS_MAIN ) &&
			!$title->isMainPage()
		) {

			$out->addModules( array( 'ext.relatedArticles.readMore.bootstrap' ) );
		}

		return true;
	}
}
