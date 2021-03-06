<?php

namespace SMW\Test;

use SMW\WantedPropertiesQueryPage;
use SMWDataItem;

/**
 * Tests for the WantedPropertiesQueryPage class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\WantedPropertiesQueryPage
 * @covers \SMW\QueryPage
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class WantedPropertiesQueryPageTest extends SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\WantedPropertiesQueryPage';
	}

	/**
	 * Helper method that returns a DIWikiPage object
	 *
	 * @since 1.9
	 *
	 * @return DIWikiPage
	 */
	private function getMockDIWikiPage( $exists = true ) {

		$text  = $this->newRandomString();

		$title = $this->newMockObject( array(
			'exists'  => $exists,
			'getText' => $text,
			'getNamespace'    => NS_MAIN,
			'getPrefixedText' => $text
		) )->getMockTitle();

		$diWikiPage = $this->newMockObject( array(
			'getTitle'  => $title,
		) )->getMockDIWikiPage();

		return $diWikiPage;
	}

	/**
	 * Helper method that returns a WantedPropertiesQueryPage object
	 *
	 * @since 1.9
	 *
	 * @param $result
	 *
	 * @return WantedPropertiesQueryPage
	 */
	private function newInstance( $result = null ) {

		$collector = $this->newMockObject( array(
			'getResults' => $result
		) )->getMockCacheableObjectCollector();

		$mockStore = $this->newMockObject( array(
			'getPropertyValues'          => array(),
			'getWantedPropertiesSpecial' => $collector
		) )->getMockStore();

		$instance = new WantedPropertiesQueryPage( $mockStore, $this->newSettings() );
		$instance->setContext( $this->newContext() );

		return $instance;
	}

	/**
	 * @test WantedPropertiesQueryPage::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$this->assertInstanceOf( $this->getClass(), $this->newInstance() );
	}

	/**
	 * @test WantedPropertiesQueryPage::formatResult
	 * @dataProvider getUserDefinedDataProvider
	 *
	 * @since 1.9
	 */
	public function testFormatResult( $isUserDefined ) {

		$instance = $this->newInstance();
		$skin     = $this->getMock( 'Skin' );

		$count    = rand();
		$property = $this->newMockObject( array(
			'isUserDefined' => $isUserDefined,
			'getDiWikiPage' => $this->getMockDIWikiPage( true ),
			'getLabel'      => $this->newRandomString(),
		) )->getMockDIProperty();

		$expected = $isUserDefined ? (string)$count : '';
		$result   = $instance->formatResult( $skin, array( $property, $count ) );

		$this->assertInternalType( 'string', $result );
		$isUserDefined ? $this->assertContains( $expected, $result ) : $this->assertEmpty( $result );

	}

	/**
	 * isUserDefined switcher
	 *
	 * @return array
	 */
	public function getUserDefinedDataProvider() {
		return array( array( true ), array( false ) );
	}

	/**
	 * @test WantedPropertiesQueryPage::getResults
	 *
	 * @since 1.9
	 */
	public function testGetResults() {

		$expected = 'Lala';
		$instance = $this->newInstance( $expected );

		$this->assertEquals( $expected, $instance->getResults( null ) );

	}
}
