<?php
/**
 * Init all plugin
 *
 * @since 1.0.0
 */

namespace Helproic;

defined( 'ABSPATH' ) || exit;

class Loader {

	use Singleton;

	/**
	 * Init core of the plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {

		Dashboard::instance();
		add_action( 'init_helproic', array( Dashboard::class, 'instance' ) );
		echo '<pre>' . print_r( 'plugins_loaded', true ) . '</pre>';
		wp_die();
		$this->load_classes();

		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		do_action( 'init_helproic' );

	}


	/**
	 * Loads plugin text domain
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {

		$current_theme = wp_get_theme();

		if ( ! empty( $current_theme->stylesheet ) && file_exists( get_theme_root() . '/' . $current_theme->stylesheet . '/local_helproic_lang' ) ) {
			load_plugin_textdomain( 'helproic', false, plugin_basename( dirname( __FILE__ ) ) . '/../../themes/' . $current_theme->stylesheet . '/local_helproic_lang' );
		} else {
			load_plugin_textdomain( 'helproic', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

	}

	/**
	 * Load needed files dependencies
	 *
	 * @since 1.0.0
	 */
	public function load_classes() {

		do_action( 'helproic_autoload' );
		spl_autoload_register( __NAMESPACE__ . '\Loader::autoload' );
		do_action( 'helproic_autoloaded' );

	}

	/**
	 * Load all classes from includes
	 *
	 * @since 1.0.0
	 */
	public static function autoload( $class ) {
		global $nhg_autoload_namespaces;

		echo '<pre>' . print_r( $class, true ) . '</pre>';
		if ( strpos( $class, 'NHG\\' ) !== 0 || empty( $nhg_autoload_namespaces ) ) {
			return;
		}

		$load_path = null;
		$autoload  = false;

		$pieces    = explode( '\\', $class );
		$classname = array_pop( $pieces );
		$namespace = implode( '\\', $pieces );

		foreach ( $nhg_autoload_namespaces as $key => $load_path ) {
			if ( $namespace === $key || ( strpos( $namespace, $key . '\\' ) === 0 ) ) {
				$autoload = true;
				break;
			}
		}

		if ( ! $autoload || ! $load_path ) {
			return;
		}

		$path = $load_path . '/includes' . strtolower( str_replace( [ '\\', '_' ], [
				'/',
				'-'
			], substr( $namespace, strlen( $key ) ) ) ) . '/';
		$slug = strtolower( str_replace( '_', '-', $classname ) ) . '.php';

		$prefixes = [ 'class', 'trait', 'abstract' ];

		foreach ( $prefixes as $prefix ) {
			$filename = $path . $prefix . '-' . $slug;

			if ( file_exists( $filename ) ) {
				require_once $filename;

				return;
			}
		}
	}

}