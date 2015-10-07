<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// autoloader
$wgAutoloadClasses['RelatedArticles'] = __DIR__ . '/RelatedArticles.class.php';

// extension & magic words i18n
$wgMessagesDirs['RelatedArticles'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['RelatedArticles'] = __DIR__ . '/RelatedArticles.i18n.php';
$wgExtensionMessagesFiles['RelatedArticlesMagic'] = __DIR__ . '/RelatedArticles.i18n.magic.php';

// hooks
$wgRelatedArticles = new RelatedArticles;
$wgHooks['ParserFirstCallInit'][] = 'RelatedArticles::parserHooks';
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = array(
	&$wgRelatedArticles,
	'onSkinTemplateOutputPageBeforeExec'
);
$wgHooks['ParserClearState'][] = array( &$wgRelatedArticles, 'onParserClearState' );
$wgHooks['ParserBeforeTidy'][] = array( &$wgRelatedArticles, 'onParserBeforeTidy' );

// @TODO Add a global to control these, and then probably use wgExtensionFunctions hook
// 2 same hooks, with different position though - enable what you want
// the first one is a "clean" solution, but has its content inserted _before_ the toolbox
// $wgHooks['SkinBuildSidebar'][] = array( &$wgRelatedArticles, 'onSkinBuildSidebar' );
// the second one is nasty: echo'ing raw html _after_ the regular toolbox
$wgHooks['SkinTemplateToolboxEnd'][] = array( &$wgRelatedArticles, 'onSkinTemplateToolboxEnd' );

// credits
$wgExtensionCredits['parserhook']['RelatedArticles'] = array(
	'path' => __FILE__,
	'name' => 'RelatedArticles',
	'url' => '//www.mediawiki.org/wiki/Extension:RelatedArticles',
	'descriptionmsg' => 'relatedarticles-desc',
	'author' => array( 'Roland Unger', 'Hans Musil', 'Matthias Mullie' ),
	'version' => '1.01'
);
