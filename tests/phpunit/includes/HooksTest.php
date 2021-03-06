<?php

namespace SMW\Test;

use SMWHooks;
use User;
use Title;
use WikiPage;
use ParserOutput;
use Parser;
use LinksUpdate;

/**
 * Tests for the SMWHooks class
 *
 * @since 1.9
 *
 * @file
 *
 * @license GNU GPL v2+
 * @author mwjames
 */

/**
 * @covers \SMWHooks
 *
 * This class is testing implemented hooks and verifies consistency with its
 * invoked methods to ensure a hook generally returns true.
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * The database group creates temporary tables which allows for testing without
 * accessing the production database.
 * @group Database
 */
class HooksTest extends \MediaWikiTestCase {

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function getTextDataProvider() {
		return array(
			array(
				"[[Lorem ipsum]] dolor sit amet, consetetur sadipscing elitr, sed diam " .
				" nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat."
			),
		);
	}

	/**
	 * Helper method that returns a normalized path
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	private function normalizePath( $path ) {
		return str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path );
	}

	/**
	 * Helper method that returns a random string
	 *
	 * @since 1.9
	 *
	 * @param $length
	 *
	 * @return string
	 */
	private function getRandomString( $length = 10 ) {
		return substr( str_shuffle( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ), 0, $length );
	}

	/**
	 * Helper method that returns a Title object
	 *
	 * @since 1.9
	 *
	 * @return Title
	 */
	private function getTitle( $text = '', $namespace = NS_MAIN ) {
		return Title::newFromText( $text !== '' ? $text : $this->getRandomString(), $namespace);
	}

	/**
	 * Helper method that creates a new wikipage
	 *
	 * @since 1.9
	 *
	 * @return WikiPage
	 */
	protected function newPage( Title $title = null ) {
		$wikiPage = new WikiPage( $title === null ? $this->getTitle() : $title );
		return $wikiPage;
	}

	/**
	 * Helper method that returns an User object
	 *
	 * @since 1.9
	 *
	 * @return User
	 */
	private function getUser() {
		return User::newFromName( $this->getRandomString() );
	}

	/**
	 * Helper method that creates a Title/ParserOutput object
	 * @see LinksUpdateTest::makeTitleAndParserOutput
	 *
	 * @since 1.9
	 *
	 * @param $titleName
	 * @param $id
	 *
	 * @return array
	 */
	private function makeTitleAndParserOutput() {
		$t = $this->getTitle();
		$t->resetArticleID( rand( 1, 1000 ) );

		$po = new ParserOutput();
		$po->setTitleText( $t->getPrefixedText() );

		return array( $t, $po );
	}

	/**
	 * Helper method that returns a Article mock object
	 *
	 * @since 1.9
	 *
	 * @param Title $title
	 *
	 * @return Article
	 */
	private function getArticleMock( Title $title ) {

		$article = $this->getMockBuilder( 'Article' )
			->disableOriginalConstructor()
			->getMock();

		$article->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		return $article;
	}

	/**
	 * Helper method that returns a Parser object
	 *
	 * @since 1.9
	 *
	 * @param $titleName
	 *
	 * @return Parser
	 */
	private function getParser() {
		global $wgContLang, $wgParserConf;

		$wikiPage = $this->newPage();
		$user = $this->getUser();
		$parserOptions = $wikiPage->makeParserOptions( $user );

		$parser = new Parser( $wgParserConf );
		$parser->setTitle( $wikiPage->getTitle() );
		$parser->setUser( $user );
		$parser->Options( $parserOptions );
		$parser->clearState();
		return $parser;
	}

