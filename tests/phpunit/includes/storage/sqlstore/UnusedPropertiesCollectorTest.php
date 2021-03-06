<?php

namespace SMW\Test\SQLStore;

use SMW\SQLStore\UnusedPropertiesCollector;

use SMW\MessageFormatter;
use SMW\StoreFactory;
use SMW\DIProperty;
use SMW\Settings;

use SMWRequestOptions;

use FakeResultWrapper;

/**
 * Test for the UnusedPropertiesCollector class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\SQLStore\UnusedPropertiesCollector
 * @covers \SMW\InvalidPropertyException
 *
 * @ingroup SQLStoreTest
 *
 * @group SMW
 * @group SMWExtension
 */
class UnusedPropertiesCollectorTest extends \SMW\Test\SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\SQLStore\UnusedPropertiesCollector';
	}

	/**
	 * Helper method that returns a Database object
	 *
	 * @since 1.9
	 *
	 * @param $smwTitle
	 *
	 * @return Database
	 */
	private function getMockDBConnection( $smwTitle = 'Foo' ) {

		$result = array(
			'smw_title' => $smwTitle,
		);

		// Database stub object to make the test independent from any real DB
		$connection = $this->getMock( 'DatabaseMysql' );

		// Override method with expected return objects
		$connection->expects( $this->any() )
			->method( 'select' )
			->will( $this->returnValue( new FakeResultWrapper( array( (object)$result ) ) ) );

		return $connection;
	}

	/**
	 * Helper method that returns a UnusedPropertiesCollector object
	 *
	 * @since 1.9
	 *
	 * @param $smwTitle
	 * @param $cacheEnabled
	 *
	 * @return UnusedPropertiesCollector
	 */
	private function getInstance( $smwTitle = 'Foo', $cacheEnabled = false ) {

		$store = StoreFactory::getStore();
		$connection = $this->getMockDBConnection( $smwTitle );

		// Settings to be used
		$settings = Settings::newFromArray( array(
			'smwgCacheType' => 'hash',
			'smwgUnusedPropertiesCache' => $cacheEnabled,
			'smwgUnusedPropertiesCacheExpiry' => 360,
		) );

		return new UnusedPropertiesCollector( $store, $connection, $settings );
	}

	/**
	 * @test UnusedPropertiesCollector::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$instance = $this->getInstance();
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test UnusedPropertiesCollector::newFromStore
	 *
	 * @since 1.9
	 */
	public function testNewFromStore() {
		$instance = UnusedPropertiesCollector::newFromStore( StoreFactory::getStore() );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test UnusedPropertiesCollector::getResults
	 * @test UnusedPropertiesCollector::getCount
	 *
	 * @since 1.9
	 */
	public function testGetResults() {

		$property = $this->getRandomString();
		$expected = array( new DIProperty( $property ) );

		$instance = $this->getInstance( $property );
		$requestOptions = new SMWRequestOptions( $property, SMWRequestOptions::STRCOND_PRE );
		$requestOptions->limit = 1;

		$instance->setRequestOptions( $requestOptions );

		$this->assertEquals( $expected, $instance->getResults() );
		$this->assertEquals( 1, $instance->getCount() );

	}

	/**
	 * @test UnusedPropertiesCollector::getResults
	 * @dataProvider exceptionDataProvider
	 *
	 * InvalidPropertyException is thrown but caught and returning with a
	 * SMWDIError instead
	 *
	 * @since 1.9
	 *
	 * @param $property
	 */
	public function testInvalidPropertyException( $property ) {

		$instance = $this->getInstance( $property );
		$results  = $instance->getResults();

		$this->assertInternalType( 'array', $results );
		$this->assertEquals( 1, $instance->getCount() );
		$this->assertInstanceOf( 'SMWDIError', $results[0] );
		$this->assertContains(
			$property,
			MessageFormatter::newFromArray( $this->getLanguage(), array( $results[0]->getErrors() ) )->getHtml()
		);

	}

	/**
	 * @test UnusedPropertiesCollector::getResults
	 * @test UnusedPropertiesCollector::isCached
	 * @dataProvider getCacheNonCacheDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $test
	 * @param $expected
	 * @param $info
	 */
	public function testCacheNoCache( array $test, array $expected, array $info ) {

		// Sample A
		$instance = $this->getInstance(
			$test['A']['property'],
			$test['cacheEnabled']
		);

		$this->assertEquals( $expected['A'], $instance->getResults(), $info['msg'] );

		// Sample B
		$instance = $this->getInstance(
			$test['B']['property'],
			$test['cacheEnabled']
		);

		$this->assertEquals( $expected['B'], $instance->getResults(), $info['msg'] );
		$this->assertEquals( $test['cacheEnabled'], $instance->isCached() );
	}

	/**
	 * Exception data sample
	 *
	 * @return array
	 */
	public function exceptionDataProvider() {
		return array( array( '-Lala' ), array( '_Lila' ) );
	}

	/**
	 * Cache and non-cache data tests sample
	 *
	 * @return array
	 */
	public function getCacheNonCacheDataProvider() {
		$propertyA = $this->getRandomString();
		$propertyB = $this->getRandomString();

		return array(
			array(

				// #0 Cached
				array(
					'cacheEnabled' => true,
					'A' => array( 'property' => $propertyA ),
					'B' => array( 'property' => $propertyB ),
				),
				array(
					'A' => array( new DIProperty( $propertyA ) ),
					'B' => array( new DIProperty( $propertyA ) )
				),
				array( 'msg' => 'Failed asserting that A & B are identical for a cached result' )
			),
			array(

				// #1 Non-cached
				array(
					'cacheEnabled' => false,
					'A' => array( 'property' => $propertyA ),
					'B' => array( 'property' => $propertyB )
				),
				array(
					'A' => array( new DIProperty( $propertyA ) ),
					'B' => array( new DIProperty( $propertyB ) )
				),
				array( 'msg' => 'Failed asserting that A & B are not identical for a non-cached result' )
			)
		);
	}
}
