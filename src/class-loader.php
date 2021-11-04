<?php
/**
 * Helper for load includes, classes and assets
 */

namespace NSukonny\NFramework;

class Loader {

	static $autoload_namespaces = [];

	/**
	 * Init classes, assets and other
	 *
	 * @param $namespace
	 * @param $dir_path
	 * @param $init_params
	 *
	 * @since 1.0.0
	 */
	public static function init( $namespace, $dir_path, $init_params ) {

		self::$autoload_namespaces[] = array(
			'namespace' => $namespace,
			'dirpath'   => $dir_path,
			'params'    => $init_params,
		); //TODO Test how it's working with two plugins in one website

		spl_autoload_register( __CLASS__ . '::autoload' );

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

}