	/**
	 * @test SMWHooks::onArticleFromTitle
	 *
	 * @since 1.9
	 */
	public function testOnArticleFromTitle() {
		$title = Title::newFromText( 'Property', SMW_NS_PROPERTY );
		$wikiPage = $this->newPage( $title );

		$result = SMWHooks::onArticleFromTitle( $title, $wikiPage );
		$this->assertTrue( $result );

		$title = Title::newFromText( 'Concepts', SMW_NS_CONCEPT );
		$wikiPage = $this->newPage( $title );

		$result = SMWHooks::onArticleFromTitle( $title, $wikiPage );
		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onParserFirstCallInit
	 *
	 * @since 1.9
	 */
	public function testOnParserFirstCallInit() {
		$parser = $this->getParser();
		$result = SMWHooks::onParserFirstCallInit( $parser );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onSpecialStatsAddExtra
	 *
	 * @since 1.9
	 */
	public function testOnSpecialStatsAddExtra() {
		$extraStats = array();
		$result = SMWHooks::onSpecialStatsAddExtra( $extraStats );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onParserAfterTidy
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $text
	 */
	public function testOnParserAfterTidy( $text ) {
		$parser = $this->getParser();
		$result = SMWHooks::onParserAfterTidy(
			$parser,
			$text
		);

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onLinksUpdateConstructed
	 *
	 * @since 1.9
	 */
	public function testOnLinksUpdateConstructed() {
		list( $title, $parserOutput ) = $this->makeTitleAndParserOutput();
		$update = new LinksUpdate( $title, $parserOutput );
		$result = SMWHooks::onLinksUpdateConstructed( $update );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onTitleIsAlwaysKnown
	 *
	 * @since 1.9
	 */
	public function testOnTitleIsAlwaysKnown() {
		$result = '';

		// Random Title
		$this->assertTrue( SMWHooks::onTitleIsAlwaysKnown(
			$this->getTitle( '', NS_MAIN ), $result ),
			'Failed asserting "true" for a random NS_MAIN title object'
		);
		$this->assertEmpty( $result, 'Failed asserting an empty result object' );

		// Random user-defined property
		$this->assertTrue( SMWHooks::onTitleIsAlwaysKnown(
			$this->getTitle( '', SMW_NS_PROPERTY ), $result ),
			'Failed asserting "true" for a random SMW_NS_PROPERTY title object'
		);
		$this->assertEmpty( $result, 'Failed asserting an empty result object' );

		// Predefined property
		$this->assertTrue( SMWHooks::onTitleIsAlwaysKnown(
			$this->getTitle( 'Modification date', SMW_NS_PROPERTY ), $result ),
			'Failed asserting "true" for a predefined property'
		);
		$this->assertTrue( $result, 'Failed asserting that the result object is returning true' );

	}

	/**
	 * @test SMWHooks::onBeforeDisplayNoArticleText
	 *
	 * @since 1.9
	 */
	public function testOnBeforeDisplayNoArticleText() {

		// Random Title
		$this->assertTrue( SMWHooks::onBeforeDisplayNoArticleText(
			$this->getArticleMock( $this->getTitle( '', NS_MAIN ) )
		), 'Failed asserting "true" for a random NS_MAIN title object' );

		// Random user-defined property
		$this->assertTrue( SMWHooks::onBeforeDisplayNoArticleText(
			$this->getArticleMock( $this->getTitle( '', SMW_NS_PROPERTY ) )
		), 'Failed asserting "true" for a random SMW_NS_PROPERTY title object' );

		// Predefined property
		$this->assertFalse( SMWHooks::onBeforeDisplayNoArticleText(
			$this->getArticleMock( $this->getTitle( 'Modification date', SMW_NS_PROPERTY ) )
		), 'Failed asserting "false" for a predefined property' );

	}

	/**
	 * @test SMWHooks::onArticleDelete
	 *
	 * @since 1.9
	 */
	public function testOnArticleDelete() {
		if ( method_exists( 'WikiPage', 'doEditContent' ) ) {

			$wikiPage = $this->newPage();
			$user = $this->getUser();
			$revision = $wikiPage->getRevision();
			$reason = '';
			$error = '';

			$result = SMWHooks::onArticleDelete(
				$wikiPage,
				$user,
				$reason,
				$error
			);

			$this->assertTrue( $result );
		} else {
			$this->markTestSkipped(
				'Skipped test due to missing method (probably MW 1.19 or lower).'
			);
		}
	}

	/**
	 * @test SMWHooks::onNewRevisionFromEditComplete
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $text
	 */
	public function testOnNewRevisionFromEditComplete( $text ) {
		if ( method_exists( 'WikiPage', 'doEditContent' ) ) {

			$wikiPage = $this->newPage();
			$user = $this->getUser();

			$content = \ContentHandler::makeContent(
				$text,
				$wikiPage->getTitle(),
				CONTENT_MODEL_WIKITEXT
			);

			$wikiPage->doEditContent( $content, "testing", EDIT_NEW );
			$this->assertTrue( $wikiPage->getId() > 0, "WikiPage should have new page id" );
			$revision = $wikiPage->getRevision();

			$result = SMWHooks::onNewRevisionFromEditComplete (
				$wikiPage,
				$revision,
				$wikiPage->getId(),
				$user
			);

			// Always make sure the clean-up
			if ( $wikiPage->exists() ) {
				$wikiPage->doDeleteArticle( "testing done." );
			}

			$this->assertTrue( $result );
		} else {
			$this->markTestSkipped(
				'Skipped test due to missing method (probably MW 1.19 or lower).'
			);
		}
	}

	/**
	 * @test SMWHooks::onSkinTemplateNavigation
	 *
	 * @since 1.9
	 */
	public function testOnSkinTemplateNavigation() {
		$skinTemplate = new \SkinTemplate();
		$skinTemplate->getContext()->setLanguage( \Language::factory( 'en' ) );
		$links = array();

		$result = SMWHooks::onSkinTemplateNavigation( $skinTemplate, $links );
		$this->assertTrue( $result );
	}


	/**
	 * @test SMWHooks::onResourceLoaderGetConfigVars
	 *
	 * @since 1.9
	 */
	public function testOnResourceLoaderGetConfigVars() {
		$vars = array();

		$result = SMWHooks::onResourceLoaderGetConfigVars( $vars );
		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::registerUnitTests
	 *
	 * Files are normally registered manually in registerUnitTests(). This test
	 * will compare registered files with the files available in the
	 * test directory.
	 *
	 * @since 1.9
	 */
	public function testRegisterUnitTests() {
		$registeredFiles = array();
		$result = SMWHooks::registerUnitTests( $registeredFiles );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $registeredFiles );

		// Get all the *.php files
		// @see http://php.net/manual/en/class.recursivedirectoryiterator.php
		$testFiles = new \RegexIterator(
			new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( __DIR__ ) ),
			'/^.+\.php$/i',
			\RecursiveRegexIterator::GET_MATCH
		);

		// Array contains files that are excluded from verification because
		// those files do not contain any executable tests and therefore are
		// not registered (such as abstract classes, mock classes etc.)
		$excludedFiles = array(
			'dataitems/DataItem',
			'printers/ResultPrinter'
		);

		// Normalize excluded files
		foreach ( $excludedFiles as &$registeredFile ) {
			$registeredFile = $this->normalizePath( __DIR__ . '/' . $registeredFile . 'Test.php' );
		}

		// Normalize registered files
		foreach ( $registeredFiles as &$registeredFile ) {
			$registeredFile = $this->normalizePath( $registeredFile );
		}

		// Compare directory files with registered files
		foreach ( $testFiles as $fileName => $object ){
			$fileName = $this->normalizePath( $fileName );

			if ( !in_array( $fileName, $excludedFiles ) ) {
				$this->assertContains(
					$fileName,
					$registeredFiles,
					'Missing registration for ' . $fileName
				);
			}
		}
	}

	/**
	 * @test SMWHooks::onInternalParseBeforeLinks
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $text
	 */
	public function testOnInternalParseBeforeLinks( $text ) {
		$parser = $this->getParser();
		$result = SMWHooks::onInternalParseBeforeLinks( $parser, $text );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onGetPreferences
	 *
	 * @since 1.9
	 */
	public function testOnGetPreferences() {
		$preferences = array();

		$result = SMWHooks::onGetPreferences( $this->getUser(), $preferences );
		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onArticlePurge
	 *
	 * @since 1.9
	 */
	public function testOnArticlePurge() {
		if ( method_exists( 'WikiPage', 'doEditContent' ) ) {

			$wikiPage = $this->newPage();

			$user = $this->getUser();

			$content = \ContentHandler::makeContent(
				'testing',
				$wikiPage->getTitle(),
				CONTENT_MODEL_WIKITEXT
			);
			$wikiPage->doEditContent( $content, "testing", EDIT_NEW, false, $user );

			$result = SMWHooks::onArticlePurge( $wikiPage );

			// Always make sure to clean-up
			if ( $wikiPage->exists() ) {
				$wikiPage->doDeleteArticle( "testing done." );
			}

			$this->assertTrue( $result );

		} else {
			$this->markTestSkipped(
				'Skipped test due to missing method (probably MW 1.19 or lower).'
			);
		}
	}

	/**
	 * @test SMWHooks::onTitleMoveComplete
	 *
	 * @since 1.9
	 */
	public function testOnTitleMoveComplete() {
		// For some mysterious reasons this test causes
		// SMW\Test\ApiAskTest::testExecute ... to fail with DBQueryError:
		// Query: SELECT  o_serialized AS v0  FROM unittest_unittest_smw_fpt_mdat
		// WHERE s_id='5'; it seems that the temp. unittest tables are
		// being deleted while this test runs
		$skip = true;

		if ( !$skip && method_exists( 'WikiPage', 'doEditContent' ) ) {
			$wikiPage = $this->newPage();
			$user = $this->getUser();

			$title = $wikiPage->getTitle();
			$newTitle = $this->getTitle();
			$pageid = $wikiPage->getId();

			$content = \ContentHandler::makeContent(
				'testing',
				$title,
				CONTENT_MODEL_WIKITEXT
			);
			$wikiPage->doEditContent( $content, "testing", EDIT_NEW, false, $user );

			$result = SMWHooks::onTitleMoveComplete( $title, $newTitle, $user, $pageid, $pageid );

			// Always make sure to clean-up
			if ( $wikiPage->exists() ) {
				$wikiPage->doDeleteArticle( "testing done." );
			}

			$this->assertTrue( $result );
		} else {
			$this->assertTrue( $skip );
		}
	}

	/**
	 * @test SMWHooks::onBeforePageDisplay
	 *
	 * @since 1.9
	 */
	public function testOnBeforePageDisplay() {
		$context = new \RequestContext();
		$context->setTitle( $this->getTitle() );
		$context->setLanguage( \Language::factory( 'en' ) );

		$skin = new \SkinTemplate();
		$skin->setContext( $context );

		$outputPage = new \OutputPage( $context );

		$result = SMWHooks::onBeforePageDisplay( $outputPage, $skin );
		$this->assertTrue( $result );
	}

}
