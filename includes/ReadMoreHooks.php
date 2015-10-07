<?php

namespace RelatedArticles;

use OutputPage;
use Skin;

class ReadMoreHooks {

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
		$vars['wgRelatedArticles'] = $out->getProperty( 'RelatedArticles' );

		return true;
	}

	/**
	 * Handler for the <code>BeforePageDisplay</code> hook.
	 *
	 * Adds the <code>ext.relatedArticles.readMore.bootstrap</code> module
	 * to the output when:
	 *
	 * <ol>
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
		$title = $out->getContext()->getTitle();

		if (
			get_class( $skin ) === 'SkinMinervaBeta' &&
			$title->inNamespace( NS_MAIN ) &&
			!$title->isMainPage()
		) {

			$out->addModules( array( 'ext.relatedArticles.readMore.bootstrap' ) );
		}

		return true;
	}
}
