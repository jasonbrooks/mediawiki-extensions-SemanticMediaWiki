<?php

namespace SMW;

use Parser;

/**
 * Class that provides the {{#subobject}} parser function
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Subobject
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * Class that provides the {{#subobject}} parser function
 *
 * @ingroup ParserFunction
 */
class SubobjectParserFunction {

	/** @var IParserData */
	protected $parserData;

	/** @var Subobject */
	protected $subobject;

	/** @var MessageFormatter */
	protected $msgFormatter;

	/** @var boolean */
	protected $objectReference = false;

	/**
	 * @since 1.9
	 *
	 * @param IParserData $parserData
	 * @param Subobject $subobject
	 * @param MessageFormatter $msgFormatter
	 */
	public function __construct( IParserData $parserData, Subobject $subobject, MessageFormatter $msgFormatter ) {
		$this->parserData = $parserData;
		$this->subobject = $subobject;
		$this->msgFormatter = $msgFormatter;
	}

	/**
	 * Returns invoked subobject
	 *
	 * @since 1.9
	 *
	 * @return Subobject
	 */
	public function getSubobject() {
		return $this->subobject;
	}

	/**
	 * Enables/disables to create an object reference pointing to the original
	 * subject
	 *
	 * @since 1.9
	 *
	 * @param boolean $objectReference
	 *
	 * @return SubobjectParserFunction
	 */
	public function setObjectReference( $objectReference ) {
		$this->objectReference = $objectReference;
		return $this;
	}

	/**
	 * Generates an Id in accordance to the available settings
	 *
	 * @since 1.9
	 *
	 * @param ArrayFormatter $parameters
	 *
	 * @return string
	 */
	protected function getId( ArrayFormatter $parameters ) {

		$isAnonymous = in_array( $parameters->getFirst(), array( null, '' ,'-' ) );

		if ( $this->objectReference || $isAnonymous ) {
			$id = $this->subobject->generateId( new HashIdGenerator( $parameters->toArray(), '_' ) );
		} else {
			$id = $parameters->getFirst();
		}

		$this->objectReference = $this->objectReference && !$isAnonymous;

		return $id;
	}

	/**
	 * Add values to the subobject instance
	 *
	 * @since 1.9
	 *
	 * @param ArrayFormatter $parameters
	 */
	protected function addSubobjectValues( ArrayFormatter $parameters ) {

		// Initialize semantic container for a given identifier
		$this->subobject->setSemanticData( $this->getId( $parameters ) );

		// Add object reference as additional parameter if enabled
		if ( $this->objectReference ) {
			$parameters->addParameter(
				$parameters->getFirst(),
				$this->parserData->getTitle()->getPrefixedText()
			);
		}

		// Add property / values to the subobject instance
		foreach ( $parameters->toArray() as $property => $values ){
			foreach ( $values as $value ) {
				$this->subobject->addPropertyValue(
					DataValueFactory::newPropertyValue( $property, $value )
				);
			}
		}
	}

	/**
	 * Parse parameters and return results to the ParserOutput object
	 *
	 * @since 1.9
	 *
	 * @param ArrayFormatter $params
	 *
	 * @return string|null
	 */
	public function parse( ArrayFormatter $parameters ) {

		// Add values to the instantiated subobject
		$this->addSubobjectValues( $parameters );

		// Store subobject to the semantic data instance
		$this->parserData->getData()->addPropertyObjectValue(
			$this->subobject->getProperty(),
			$this->subobject->getContainer()
		);

		// Update ParserOutput
		$this->parserData->updateOutput();

		return $this->msgFormatter->addFromArray( $this->subobject->getErrors() )
			->addFromArray( $this->parserData->getErrors() )
			->addFromArray( $parameters->getErrors() )
			->getHtml();
	}

	/**
	 * Parser::setFunctionHook {{#subobject}} handler method
	 *
	 * @param Parser $parser
	 *
	 * @return string|null
	 */
	public static function render( Parser &$parser ) {
		$instance = new self(
			new ParserData( $parser->getTitle(), $parser->getOutput() ),
			new Subobject( $parser->getTitle() ),
			new MessageFormatter( $parser->getTargetLanguage() )
		);

		return $instance->parse( ParameterFormatterFactory::newFromArray( func_get_args() ) );
	}
}
