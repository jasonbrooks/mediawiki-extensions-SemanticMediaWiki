<?php

namespace SMW;

/**
 * Interface for objects that can report messages.
 *
 * @since 1.9
 *
 * @file
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface MessageReporter {

	/**
	 * Report the provided message.
	 *
	 * @since 1.9
	 *
	 * @param string $message
	 */
	public function reportMessage( $message );

}

/**
 * Message reporter that reports messages by passing them along to all
 * registered handlers.
 *
 * @since 1.9
 *
 * @file
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ObservableMessageReporter implements MessageReporter {

	/**
	 * @since 1.9
	 *
	 * @var MessageReporter[]
	 */
	protected $reporters = array();

	/**
	 * @since 1.9
	 *
	 * @var callable[]
	 */
	protected $callbacks = array();

	/**
	 * @see MessageReporter::report
	 *
	 * @since 1.9
	 *
	 * @param string $message
	 */
	public function reportMessage( $message ) {
		foreach ( $this->reporters as $reporter ) {
			$reporter->reportMessage( $message );
		}

		foreach ( $this->callbacks as $callback ) {
			call_user_func( $callback, $message );
		}
	}

	/**
	 * Register a new message reporter.
	 *
	 * @since 1.9
	 *
	 * @param MessageReporter $reporter
	 */
	public function registerMessageReporter( MessageReporter $reporter ) {
		$this->reporters[] = $reporter;
	}

	/**
	 * Register a callback as message reporter.
	 *
	 * @since 1.9
	 *
	 * @param callable $handler
	 */
	public function registerReporterCallback( $handler ) {
		$this->callbacks[] = $handler;
	}

}
