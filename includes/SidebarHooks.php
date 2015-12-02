<?php

namespace RelatedArticles;

use ConfigFactory;
use Title;
use SkinTemplate;
use BaseTemplate;
use Skin;
use Html;
use User;

class SidebarHooks {

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

		if ( !self::isInSidebar( $relatedPages, $out->getUser() ) ) {
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

		if ( !self::isInSidebar( $relatedPages, $out->getUser() ) ) {
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
	 * Check whether there are related articles that can be displayed, or
	 * the ReadMore feature is disabled. The beta feature is used only
	 * for enabling ReadMore, so do not take it into account.
	 *
	 * @param mixed $relatedPages
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
