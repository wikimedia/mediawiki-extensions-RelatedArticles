<?php

namespace RelatedArticles;

use BetaFeatures;
use MediaWiki\MediaWikiServices;
use OutputPage;
use ResourceLoader;
use Skin;
use User;
use DisambiguatorHooks;
use Title;

class FooterHooks {

	/**
	 * Handler for the <code>MakeGlobalVariablesScript</code> hook.
	 *
	 * Sets the value of the <code>wgRelatedArticles</code> global variable
	 * to the list of related articles in the cached parser output.
	 *
	 * @param array &$vars variables to be added into the output of OutputPage::headElement.
	 * @param OutputPage $out OutputPage instance calling the hook
	 * @return bool Always <code>true</code>
	 */
	public static function onMakeGlobalVariablesScript( &$vars, OutputPage $out ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'RelatedArticles' );

		$vars['wgRelatedArticles'] = $out->getProperty( 'RelatedArticles' );
		$vars['wgRelatedArticlesUseCirrusSearch'] = $config->get( 'RelatedArticlesUseCirrusSearch' );
		$vars['wgRelatedArticlesOnlyUseCirrusSearch'] =
			$config->get( 'RelatedArticlesOnlyUseCirrusSearch' );

		return true;
	}

	/**
	 * Uses the Disambiguator extension to test whether the page is a disambiguation page.
	 *
	 * If the Disambiguator extension isn't installed, then the test always fails, i.e. the page is
	 * never a disambiguation page.
	 *
	 * @return boolean
	 */
	private static function isDisambiguationPage( Title $title ) {
		return \ExtensionRegistry::getInstance()->isLoaded( 'Disambiguator' ) &&
			DisambiguatorHooks::isDisambiguationPage( $title );
	}

	/**
	 * Check whether the output page is a diff page
	 *
	 * @param OutputPage $out
	 * @return bool
	 */
	private static function isDiffPage( OutputPage $out ) {
		$request = $out->getRequest();
		$type = $request->getText( 'type' );
		$diff = $request->getText( 'diff' );
		$oldId = $request->getText( 'oldid' );
		$isSpecialMobileDiff = $out->getTitle()->isSpecial( 'MobileDiff' );

		return $type === 'revision' || $diff || $oldId || $isSpecialMobileDiff;
	}

	/**
	 * Is ReadMore allowed on skin?
	 *
	 * The feature is allowed on all skins as long as they are whitelisted
	 * in the configuration variable `RelatedArticlesFooterWhitelistedSkins`.
	 *
	 * @param User $user
	 * @param Skin $skin
	 * @return bool
	 */
	private static function isReadMoreAllowedOnSkin( User $user, Skin $skin ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'RelatedArticles' );
		$skins = $config->get( 'RelatedArticlesFooterWhitelistedSkins' );
		$skinName = $skin->getSkinName();
		return in_array( $skinName, $skins );
	}

	/**
	 * Handler for the <code>BeforePageDisplay</code> hook.
	 *
	 * Adds the <code>ext.relatedArticles.readMore.bootstrap</code> module
	 * to the output when:
	 *
	 * <ol>
	 *   <li>On mobile, the output is being rendered with
	 *     <code>SkinMinervaBeta<code></li>
	 *   <li>The page is in mainspace</li>
	 *   <li>The action is 'view'</li>
	 *   <li>The page is not the Main Page</li>
	 *   <li>The page is not a disambiguation page</li>
	 *   <li>The page is not a diff page</li>
	 *   <li>The feature is allowed on the skin (see isReadMoreAllowedOnSkin() above)</li>
	 * </ol>
	 *
	 * @param OutputPage $out The OutputPage object
	 * @param Skin $skin Skin object that will be used to generate the page
	 * @return bool Always <code>true</code>
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$title = $out->getContext()->getTitle();
		$action = $out->getRequest()->getText( 'action', 'view' );

		if (
			$title->inNamespace( NS_MAIN ) &&
			// T120735
			$action === 'view' &&
			!$title->isMainPage() &&
			!self::isDisambiguationPage( $title ) &&
			!self::isDiffPage( $out ) &&
			self::isReadMoreAllowedOnSkin( $out->getUser(), $skin )
		) {
			$out->addModules( [ 'ext.relatedArticles.readMore.bootstrap' ] );
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
	 * @param array &$schemas The schemas currently registered with the EventLogging
	 *  extension
	 * @return bool Always true
	 */
	public static function onEventLoggingRegisterSchemas( &$schemas ) {
		// @see https://meta.wikimedia.org/wiki/Schema:RelatedArticles
		$schemas['RelatedArticles'] = 16352530;

		return true;
	}

	/**
	 * ResourceLoaderGetConfigVars hook handler for setting a config variable
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array &$vars Array of variables to be added into the output of the startup module.
	 * @return bool
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'RelatedArticles' );
		$vars['wgRelatedArticlesLoggingBucketSize'] =
			$config->get( 'RelatedArticlesLoggingBucketSize' );
		$vars['wgRelatedArticlesEnabledBucketSize']
			= $config->get( 'RelatedArticlesEnabledBucketSize' );

		$limit = $config->get( 'RelatedArticlesCardLimit' );
		$vars['wgRelatedArticlesCardLimit'] = $limit;
		if ( $limit < 1 || $limit > 20 ) {
			throw new \RuntimeException(
				'The value of wgRelatedArticlesCardLimit is not valid. It should be between 1 and 20.'
			);
		}
		return true;
	}

	/**
	 * Register the "ext.relatedArticles.readMore.eventLogging" module.
	 * Optionally update the dependencies and scripts if EventLogging is installed.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return bool
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		$dependencies = [];
		$scripts = [];

		if ( class_exists( 'EventLogging' ) ) {
			$dependencies[] = "mediawiki.user";
			$dependencies[] = "mediawiki.viewport";
			$dependencies[] = "ext.eventLogging.Schema";
			$scripts[] = "resources/ext.relatedArticles.readMore.eventLogging/index.js";
		}

		$resourceLoader->register(
			"ext.relatedArticles.readMore.eventLogging",
			[
				"dependencies" => $dependencies,
				"scripts" => $scripts,
				"targets" => [
					"desktop",
					"mobile"
				],
				"localBasePath" => __DIR__ . "/..",
				"remoteExtPath" => "RelatedArticles"
			]
		);

		return true;
	}
}
