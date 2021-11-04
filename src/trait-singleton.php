<?php
/**
 * Framework for class initiation.
 */

namespace NSukonny\NFramework;

use Exception;

defined( 'ABSPATH' ) || exit;

trait Singleton {

	/**
	 * Clone method
	 *
	 * @return void
	 *
	 * @since  1.1.0
	 */
	protected function __clone() {
	}

	/**
	 * Wakeup method
	 *
	 * @throws Exception When used.
	 *
	 * @since 1.1.0
	 */
	protected function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Gets the instance
	 *
	 * @return self
	 *
	 * @since  1.1.0
	 */
	final public static function instance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * The constructor
	 *
	 * @since 1.0.0
	 */
	final protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize
	 *
	 * @since 1.0.0
	 */
	abstract public function init();
}
