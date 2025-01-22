<?php

namespace RelatedArticles;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Disambiguator\Lookup;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SkinAfterContentHook;
use MediaWiki\Html\Html;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderGetConfigVarsHook;
use MediaWiki\Title\Title;
use Skin;

class Hooks implements
	ParserFirstCallInitHook,
	BeforePageDisplayHook,
	ResourceLoaderGetConfigVarsHook,
	SkinAfterContentHook
{

	private Config $relatedArticlesConfig;

	/** Either a Lookup from the Disambiguator extension, or null if that is not installed */
	private ?Lookup $disambiguatorLookup;

	public function __construct( ConfigFactory $configFactory, ?Lookup $disambiguatorLookup ) {
		$this->relatedArticlesConfig = $configFactory->makeConfig( 'RelatedArticles' );
		$this->disambiguatorLookup = $disambiguatorLookup;
	}

	/**
	 * Uses the Disambiguator extension to test whether the page is a disambiguation page.
	 *
	 * If the Disambiguator extension isn't installed, then the test always fails, i.e. the page is
	 * never a disambiguation page.
	 *
	 * @param Title $title
	 * @return bool
	 */
	private function isDisambiguationPage( Title $title ) {
		return $this->disambiguatorLookup &&
			$this->disambiguatorLookup->isDisambiguationPage( $title );
	}

	/**
	 * Check whether the output page is a diff page
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	private static function isDiffPage( IContextSource $context ) {
		$request = $context->getRequest();
		$type = $request->getRawVal( 'type' );
		$diff = $request->getCheck( 'diff' );
		$oldId = $request->getCheck( 'oldid' );

		return $type === 'revision' || $diff || $oldId;
	}

	/**
	 * Is ReadMore allowed on skin?
	 *
	 * Some wikis may want to only enable the feature on some skins, so we'll only
	 * show it if the allow list (`RelatedArticlesFooterAllowedSkins`
	 * configuration variable) is empty or the skin is listed.
	 *
	 * @param Skin $skin
	 * @return bool
	 */
	private function isReadMoreAllowedOnSkin( Skin $skin ) {
		$skins = $this->relatedArticlesConfig->get( 'RelatedArticlesFooterAllowedSkins' );
		$skinName = $skin->getSkinName();
		return !$skins || in_array( $skinName, $skins );
	}

	/**
	 * Can the page show related articles?
	 *
	 * @param Skin $skin
	 * @return bool
	 */
	private function hasRelatedArticles( Skin $skin ): bool {
		$title = $skin->getTitle();
		$action = $skin->getRequest()->getRawVal( 'action' ) ?? 'view';
		return $title->inNamespace( NS_MAIN ) &&
			// T120735
			$action === 'view' &&
			!$title->isMainPage() &&
			$title->exists() &&
			!self::isDiffPage( $skin ) &&
			!$this->isDisambiguationPage( $title ) &&
			$this->isReadMoreAllowedOnSkin( $skin );
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
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->hasRelatedArticles( $skin ) ) {
			$out->addModules( [ 'ext.relatedArticles.readMore.bootstrap' ] );
			$out->addModuleStyles( [ 'ext.relatedArticles.styles' ] );
		}
	}

	/**
	 * ResourceLoaderGetConfigVars hook handler for setting a config variable
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array &$vars Array of variables to be added into the output of the startup module.
	 * @param string $skin
	 * @param Config $config
	 */
	public function onResourceLoaderGetConfigVars( array &$vars, $skin, Config $config ): void {
		$limit = $this->relatedArticlesConfig->get( 'RelatedArticlesCardLimit' );
		$abTestConfig = $this->relatedArticlesConfig->get( 'RelatedArticlesABTestEnrollment' );

		$vars['wgRelatedArticlesCardLimit'] = $limit;
		$vars['wgRelatedArticlesABTestEnrollment'] = $abTestConfig;

		if ( $limit < 1 || $limit > 20 ) {
			throw new \RuntimeException(
				'The value of wgRelatedArticlesCardLimit is not valid. It should be between 1 and 20.'
			);
		}
	}

	/**
	 * Handler for the <code>ParserFirstCallInit</code> hook.
	 *
	 * Registers the <code>related</code> parser function (see
	 * {@see Hooks::onFuncRelated}).
	 *
	 * @param Parser $parser Parser object
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'related', [ self::class, 'onFuncRelated' ] );
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
	 * @param string ...$args
	 *
	 * @return string Always <code>''</code>
	 */
	public static function onFuncRelated( Parser $parser, ...$args ) {
		$parserOutput = $parser->getOutput();
		// Add all the related pages passed by the parser function
		// {{#related:Test with read more|Foo|Bar}}
		foreach ( $args as $relatedPage ) {
			$parserOutput->appendJsConfigVar( 'wgRelatedArticles', $relatedPage );
		}

		return '';
	}

	/**
	 * Create container for ReadMore cards so that they're correctly placed in all skins.
	 *
	 * @param string &$data
	 * @param Skin $skin
	 */
	public function onSkinAfterContent( &$data, $skin ) {
		if ( $this->hasRelatedArticles( $skin ) ) {
			$data .= Html::element( 'div', [ 'class' => 'read-more-container' ] );
		}
	}
}
