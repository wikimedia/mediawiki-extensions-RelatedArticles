<?php

namespace RelatedArticles;

use Parser;
use CustomData;
use Exception;
use Title;
use SkinTemplate;
use BaseTemplate;
use Skin;
use Html;

class Hooks {

	/**
	 * @var array The list of related articles
	 */
	private static $relatedArticles = array();

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
	 *
	 * @todo Test for uniqueness
	 *
	 * @return string Always <code>''</code>
	 */
	public static function onFuncRelated() {
		$args = func_get_args();
		array_shift( $args );

		foreach ( $args as $relatedArticle ) {
			self::$relatedArticles[] = $relatedArticle;
		}

		return '';
	}

	/**
	 * Handler for the <code>ParserClearState</code> hook.
	 *
	 * Empties the internal list.
	 *
	 * @param Parser $parser
	 * @return boolean Always <code>true</code>
	 */
	public static function onParserClearState( Parser &$parser ) {
		self::$relatedArticles = array();

		return true;
	}

	/**
	 * Gets the global instance of the {@see CustomData} class.
	 *
	 * If the instance isn't available, then an exception is thrown.
	 *
	 * @throws Exception When the CustomData extension isn't properly installed
	 * @return CustomData
	 */
	public static function getCustomData() {
		global $wgCustomData;

		if ( !$wgCustomData instanceof CustomData ) {
			throw new Exception( 'CustomData extension isn\'t properly installed.' );
		}

		return $wgCustomData;
	}

	/**
	 * Handler for the <code>ParserBeforeTidy</code> hook.
	 *
	 * Stores the internal list of articles as custom parser output data
	 * (see {@see CustomData::setParserData}) so that it can be retrieved
	 * even when using cached parser output.
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @return boolean Always <code>true</code>
	 */
	public static function onParserBeforeTidy( Parser &$parser, &$text ) {
		if ( self::$relatedArticles ) {
			self::getCustomData()->setParserData(
				$parser->mOutput,
				'RelatedArticles',
				self::$relatedArticles
			);
		}

		return true;
	}

	/**
	 * Handler for the <code>SkinTemplateOutputPageBeforeExec</code> hook.
	 *
	 * Set the list of articles from the custom parser output, if any, that
	 * has been merged into the page output, as a template variable using
	 * {@see CustomData::setSkinData}.
	 *
	 * This is done to facilitate the <code>SkinTemplateToolboxEnd</code>
	 * (see {@see Hooks::onSkinTemplateToolboxEnd}) hook handler as it
	 * isn't passed an instance of either the <code>OutputPage</code> or
	 * the <code>Skin</code> class.
	 *
	 * @param SkinTemplate $skin
	 * @param QuickTemplate $template
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinTemplateOutputPageBeforeExec(
		SkinTemplate &$skin,
		BaseTemplate &$template
	) {
		global $wgOut;

		$customData = self::getCustomData();
		$relatedArticles = $customData->getPageData( $wgOut, 'RelatedArticles' );
		$customData->setSkinData( $template, 'RelatedArticles', $relatedArticles );

		return true;
	}

	/**
	 * Generates anchor element attributes for each entry in list of articles.
	 *
	 * The attributes that are generated are: <code>href</code>,
	 * <code>text</code>, and <code>class</code>, with the latter always
	 * set to <code>"interwiki-relart"</code>.
	 *
	 * If the the article is of the form <code>"Foo && Bar"</code>, then
	 * the <code>text</code> attribute will be set to "Bar", otherwise the
	 * article's {@see Title::getPrefixedText prefixed text} will be used.
	 *
	 * @param array[string] $relatedArticles
	 * @return array An array of maps, each with <code>href</code>,
	 *  <code>text</code>, and <code>class</code> entries.
	 */
	private static function getRelatedArticlesUrls( array $relatedArticles ) {
		$relatedArticlesUrls = array();

		foreach ( $relatedArticles as $article ) {
			// Tribute to Evan
			$article = urldecode( $article );

			$altText = '';
			if ( preg_match( '/\&\&/', $article ) ) {
				$parts = array_map( 'trim', explode( '&&', $article, 2 ) );
				$article = $parts[0];
				$altText = $parts[1];
			}

			$title = Title::newFromText( $article );
			if ( $title ) {
				$relatedArticlesUrls[] = array(
					'href' => $title->getLocalURL(),
					'text' => $altText ?: $title->getPrefixedText(),
					'class' => 'interwiki-relart'
				);
			}
		};

		return $relatedArticlesUrls;
	}

	/**
	 * Handler for the <code>SkinBuildSidebar</code> hook.
	 *
	 * Retrieves the list of related articles from the cached parser output
	 * and adds its HTML representation to the sidebar.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinBuildSidebar( Skin $skin, &$bar ) {
		$out = $skin->getOutput();
		$relatedArticles = self::getCustomData()->getParserData( $out, 'RelatedArticles' );

		if ( !$relatedArticles ) {
			return true;
		}

		$relatedArticlesUrls = self::getRelatedArticlesUrls( $relatedArticles );

		// build relatedarticles <li>'s
		$relatedArticles = array();
		foreach ( (array) $relatedArticlesUrls as $url ) {
			$relatedArticles[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::element( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// build complete html
		$bar[$skin->msg( 'relatedarticles-title' )->text()] =
			Html::rawElement( 'ul', array(),
				implode( '', $relatedArticles )
			);

		return true;
	}

	/**
	 * Handler for the <code>SkinTemplateToolboxEnd</code> hook.
	 *
	 * Retrieves the list of related articles from the template and
	 * <code>echo</code>s its HTML representation to the sidebar.
	 *
	 * @param SkinTemplate $skinTpl
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinTemplateToolboxEnd( BaseTemplate &$skinTpl ) {
		$relatedArticles = self::getCustomData()->getSkinData( $skinTpl, 'RelatedArticles' );

		if ( !$relatedArticles ) {
			return true;
		}

		$relatedArticlesUrls = self::getRelatedArticlesUrls( $relatedArticles );

		// build relatedarticles <li>'s
		$relatedArticles = array();
		foreach ( (array) $relatedArticlesUrls as $url ) {
			$relatedArticles[] =
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
			implode( '', $relatedArticles );

		return true;
	}

}
