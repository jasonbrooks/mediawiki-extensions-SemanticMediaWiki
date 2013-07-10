<?php

namespace SMW\Test;

use SMW\ArrayAccessor;
use SMWDataItem;

/**
 * MockObject builder
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * MockObject builder
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @codeCoverageIgnore
 */
class MockObjectBuilder extends \PHPUnit_Framework_TestCase {

	/** @var ArrayAccessor */
	protected $accessor;

	/**
	 * @since 1.9
	 *
	 * @param ArrayAccessor $accessor
	 */
	public function __construct( ArrayAccessor $accessor ) {
		$this->accessor = $accessor;
	}

	/**
	 * Sets value
	 *
	 * @since 1.9
	 *
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	protected function set( $key, $default = null ) {
		return $this->accessor->has( $key ) ? $this->accessor->get( $key ) : $default;
	}

	/**
	 * Returns a SMWQuery object
	 *
	 * @since 1.9
	 *
	 * @return SMWQuery
	 */
	public function getMockQuery() {

		$query = $this->getMockBuilder( 'SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		return $query;
	}

	/**
	 * Returns a SMWQueryResult object
	 *
	 * @since 1.9
	 *
	 * @return SMWQueryResult
	 */
	public function getMockQueryResult() {

		$queryResult = $this->getMockBuilder( 'SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->any() )
			->method( 'toArray' )
			->will( $this->returnValue( $this->set( 'toArray' ) ) );

		$queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( $this->set( 'getErrors', array() ) ) );

		$queryResult->expects( $this->any() )
			->method( 'hasFurtherResults' )
			->will( $this->returnValue( $this->set( 'hasFurtherResults' ) ) );

		return $queryResult;
	}

	/**
	 * Helper method that returns a DIWikiPage object
	 *
	 * @since 1.9
	 *
	 * @return DIWikiPage
	 */
	public function getMockDIWikiPage() {

		$diWikiPage = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$diWikiPage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $this->set( 'getTitle' ) ) );

		$diWikiPage->expects( $this->any() )
			->method( 'getDIType' )
			->will( $this->returnValue( SMWDataItem::TYPE_WIKIPAGE ) );

		$diWikiPage->expects( $this->any() )
			->method( 'findPropertyTypeID' )
			->will( $this->returnValue( $this->set( 'findPropertyTypeID', '_wpg' ) ) );

