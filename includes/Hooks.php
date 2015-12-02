<?php

namespace RelatedArticles;

use ConfigFactory;
use Parser;
use Title;
use SkinTemplate;
use BaseTemplate;
use Skin;
use Html;
use OutputPage;
use ParserOutput;
use User;

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
	 * Generates anchor element attributes for each entry in list of pages.
	 *
	 * The attributes that are generated are: <code>href</code>,
	 * <code>text</code>, and <code>class</code>, with the latter always
	 * set to <code>"interwiki-relart"</code>.
	 *
	 * If the the page is of the form <code>"Foo && Bar"</code>, then
	 * the <code>text</code> attribute will be set to "Bar", otherwise the
	 * page's {@see Title::getPrefixedText prefixed text} will be used.
	 *
	 * @param array[string] $relatedPages
	 * @return array An array of maps, each with <code>href</code>,
	 *  <code>text</code>, and <code>class</code> entries.
	 */
	private static function getRelatedPagesUrls( array $relatedPages ) {
		$relatedPagesUrls = array();

		foreach ( $relatedPages as $page ) {
			// Tribute to Evan
			$page = urldecode( $page );

			$altText = '';
			if ( preg_match( '/\&\&/', $page ) ) {
				$parts = array_map( 'trim', explode( '&&', $page, 2 ) );
				$page = $parts[0];
				$altText = $parts[1];
			}

			$title = Title::newFromText( $page );
			if ( $title ) {
				$relatedPagesUrls[] = array(
					'href' => $title->getLocalURL(),
					'text' => $altText ?: $title->getPrefixedText(),
					'class' => 'interwiki-relart'
				);
			}
		};

		return $relatedPagesUrls;
	}

	/**
	 * Handler for the <code>SkinBuildSidebar</code> hook.
	 *
	 * Retrieves the list of related pages
	 * and adds its HTML representation to the sidebar if the ReadMore feature
	 * is disabled and the beta feature is enabled by the user.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinBuildSidebar( Skin $skin, &$bar ) {
		$out = $skin->getOutput();
		$relatedPages = $out->getProperty( 'RelatedArticles' );

		if ( !Hooks::isInSidebar( $relatedPages, $out->getUser() ) ) {
			return true;
		}

		$relatedPagesUrls = self::getRelatedPagesUrls( $relatedPages );

		// build relatedarticles <li>'s
		$relatedPages = array();
		foreach ( (array) $relatedPagesUrls as $url ) {
			$relatedPages[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::element( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// build complete html
		$bar[$skin->msg( 'relatedarticles-title' )->text()] =
			Html::rawElement( 'ul', array(),
				implode( '', $relatedPages )
			);

		return true;
	}

	/**
	 * Handler for the <code>SkinTemplateToolboxEnd</code> hook.
	 *
	 * Retrieves the list of related pages from the template and
	 * <code>echo</code>s its HTML representation to the sidebar if the
	 * ReadMore feature is disabled and the beta feature is enabled by the user.
	 *
	 * @param SkinTemplate $skinTpl
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinTemplateToolboxEnd( BaseTemplate &$skinTpl ) {
		$out = $skinTpl->getSkin()->getOutput();
		$relatedPages = $out->getProperty( 'RelatedArticles' );

		if ( !Hooks::isInSidebar( $relatedPages, $out->getUser() ) ) {
			return true;
		}

		$relatedPagesUrls = self::getRelatedPagesUrls( $relatedPages );

		// build relatedarticles <li>'s
		$relatedPages = array();
		foreach ( (array) $relatedPagesUrls as $url ) {
			$relatedPages[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::element( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// build complete html
		echo
			Html::closeElement( 'ul' ) .
			Html::closeElement( 'div' ) .
			Html::closeElement( 'div' ) .
			Html::openElement( 'div', array(
				'class' => 'portal',
				'role' => 'navigation',
				'id' => 'p-relatedarticles',
			) ) .
			Html::element( 'h3', array(), wfMessage( 'relatedarticles-title' )->text() ) .
			Html::openElement( 'div', array( 'class' => 'body' ) ) .
			Html::openElement( 'ul' ) .
			implode( '', $relatedPages );

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
	 * Check whether there are related articles that can be displayed, or
	 * the ReadMore feature is disabled. The beta feature is used only
	 * for enabling ReadMore, so do not take it into account.
	 *
	 * @param mixed|null $relatedPages
	 * @param User $user
	 * @return bool
	 * @throws \ConfigException
	 */
	private static function isInSidebar( $relatedPages, User $user ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'RelatedArticles' );

		if ( !$relatedPages || $config->get( 'RelatedArticlesShowReadMore' ) ) {
			return false;
		}

		return true;
	}
}
