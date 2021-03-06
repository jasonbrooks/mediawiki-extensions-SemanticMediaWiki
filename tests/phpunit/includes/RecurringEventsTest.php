<?php

namespace SMW\Test;

use SMW\RecurringEvents;
use SMW\ParserParameterFormatter;

/**
 * Tests for the RecurringEvents class.
 *
 * @since 1.9
 *
 * @file
 * @ingroup Test
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * Tests for the RecurringEvents class
 * @covers \SMW\RecurringEvents
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class RecurringEventsTest extends SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\RecurringEvents';
	}

	/**
	 * Provides sample data normally found in connection with {{#set_recurring_event}}
	 *
	 * @return array
	 */
	public function getParametersDataProvider() {
		return array(
			// {{#set_recurring_event:property=Has birthday
			// |start=01 Feb 1970
			// |has title= Birthday
			// |unit=year
			// |period=12
			// |limit=3
			// }}
			array(
				array(
					'property=Has birthday',
					'start=01 Feb 1970',
					'has title=Birthday',
					'unit=month',
					'period=12',
					'limit=3'
				),
				array(
					'errors' => 0,
					'dates' => array( '1 February 1970', '1 February 1971 00:00:00', '1 February 1972 00:00:00', '1 February 1973 00:00:00' ),
					'property' => 'Has birthday',
					'parameters' => array( 'has title' => array( 'Birthday' ) )
				)
			),

			// {{#set_recurring_event:property=Has birthday
			// |start=01 Feb 1970
			// |end=01 Feb 1972
			// |has title= Birthday
			// |unit=year
			// |period=12
			// |limit=3
			// }}
			array(
				array(
					'property=Has birthday',
					'start=01 Feb 1970',
					'end=01 Feb 1972',
					'has title=Birthday',
					'unit=month',
					'period=12',
					'limit=3'
				),
				array(
					'errors' => 0,
					'dates' => array( '1 February 1970', '1 February 1971 00:00:00', '1 February 1972 00:00:00' ),
					'property' => 'Has birthday',
					'parameters' => array( 'has title' => array( 'Birthday' ) )
				)
			),

			// {{#set_recurring_event:property=Has birthday
			// |start=01 Feb 1970
			// |end=01 Feb 1972
			// |has title= Birthday
			// |unit=year
			// |week number=2
			// |period=12
			// |limit=3
			// }}
			array(
				array(
					'property=Has birthday',
					'start=01 Feb 1970',
					'end=01 Feb 1972',
					'has title=Birthday',
					'unit=month',
					'week number=2',
					'period=12',
					'limit=3'
				),
				array(
					'errors' => 0,
					'dates' => array( '1 February 1970', '14 February 1971 00:00:00' ),
					'property' => 'Has birthday',
					'parameters' => array( 'has title' => array( 'Birthday' ) )
				)
			),

			// {{#set_recurring_event:property=Has birthday
			// |start=01 Feb 1972 02:00
			// |has title=Test 12
			// |unit=week
			// |period=4
			// |limit=3
			// }}
			array(
				array(
					'property=Has birthday',
					'start=01 Feb 1972 02:00',
					'has title=Test 2',
					'unit=week',
					'period=4',
					'limit=3'
				),
				array(
					'errors' => 0,
					'dates' => array( '1 February 1972 02:00:00', '29 February 1972 02:00:00', '28 March 1972 02:00:00', '25 April 1972 02:00:00' ),
					'property' => 'Has birthday',
					'parameters' => array( 'has title' => array( 'Test 2' ) )
				)
			),

			// {{#set_recurring_event:property=Has date
			// |start=January 4, 2010
			// |unit=week
			// |period=1
			// |limit=4
			// |include=March 16, 2010;March 23, 2010
			// |exclude=January 18, 2010;January 25, 2010
			// }}
			array(
				array(
					'property=Has date',
					'start=January 4, 2010',
					'unit=week',
					'period=1',
					'limit=4',
					'include=March 16, 2010;March 23, 2010',
					'exclude=January 18, 2010;January 25, 2010'
				),
				array(
					'errors' => 0,
					'dates' => array( '4 January 2010', '11 January 2010 00:00:00', '1 February 2010 00:00:00', 'March 16, 2010', 'March 23, 2010' ),
					'property' => 'Has date',
					'parameters' => array()
				)
			),

			// {{#set_recurring_event:property=Has date
			// |start=January 4, 2010
			// |unit=week
			// |period=1
			// |limit=4
			// |include=March 16, 2010;March 23, 2010|+sep=;
			// |exclude=January 18, 2010;January 25, 2010|+sep=;
			// }}
			array(
				array(
					'property=Has date',
					'start=January 4, 2010',
					'unit=week',
					'period=1',
					'limit=4',
					'include=March 16, 2010;March 23, 2010',
					'+sep=;',
					'exclude=January 18, 2010;January 25, 2010',
					'+sep=;'
				),
				array(
					'errors' => 0,
					'dates' => array( '4 January 2010', '11 January 2010 00:00:00', '1 February 2010 00:00:00', 'March 16, 2010', 'March 23, 2010' ),
					'property' => 'Has date',
					'parameters' => array()
				)
			),

			// Simulate start date has wrong type

			// {{#set_recurring_event:property=Has date
			// |start=???
			// |unit=week
			// |period=1
			// |limit=4
			// |include=March 16, 2010;March 23, 2010
			// |exclude=January 18, 2010;January 25, 2010
			// }}
			array(
				array(
					'property=Has date',
					'start=???',
					'unit=week',
					'period=1',
					'limit=4',
					'include=March 16, 2010;March 23, 2010',
					'exclude=January 18, 2010;January 25, 2010'
				),
				array(
					'errors' => 1,
					'dates' => array(),
					'property' => 'Has date',
					'parameters' => array()
				)
			),

			// Simulate missing start date

			// {{#set_recurring_event:property=Has date
			// |start=
			// |unit=week
			// |period=1
			// |limit=4
			// |include=March 16, 2010;March 23, 2010
			// |exclude=January 18, 2010;January 25, 2010
			// }}
			array(
				array(
					'property=Has date',
					'start=',
					'unit=week',
					'period=1',
					'limit=4',
					'include=March 16, 2010;March 23, 2010',
					'exclude=January 18, 2010;January 25, 2010'
				),
				array(
					'errors' => 1,
					'dates' => array(),
					'property' => 'Has date',
					'parameters' => array()
				)
			),

			// Simulate missing property

			// {{#set_recurring_event:property=
			// |start=January 4, 2010
			// |unit=week
			// |period=1
			// |limit=4
			// |include=March 16, 2010;March 23, 2010|+sep=;
			// |exclude=January 18, 2010;January 25, 2010|+sep=;
			// }}
			array(
				array(
					'property=',
					'start=January 4, 2010',
					'unit=week', 'period=1',
					'limit=4',
					'include=March 16, 2010;March 23, 2010',
					'+sep=;',
					'exclude=January 18, 2010;January 25, 2010',
					'+sep=;'
				),
				array(
					'errors' => 1,
					'dates' => array(),
					'property' => '',
					'parameters' => array()
				)
			),
		);
	}

	/**
	 * Helper method that returns IParameterFormatter object
	 *
	 * @return ParserParameterFormatter
	 */
	private function getParameters( array $params ) {
		$parameters = new ParserParameterFormatter( $params );
		return $parameters->toArray();
	}

	/**
	 * Helper method that returns Settings object
	 *
	 * @return Settings
	 */
	protected function getRecurringEventsSettings() {
		return $this->getSettings( array(
			'smwgDefaultNumRecurringEvents' => 10,
			'smwgMaxNumRecurringEvents' => 50
			)
		);
	}

	/**
	 * Helper method that returns an RecurringEvents object
	 *
	 * @return RecurringEvents
	 */
	private function getInstance( array $params ) {
		return new RecurringEvents(
			$this->getParameters( $params ),
			$this->getRecurringEventsSettings()
		);
	}

	/**
	 * @test RecurringEvents::__construct (parameters exceptions)
	 *
	 * @since  1.9
	 */
	public function testMissingParametersExceptions() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$instance = new RecurringEvents( '' , '' );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test RecurringEvents::__construct
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 */
	public function testInstance( array $params ) {
		$instance = $this->getInstance( $params );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test RecurringEvents::__construct (options exceptions)
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 */
	public function testMissingOptionsExceptions( array $params ) {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$parameters = $this->getParameters( $params );

		$instance = new RecurringEvents( $parameters, '' );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test RecurringEvents::getErrors
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testGetErrors( array $params, array $expected ) {
		$instance = $this->getInstance( $params );
		$this->assertCount( $expected['errors'], $instance->getErrors() );
	}

	/**
	 * @test RecurringEvents::getProperty
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testGetProperty( array $params, array $expected ) {
		$instance = $this->getInstance( $params );
		$this->assertEquals( $expected['property'], $instance->getProperty() );
	}

	/**
	 * @test RecurringEvents::getParameters
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testGetParameters( array $params, array $expected ) {
		$instance = $this->getInstance( $params );
		$this->assertEquals( $expected['parameters'], $instance->getParameters() );
	}

	/**
	 * @test RecurringEvents::getDates
	 * @dataProvider getParametersDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testGetDates( array $params, array $expected ) {
		$instance = $this->getInstance( $params );
		$this->assertEquals( $expected['dates'], $instance->getDates() );
	}

	/**
	 * Provides sample data for a mass insert
	 *
	 * @return array
	 */
	public function getMassInsertDataProvider() {
		return array(
			array(
				array(
					'property=Has birthday',
					'start=01 Feb 1970',
					'Has title=Birthday',
					'unit=month', 'period=12',
					'limit=500',
				),
				array(
					'errors' => 0,
					'count' => 501,
					'property' => '',
					'parameters' => array()
				)
			)
		);
	}

	/**
	 * @test RecurringEvents::getDates (mass insert)
	 * @dataProvider getMassInsertDataProvider
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 * @param array $expected
	 */
	public function testMassInsert( array $params, array $expected ) {
		$instance = $this->getInstance( $params );
		$this->assertCount( $expected['count'], $instance->getDates() );
	}

	/**
	 * @test RecurringEvents::getJulianDay
	 *
	 * @since 1.9
	 */
	public function testGetJulianDay() {
		$instance = $this->getInstance( array() );

		// SMWDIWikiPage stub object
		$dataValue = $this->getMockBuilder( 'SMWTimeValue' )
			->disableOriginalConstructor()
			->getMock();

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( null ) );

		$this->assertEquals( null, $instance->getJulianDay( $dataValue ) );
	}
}
