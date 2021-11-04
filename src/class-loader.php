<?php
/**
 * Helper for load includes, classes and assets
 */

namespace NSukonny\NFramework;

class Loader {

	use Singleton;

	static $autoload_namespaces = [];

	public function init() {

		$this->load_assets();

	}

	/**
	 * Find and load default assets
	 *
	 * @since 1.0.0
	 */
	public function load_assets() {

		if ( ! function_exists( 'is_admin' ) ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

	}

	/**
	 * Init classes, assets and other
	 *
	 * @param $namespace
	 * @param $dir_path
	 *
	 * @since 1.0.0
	 */
	public static function init_autoload( $namespace, $dir_path ) {

		self::$autoload_namespaces[] = array(
			'namespace' => $namespace,
			'dirpath'   => $dir_path,
		); //TODO Test how it's working with two plugins in one website

		spl_autoload_register( __CLASS__ . '::autoload' );

		add_action( 'init', array( Loader::class, 'instance' ) );

	}

	/**
	 * Include files from called class
	 *
	 * @param string $class Namespace for needed class
	 *
	 * @since 1.0.0
	 */
	public static function autoload( $class ) {

		$class_explode = explode( '\\', $class );
		if ( ! self::is_for_framework( $class_explode ) ) {
			return;
		}

		$namespace_key = array_search( $class_explode[0], array_column( self::$autoload_namespaces, 'namespace' ) );
		$includes_path = self::$autoload_namespaces[ $namespace_key ]['dirpath'] . '/includes';
		$file_path     = '';
		$file_types    = array( 'class', 'trait', 'abstract' );
		$file_name     = strtolower( str_replace( '_', '-', array_pop( $class_explode ) ) ) . '.php';

		foreach ( $class_explode as $key => $classname ) {
			if ( 0 !== $key ) {
				$file_path .= '/' . strtolower( str_replace( '_', '-', $classname ) );
			}
		}

		foreach ( $file_types as $type ) {
			$filename = $includes_path . $file_path . '/' . $type . '-' . $file_name;

			if ( file_exists( $filename ) ) {
				require_once $filename;

				return;
			}
		}
	}

	/**
	 * Check if we need use framework for that call
	 *
	 * @param array $class_explode Called class name exploaded by \
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_for_framework( array $class_explode ): bool {

		if ( empty( self::$autoload_namespaces ) ) {
			return false;
		}

		$namespaces = array_column( self::$autoload_namespaces, 'namespace' );

		return in_array( $class_explode[0], $namespaces, true );
	}

	/**
	 * Find and enqueue admin styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {

		$namespace = strtolower( self::$autoload_namespaces[0]['namespace'] );
		$css_file  = 'assets/css/admin.min.css';
		$js_file   = 'assets/js/admin.min.js';

		if ( file_exists( self::$autoload_namespaces[0]['dirpath'] . '/' . $css_file ) ) {
			wp_enqueue_style(
				$namespace,
				URL . $css_file,
				array(),
				VERSION
			);
		}

		if ( file_exists( self::$autoload_namespaces[0]['dirpath'] . '/' . $js_file ) ) {
			wp_register_script(
				$namespace,
				URL . $js_file,
				array( 'jquery' ),
				VERSION
			);

			wp_enqueue_script( $namespace );
		}

	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$namespace = strtolower( self::$autoload_namespaces[0]['namespace'] );
		$css_file  = URL . '/assets/css/style.min.css';
		$js_file   = URL . '/assets/js/script.min.js';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				$namespace,
				$css_file,
				array(),
				VERSION
			);
		}

		if ( file_exists( $js_file ) ) {
			wp_register_script(
				$namespace,
				$js_file,
				array( 'jquery' ),
				VERSION
			);
			wp_enqueue_script( $namespace );
		}

	}

	/**
	 * Loads plugin text domain
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {

		$current_theme = wp_get_theme();
		$namespace     = strtolower( self::$autoload_namespaces[0]['namespace'] );

		if ( ! empty( $current_theme->stylesheet ) && file_exists( get_theme_root() . '/' . $current_theme->stylesheet . '/local_' . $namespace . '_lang' ) ) {
			load_plugin_textdomain( $namespace, false, get_theme_root() . '/' . $current_theme->stylesheet . '/local_' . $namespace . '_lang' );
		} else if ( file_exists( self::$autoload_namespaces[0]['dirpath'] . '/languages' ) ) {
			load_plugin_textdomain( $namespace, false, self::$autoload_namespaces[0]['dirpath'] . '/languages' );
		}

	}
}