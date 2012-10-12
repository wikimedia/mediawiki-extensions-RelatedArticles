<?php

if ( !defined( 'MEDIAWIKI' ) ) {
        die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( dirname(__FILE__) . "/../CustomData/CustomData.php" );

$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['RelatedArticles'] = $dir . 'RelatedArticles.i18n.php';

$wgExtensionFunctions[] = 'wfSetupRelatedArticles';
$wgExtensionCredits['parserhook']['RelatedArticles'] = array( 'name' => 'RelatedArticles', 'url' => 
'http://wikivoyage.org/tech/RelatedArticles-Extension', 'author' => 'Roland Unger/Hans Musil',
'descriptionmsg' => 'ra-desc' );

$wgHooks['LanguageGetMagic'][]       = 'wfRelatedArticlesParserFunction_Magic';

class RelatedArticles
{
	var $mRelArtSet = array();

	function RelatedArticles()
	{
		# wfDebug( "Call to RelatedArticles constructor\n");
		$this->mRelArtSet = array();
	}

	function onParserClearState( &$parser)
	{
		# wfDebug( "RelatedArticles::onParserClearState: " . $parser->mTitle->getPrefixedText() . "\n");
		# wfDebug( "RelatedArticles::onParserClearState\n");

		$this->mRelArtSet = array();

		return true;
	}

	# function onFuncRelated( &$parser, $relart)
	function onFuncRelated()
	{
		$args = func_get_args();
		array_shift( $args);
		# $parser = array_shift( $args);

		foreach( $args as $relart)
		{
			# wfDebug( "RelatedArticles::onFuncRelated: relart = $relart\n");

			$this->mRelArtSet[] = $relart;
		};

		return '';
	}

	#
	#	After parsing is done, store the $mRelArtSet in $wgCustomData.
	#
	function onParserBeforeTidy( &$parser, &$text)
	{
		global $wgCustomData;

		if( $this->mRelArtSet)
		{
		      $wgCustomData->setParserData( $parser->mOutput, 'RelatedArticles', $this->mRelArtSet);
		};

		return true;
	}

	#
	# Hooked in from hook SkinTemplateOutputPageBeforeExec.
	# Preprocess related articles links.
	#
	function onSkinTemplateOutputPageBeforeExec( &$SkTmpl, &$QuickTmpl)
	{
		global $wgCustomData, $wgOut;

		# wfDebug( "RelatedArticles::onSkinTemplateOutputPageBeforeExec\n");
		$RelatedArticles_urls = array();

	#
	# Fill the RelatedArticles array.
	#
		$ra = $wgCustomData->getPageData( $wgOut, 'RelatedArticles');
		foreach( $ra as $l)
		{
			// Tribute to Evan
			$l = urldecode( $l);
			$altText = '';
			if (preg_match('/\&\&/', $l)) {
				$parts = array_map( 'trim', explode('&&', $l, 2) );
				$l = $parts[0];
				$altText = $parts[1];
			}

			# wfDebug( "RelatedArticles::onSkinTemplateOutputPageBeforeExec: l = $l\n");

			$class = 'interwiki-relart';
			$nt = Title::newFromText( $l );
		      if( $nt)
		      {
				if ($altText == '') $altText = $nt->getPrefixedText();
				$RelatedArticles_urls[] = array(
					'href' => $nt->getLocalURL(),
					# 'href' => $nt->getFullURL(),
					'text' => $altText,
					# 'text' => $nt->getText(),
					'class' => $class
				);
			};

			# wfDebug( "l: $l\n");
		};
		$wgCustomData->setSkinData( $QuickTmpl, 'RelatedArticles', $RelatedArticles_urls);

		return true;
	}

	#
	# Write out HTML-code.
	#
	function onSkinTemplateToolboxEnd( &$skTemplate)
	{
		global $wgCustomData;

		# wfDebug( "RelatedArticles::onSkinTemplateToolboxEnd\n");

		$ra = $wgCustomData->getSkinData( $skTemplate, 'RelatedArticles');
		if( $ra ) 
		{ ?>
                        </ul>
                </div>
        </div>
        <div id="p-lang" class="portal">
                <h5><?php $skTemplate->msg('ra-RelatedArticles') ?></h5>
                <div class="body">
                        <ul>
<?php

			foreach( $ra as $ralink)
			{ ?>
					<li class="<?php echo htmlspecialchars($ralink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($ralink['href']) ?>"><?php echo $ralink['text'] ?></a></li>
<?php }

		};

	return true;
	}

};



function wfSetupRelatedArticles()
{
	global $wgParser, $wgHooks;

	global $wgRelatedArticles;
	$wgRelatedArticles     = new RelatedArticles;


	$wgParser->setFunctionHook( 'related', array( &$wgRelatedArticles, 'onFuncRelated' ));


	$wgHooks['SkinTemplateToolboxEnd'][] = 
					array( &$wgRelatedArticles, 'onSkinTemplateToolboxEnd' );
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 
					array( &$wgRelatedArticles, 'onSkinTemplateOutputPageBeforeExec' );
	$wgHooks['ParserClearState'][] = array( &$wgRelatedArticles, 'onParserClearState' );
	$wgHooks['ParserBeforeTidy'][] = array( &$wgRelatedArticles, 'onParserBeforeTidy' );

	return true;
}

function wfRelatedArticlesParserFunction_Magic( &$magicWords, $langCode )
{
	# wfDebug( "Call to wfRelatedArticlesParserFunction_Magic\n");

	$magicWords['related'] = array( 0, 'related' );

	return true;
}

?>