		return $diWikiPage;
	}

	/**
	 * Returns a DIProperty object
	 *
	 * @par Example:
	 * @code
	 *  $property = array(
	 *   'isUserDefined' => $isUserDefined,
	 *   'getDiWikiPage' => $this->getMockDIWikiPage( true ),
	 *   'getLabel'      => $this->getRandomString(),
	 *  );
	 *
	 *  $mockObject = new MockObjectBuilder( new ArrayAccessor( $property ) );
	 *  $mockObject->getMockDIProperty();
	 * @endcode
	 *
	 * @since 1.9
	 *
	 * @return DIProperty
	 */
	public function getMockDIProperty() {

		$property = $this->getMockBuilder( '\SMW\DIProperty' )
			->disableOriginalConstructor()
			->getMock();

		$property->expects( $this->any() )
			->method( 'isUserDefined' )
			->will( $this->returnValue( $this->set( 'isUserDefined' ) ) );

		$property->expects( $this->any() )
			->method( 'getDiWikiPage' )
			->will( $this->returnValue( $this->set( 'getDiWikiPage' ) ) );

		$property->expects( $this->any() )
			->method( 'findPropertyTypeID' )
			->will( $this->returnValue( $this->set( 'findPropertyTypeID', '_wpg' ) ) );

		$property->expects( $this->any() )
			->method( 'getKey' )
			->will( $this->returnValue( $this->set( 'getKey', '_wpg' ) ) );

		$property->expects( $this->any() )
			->method( 'getDIType' )
			->will( $this->returnValue( SMWDataItem::TYPE_PROPERTY ) );

		$property->expects( $this->any() )
			->method( 'getLabel' )
			->will( $this->returnValue( $this->set( 'getLabel' ) ) );

		return $property;
	}

	/**
	 * Returns a Store object
	 *
	 * @note MockStore is based on the abstract Store class which avoids
	 * dependency on a specific Store implementation (SQLStore etc.), the mock
	 * object will allow to override necessary methods
	 *
	 * @since 1.9
	 *
	 * @return Store
	 */
	public function getMockStore( ) {

		$idTable = $this->getMock( 'stdClass', array( 'getIdTable') );

		$idTable->expects( $this->any() )
			->method( 'getIdTable' )
			->will( $this->returnValue( 'smw_id_table_test' ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->setMethods( array(
				'setup',
				'drop',
				'getStatisticsTable',
				'getObjectIds',
				'refreshData',
				'getStatistics',
				'getQueryResult',
				'getPropertiesSpecial',
				'getUnusedPropertiesSpecial',
				'getWantedPropertiesSpecial',
				'getPropertyTables',
				'deleteSubject',
				'doDataUpdate',
				'changeTitle',
				'getProperties',
				'getInProperties',
				'getAllPropertySubjects',
				'getSQLConditions',
				'getSemanticData',
				'getPropertyValues',
				'getPropertySubjects'
			) )
			->getMock();

		$store->expects( $this->any() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( $this->set( 'getPropertyValues' ) ) );

		$store->expects( $this->any() )
			->method( 'getPropertiesSpecial' )
			->will( $this->returnValue( $this->set( 'getPropertiesSpecial' ) ) );

		$store->expects( $this->any() )
			->method( 'getUnusedPropertiesSpecial' )
			->will( $this->returnValue( $this->set( 'getUnusedPropertiesSpecial' ) ) );

		$store->expects( $this->any() )
			->method( 'getWantedPropertiesSpecial' )
			->will( $this->returnValue( $this->set( 'getWantedPropertiesSpecial' ) ) );

		$store->expects( $this->any() )
			->method( 'getSQLConditions' )
			->will( $this->returnValue( $this->set( 'getSQLConditions' ) ) );

		$store->expects( $this->any() )
			->method( 'getStatistics' )
			->will( $this->returnValue( $this->set( 'getStatistics' ) ) );

		$store->expects( $this->any() )
			->method( 'getPropertyTables' )
			->will( $this->returnValue( $this->set( 'getPropertyTables' ) ) );

		$store->expects( $this->any() )
			->method( 'getQueryResult' )
			->will( is_callable( $this->set( 'getQueryResult' ) ) ? $this->returnCallback( $this->set( 'getQueryResult' ) ) : $this->returnValue( $this->set( 'getQueryResult' ) ) );

		$store->expects( $this->any() )
			->method( 'getObjectIds' )
			->will( $this->returnValue( $idTable ) );

		$store->expects( $this->any() )
			->method( 'getStatisticsTable' )
			->will( $this->returnValue( 'smw_statistics_table_test' ) );

		return $store;
	}

	/**
	 * Returns a SMWDIError object
	 *
	 * @since 1.9
	 *
	 * @return SMWDIError
	 */
	public function getMockDIError() {

		$errors = $this->getMockBuilder( 'SMWDIError' )
			->disableOriginalConstructor()
			->getMock();

		$errors->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( $this->set( 'getErrors' ) ) );

		return $errors;
	}

	/**
	 * Returns a Title mock object
	 *
	 * @note This mock object avoids the involvement of LinksUpdate (which
	 * requires DB access) and returns a randomized LatestRevID/ArticleID
	 *
	 * @since 1.9
	 *
	 * @return Title
	 */
	public function getMockTitle() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getArticleID' )
			->will( $this->returnValue( rand( 10, 10000 ) ) );

		$title->expects( $this->any() )
			->method( 'getNamespace' )
			->will( $this->returnValue( $this->set( 'getNamespace' ) ) );

		$title->expects( $this->any() )
			->method( 'isKnown' )
			->will( $this->returnValue( $this->set( 'exists' ) ) );

		$title->expects( $this->any() )
			->method( 'exists' )
			->will( $this->returnValue( $this->set( 'exists' ) ) );

		$title->expects( $this->any() )
			->method( 'getLatestRevID' )
			->will( $this->returnValue( rand( 10, 5000 ) ) );

		$title->expects( $this->any() )
			->method( 'getText' )
			->will( $this->returnValue( $this->set( 'getText' ) ) );

		$title->expects( $this->any() )
			->method( 'getPrefixedText' )
			->will( $this->returnValue( $this->set( 'getPrefixedText' ) ) );

		return $title;
	}

}
