<?php

namespace SMW\Test;

use SMW\ShowParserFunction;
use SMW\MessageFormatter;
use SMW\QueryData;

use Title;
use ParserOutput;
use ReflectionClass;

/**
 * Tests for the ShowParserFunction class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\ShowParserFunction
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class ShowParserFunctionTest extends ParserTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SMW\ShowParserFunction';
	}

	/**
	 * Helper method that returns a ShowParserFunction object
	 *
	 * @since 1.9
	 *
	 * @param Title $title
	 * @param ParserOutput $parserOutput
	 *
	 * @return ShowParserFunction
	 */
	private function getInstance( Title $title, ParserOutput $parserOutput = null ) {

		$settings = $this->newSettings();

		return new ShowParserFunction(
			$this->getParserData( $title, $parserOutput ),
			new QueryData( $title ),
			new MessageFormatter( $title->getPageLanguage() ),
			$settings
		 );
	}

	/**
	 * @test ShowParserFunction::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$instance = $this->getInstance( $this->newTitle(), $this->newParserOutput() );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test ShowParserFunction::__construct (Test instance exception)
	 *
	 * @since 1.9
	 */
	public function testConstructorException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$instance =  $this->getInstance( $this->getTitle() );
	}

	/**
	 * @test ShowParserFunction::parse
	 * @dataProvider getDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testParse( array $params, array $expected ) {

		$instance = $this->getInstance( $this->newTitle(), $this->newParserOutput() );
		$result   = $instance->parse( $params, true );

		if (  $expected['output'] === '' ) {
			$this->assertEmpty( $result );
		} else {
			$this->assertContains( $expected['output'], $result );
		}

	}

	/**
	 * @test ShowParserFunction::parse (Test $GLOBALS['smwgQEnabled'] = false)
	 * @dataProvider getDataProvider
	 *
	 * @since 1.9
	 */
	public function testParseDisabledsmwgQEnabled() {

		$title    = $this->newTitle();
		$message  = new MessageFormatter( $title->getPageLanguage() );
		$expected = $message->addFromKey( 'smw_iq_disabled' )->getHtml();

		$instance = $this->getInstance( $title, $this->getParserOutput() );

		// Make protected method accessible
		$reflection = new ReflectionClass( $this->getClass() );
		$method = $reflection->getMethod( 'disabled' );
		$method->setAccessible( true );

		$result = $method->invoke( $instance );
		$this->assertEquals( $expected , $result );
	}

	/**
	 * @test ShowParserFunction::parse (Test generated query data)
	 * @dataProvider getDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testInstantiatedQueryData( array $params, array $expected ) {

		$parserOutput = $this->newParserOutput();
		$title        = $this->newTitle();

		// Initialize and parse
		$instance = $this->getInstance( $title, $parserOutput );
		$instance->parse( $params );

		// Get semantic data from the ParserOutput
		$parserData = $this->getParserData( $title, $parserOutput );

		// Check the returned instance
		$this->assertInstanceOf( '\SMW\SemanticData', $parserData->getData() );

		// Confirm subSemanticData objects for the SemanticData instance
		foreach ( $parserData->getData()->getSubSemanticData() as $containerSemanticData ){
			$this->assertInstanceOf( 'SMWContainerSemanticData', $containerSemanticData );
			$this->assertSemanticData( $containerSemanticData, $expected );
		}

	}

	/**
	 * @test ShowParserFunction::render
	 *
	 * @since 1.9
	 */
	public function testStaticRender() {
		$parser = $this->getParser( $this->newTitle(), $this->getUser() );
		$result = ShowParserFunction::render( $parser );
		$this->assertInternalType( 'string', $result );
	}

	/**
	 * Provides data sample normally found in connection with the {{#show}}
	 * parser function. The first array contains parametrized input value while
	 * the second array contains expected return results for the instantiated
	 * object.
	 *
	 * @return array
	 */
	public function getDataProvider() {

		$provider = array();

		// #0
		// {{#show: Foo
		// |?Modification date
		// }}
		$provider[] = array(
			array(
				'Foo',
				'?Modification date',
			),
			array(
				'output' => '',
				'propertyCount' => 4,
				'propertyKey' => array( '_ASKFO', '_ASKDE', '_ASKSI', '_ASKST' ),
				'propertyValue' => array( 'list', 0, 1, '[[:Foo]]' )
			)
		);

		// #1
		// {{#show: Help:Bar
		// |?Modification date
		// |default=no results
		// }}
		$provider[] = array(
			array(
				'Help:Bar',
				'?Modification date',
				'default=no results'
			),
			array(
				'output' => 'no results',
				'propertyCount' => 4,
				'propertyKey' => array( '_ASKFO', '_ASKDE', '_ASKSI', '_ASKST' ),
				'propertyValue' => array( 'list', 0, 1, '[[:Help:Bar]]' )
			)
		);

		// #2 [[..]] is not acknowledged therefore displays an error message
		// {{#show: [[File:Fooo]]
		// |?Modification date
		// |default=no results
		// |format=table
		// }}
		$provider[] = array(
			array(
				'[[File:Fooo]]',
				'?Modification date',
				'default=no results',
				'format=table'
			),
			array(
				'output' => 'class="smwtticon warning"', // lazy content check for the error
				'propertyCount' => 4,
				'propertyKey' => array( '_ASKFO', '_ASKDE', '_ASKSI', '_ASKST' ),
				'propertyValue' => array( 'table', 0, 1, '[[:]]' )
			)
		);

		return $provider;
	}
}
