<?php

class RelatedArticles {
	public $mRelatedArticlesSet = array();

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function parserHooks( Parser &$parser ) {
		global $wgRelatedArticles;
		$parser->setFunctionHook( 'related', array( &$wgRelatedArticles, 'onFuncRelated' ) );
		return true;
	}

	/**
	 * @throws MWException
	 * @return CustomData
	 */
	public function getCustomData() {
		global $wgCustomData;

		if ( !$wgCustomData instanceof CustomData ) {
			throw new MWException( 'CustomData extension is not properly installed.' );
		}

		return $wgCustomData;
	}

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public function onParserClearState( Parser &$parser ) {
		$this->mRelatedArticlesSet = array();
		return true;
	}

	public function onFuncRelated() {
		$args = func_get_args();
		array_shift( $args );

		foreach ( $args as $relatedArticle ) {
			$this->mRelatedArticlesSet[] = $relatedArticle;
		}

		return '';
	}

	/**
	 * After parsing is done, store the $mRelatedArticlesSet in $wgCustomData.
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @return bool
	 */
	public function onParserBeforeTidy( Parser &$parser, &$text ) {
		if ( $this->mRelatedArticlesSet ) {
			$this->getCustomData()->setParserData( $parser->mOutput, 'RelatedArticles', $this->mRelatedArticlesSet );
		}

		return true;
	}

	/**
	 * Preprocess relatedarticles links.
	 *
	 * @param SkinTemplate $skinTpl
	 * @param QuickTemplate $QuickTmpl
	 * @return bool
	 */
	public function onSkinTemplateOutputPageBeforeExec( SkinTemplate &$skinTpl, &$QuickTmpl ) {
		global $wgOut;

		$customData = $this->getCustomData();

		// Fill the RelatedArticles array.
		$relatedArticles = $customData->getPageData( $wgOut, 'RelatedArticles' );
		$customData->setSkinData( $QuickTmpl, 'RelatedArticles', $relatedArticles );

		return true;
	}

	/**
	 * @param array $relatedArticles
	 * @return array
	 */
	protected function getRelatedArticlesUrls( array $relatedArticles ) {
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
	 * Write out HTML-code.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return bool
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {
		$out = $skin->getOutput();
		$relatedArticles = $this->getCustomData()->getParserData( $out, 'RelatedArticles' );

		if ( count( $relatedArticles ) == 0 ) {
			return true;
		}

		$relatedArticlesUrls = $this->getRelatedArticlesUrls( $relatedArticles );

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
	 * Write out HTML-code.
	 *
	 * @param SkinTemplate|VectorTemplate $skinTpl
	 * @return bool
	 */
	public function onSkinTemplateToolboxEnd( &$skinTpl ) {
		$relatedArticles = $this->getCustomData()->getSkinData( $skinTpl, 'RelatedArticles' );

		if ( count( $relatedArticles ) == 0 ) {
			return true;
		}

		$relatedArticlesUrls = $this->getRelatedArticlesUrls( $relatedArticles );

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
