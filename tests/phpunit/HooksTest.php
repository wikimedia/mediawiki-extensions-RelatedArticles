<?php

namespace Tests\RelatedArticles;

use PHPUnit_Framework_TestCase;
use Parser;
use ParserOutput;
use RelatedArticles\Hooks;

class HooksTest extends PHPUnit_Framework_TestCase {

	public function test_onParserClearState() {
		$parser = new Parser();
		$parserOutput = $parser->mOutput = new ParserOutput();
		$relatedArticles = array( 'Maybeshewill' );

		$parserOutput->setExtensionData( 'RelatedArticles', $relatedArticles );
		$parserOutput->setProperty( 'RelatedArticles', $relatedArticles );

		Hooks::onParserClearState( $parser );

		$this->assertEquals(
			array(),
			$parserOutput->getExtensionData( 'RelatedArticles' ),
			'It clears the list of related articles.'
		);

		$this->assertEquals(
			false,
			$parserOutput->getProperty( 'RelatedArticles' ),
			'[T115698] It unsets the list of related articles that were set as a property.'
		);
	}
}