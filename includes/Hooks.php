<?php

namespace RelatedArticles;

use Parser;
// FIXME: Remove in 30 days (T114915)
use CustomData;
use Exception;
use Title;
use SkinTemplate;
use BaseTemplate;
use Skin;
use Html;
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
		$relatedArticles = $parserOutput->getExtensionData( 'RelatedArticles' );
		if ( !$relatedArticles ) {
			$relatedArticles = array();
		}
		$args = func_get_args();
		array_shift( $args );

		// Add all the related articles passed by the parser function
		// {{#related:Test with read more|Foo|Bar}}
		foreach ( $args as $relatedArticle ) {
			$relatedArticles[] = $relatedArticle;
		}
		$parserOutput->setExtensionData( 'RelatedArticles', $relatedArticles );

		return '';
	}

	/**
	 * Handler for the <code>ParserClearState</code> hook.
	 *
	 * Empties the internal list so that related articles are not passed on to future
	 * ParserOutput's - note that {{#related:Foo}} appends and can be used multiple times
	 * in the page.
	 *
	 * @param Parser $parser
	 * @return boolean Always <code>true</code>
	 */
	public static function onParserClearState( Parser &$parser ) {
		$parser->getOutput()->setProperty( 'RelatedArticles', array() );

		return true;
	}


	/**
	* Gets the global instance of the {@see CustomData} class for backwards compatibility.
	*
	* FIXME: This can be removed when cache clears. (T114915)
	* If the instance isn't available, then an exception is thrown.
	*
	* @throws Exception When the CustomData extension isn't properly installed
	* @deprecated
	* @return CustomData
	*/
	public static function getCustomData() {
		global $wgCustomData;

		if ( !$wgCustomData instanceof CustomData ) {
			throw new Exception( 'CustomData extension isn\'t properly installed and is needed to view pages in cache.' );
		}

		return $wgCustomData;
	}

	/**
	 * Passes the related articles array from the cached parser output object to the output page for rendering
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 * @return boolean Always <code>true</code>
	 */
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parserOutput ) {
		$related = $parserOutput->getExtensionData( 'RelatedArticles' );
		// Backwards compatability with old cached pages. In cached pages, related articles will not be in
		// ParserOutput but will still be in custom data so let's retrieve them from there.
		// FIXME: Remove in 30 days (T114915)
		if ( !$related ) {
			$related = self::getCustomData()->getParserData( $out, 'RelatedArticles' );
		}

		if ( $related ) {
			$out->setProperty( 'RelatedArticles', $related );
		}

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
	 * Retrieves the list of related articles
	 * and adds its HTML representation to the sidebar.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return boolean Always <code>true</code>
	 */
	public static function onSkinBuildSidebar( Skin $skin, &$bar ) {
		$out = $skin->getOutput();
		$relatedArticles = $out->getProperty( 'RelatedArticles' );

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
		$relatedArticles = $skinTpl->getSkin()->getOutput()->getProperty( 'RelatedArticles' );

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
